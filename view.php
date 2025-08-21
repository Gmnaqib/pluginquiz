<?php
require_once('../../config.php');

$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('quizgenerator', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/quizgenerator/view.php', ['id' => $id]);
$PAGE->set_title('Quiz Generator');
$PAGE->set_heading($course->fullname);

$question_bank_url = new moodle_url('/question/edit.php', ['courseid' => $course->id]);

// redirect langsung ke generate.php
redirect(new moodle_url('/mod/quizgenerator/generate.php', ['id' => $id]));

echo $OUTPUT->header();
echo '<h2>Welcome to Quiz Generator</h2>';
echo '<a href="generate.php?id=' . $id . '">Generate Questions</a><br/>';
echo '<a href="' . $question_bank_url->out() . '" target="blank">Go to Question Bank</a>';
echo $OUTPUT->footer();
