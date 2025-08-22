<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/editlib.php');

function quizgenerator_add_instance($quizgenerator, $cm)
{
    global $DB;
    $quizgenerator->course = $cm->course;
    $quizgenerator->timecreated = time();
    $quizgenerator->timemodified = time();
    $quizgenerator->name = clean_param($quizgenerator->name, PARAM_TEXT);
    if (strlen($quizgenerator->name) > 255) {
        $quizgenerator->name = substr($quizgenerator->name, 0, 255);
    }
    $quizgenerator->id = $DB->insert_record('quizgenerator', $quizgenerator);
    return $quizgenerator->id;
}

function quizgenerator_update_instance($quizgenerator, $cm)
{
    global $DB;
    $quizgenerator->course = $cm->course;
    $quizgenerator->timemodified = time();
    $quizgenerator->id = $quizgenerator->instance;
    $quizgenerator->name = clean_param($quizgenerator->name, PARAM_TEXT);
    if (strlen($quizgenerator->name) > 255) {
        $quizgenerator->name = substr($quizgenerator->name, 0, 255);
    }
    return $DB->update_record('quizgenerator', $quizgenerator);
}

function quizgenerator_delete_instance($id)
{
    global $DB;
    return $DB->delete_records('quizgenerator', ['id' => $id]);
}

function quizgenerator_get_question_category($courseid)
{
    global $DB;
    $context = context_course::instance($courseid);
    $category = $DB->get_record('question_categories', ['contextid' => $context->id], '*', IGNORE_MISSING);
    return $category ? $category->id : null;
}

function quizgenerator_create_question($categoryid, $questiondata)
{
    global $DB, $USER;

    if (!$categoryid) {
        throw new moodle_exception('Invalid question category ID');
    }

    $qtype = $questiondata->answers == NULL ? 'essay' : 'multichoice';

    // Sanitize and truncate the name to fit the 255 character limit
    $questionname = trim(html_entity_decode(strip_tags($questiondata->name)));
    if (strlen($questionname) > 255) {
        $questionname = substr($questionname, 0, 255);
    }

    $questiontext = trim(html_entity_decode(strip_tags($questiondata->text)));
    if (strlen($questiontext) > 65535) { // Mediumtext limit
        $questiontext = substr($questiontext, 0, 65535);
    }

    $question = new stdClass();
    $question->category               = $categoryid;
    $question->name                   = $questionname;
    $question->questiontext           = $questiontext;
    $question->questiontextformat     = FORMAT_HTML;
    $question->generalfeedback        = '';
    $question->generalfeedbackformat  = FORMAT_HTML;
    $question->qtype                  = $qtype;
    $question->defaultmark            = 1;
    $question->penalty                = 0.3333333;
    $question->penaltyformat          = FORMAT_HTML;
    $question->createdby              = $USER->id;
    $question->modifiedby             = $USER->id;
    $question->stamp                  = make_unique_id_code();
    $question->version                = 1;

    $questionid = $DB->insert_record('question', $question);
    if (!$questionid) {
        throw new moodle_exception('Failed to insert question');
    }

    if ($qtype === 'essay') {
        $essayOptions = new stdClass();
        $essayOptions->questionid = $questionid;
        $essayOptions->responseformat = 'editor';
        $essayOptions->responserequired = 1;
        $essayOptions->responsefieldlines = 15;
        $essayOptions->minwordlimit = NULL;
        $essayOptions->maxwordlimit = NULL;
        $essayOptions->attachments = 0;
        $essayOptions->attachmentsrequired = 0;
        $essayOptions->graderinfo = NULL;
        $essayOptions->graderinfoformat = 0;
        $essayOptions->responsetemplate = NULL;
        $essayOptions->responsetemplateformat = 0;
        $essayOptions->maxbytes = 0;
        $essayOptions->filetypeslist = NULL;

        $DB->insert_record('qtype_essay_options', $essayOptions);
    }

    $qbe = new stdClass();
    $qbe->questioncategoryid = $categoryid;
    $qbe->ownerid = $USER->id;
    $qbe->stamp = make_unique_id_code();
    $questionbankentryid = $DB->insert_record('question_bank_entries', $qbe);
    if (!$questionbankentryid) {
        throw new moodle_exception('Failed to insert question bank entry');
    }

    $qv = new stdClass();
    $qv->questionbankentryid = $questionbankentryid;
    $qv->version = 1;
    $qv->questionid = $questionid;
    $qv->status = "ready";
    $questionversionid = $DB->insert_record('question_versions', $qv);
    if (!$questionversionid) {
        throw new moodle_exception('Failed to insert question version');
    }

    if ($qtype === 'multichoice') {
        foreach ($questiondata->answers as $answer) {
            $answerobj = new stdClass();
            $answerobj->question = $questionid;
            $answerobj->answer = $answer['text'];
            $answerobj->fraction = $answer['fraction'];
            $answerobj->feedback = '';
            $answerobj->feedbackformat = FORMAT_HTML;
            $DB->insert_record('question_answers', $answerobj);
        }
    }

    return $questionid;
}

function quizgenerator_get_course_documents($courseid)
{
    global $DB;
    $fs = get_file_storage();
    $modinfo = get_fast_modinfo($courseid);
    $cms = $modinfo->get_cms();
    $allfiles = [];
    foreach ($cms as $cm) {
        if (!$cm->uservisible) {
            continue;
        }
        $context = context_module::instance($cm->id);
        $component = 'mod_resource';
        $fileareas = ['content'];
        foreach ($fileareas as $filearea) {
            $files = $fs->get_area_files($context->id, $component, $filearea, false, 'timemodified DESC');
            foreach ($files as $file) {
                if (!$file->is_directory()) {
                    $allfiles[] = [
                        'coursemodule' => $cm->name,
                        'filename'     => $file->get_filename(),
                        'url'          => moodle_url::make_pluginfile_url(
                            $file->get_contextid(),
                            $file->get_component(),
                            $file->get_filearea(),
                            $file->get_itemid(),
                            $file->get_filepath(),
                            $file->get_filename()
                        )
                    ];
                }
            }
        }
    }
    return $allfiles;
}

function quizgenerator_call_quiz_api($id, $courseid, $documents)
{
    global $CFG;

    $query = required_param('quizquery', PARAM_TEXT);
    $question_type = optional_param_array('quiztype', ['multiple_choice'], PARAM_RAW);
    $number_of_question = optional_param('totalquiz', 5, PARAM_INT);
    $document_name = required_param('dummyselect', PARAM_TEXT);

    $document_content = '';
    foreach ($documents as $doc) {
        if ($doc['filename'] === $document_name) {
            $document_content = file_get_contents($doc['url']);
            break;
        }
    }

    $payload = [
        "course_id" => 5,
        "module_id" => 5,
        "threshold" => 0.3,
        "limit" => 5,
        "query" => $query,
        "question_type" => implode(", ", $question_type),
        "number_of_question" => (string) $number_of_question
    ];

    $payload_json = json_encode($payload);

    $ch = curl_init('http://165.22.62.163:5000/quiz');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload_json)
    ]);

    $result = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_status != 200) {
        return ['error' => 'API call failed with status: ' . $http_status . ' and response: ' . $result];
    }

    $data = json_decode($result, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Failed to parse JSON response: ' . json_last_error_msg()];
    }

    $questionsdata = [];
    $i = 1;
    if (isset($data['parsed'])) {
        foreach ($data['parsed'] as $q) {
            if ($q['type'] === 'Multiple' || $q['type'] === 'Choice') {
                $answers = [];
                if (isset($q['choices'])) {
                    foreach ($q['choices'] as $choice) {
                        $is_correct = in_array($choice, $q['answer']);
                        $answers[] = [
                            'text' => $choice,
                            'fraction' => $is_correct ? 1.0 : 0.0
                        ];
                    }
                }
                $questionsdata[$i] = [
                    'name' => $q['title'],
                    'text' => $q['title'],
                    'answers' => $answers,
                    'type' => 'multiplechoice'
                ];
            } elseif ($q['type'] === 'Essay') {
                $questionsdata[$i] = [
                    'name' => $q['title'],
                    'text' => $q['title'],
                    'answers' => null,
                    'type' => 'essay'
                ];
            }
            $i++;
        }
    }

    return $questionsdata;
}
