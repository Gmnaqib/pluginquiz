<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/question/editlib.php');

$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('quizgenerator', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
require_login($course, true, $cm);

$PAGE->set_url('/mod/quizgenerator/generate.php', ['id' => $id]);
$PAGE->set_title('Generate Questions');
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

$documents = quizgenerator_get_course_documents($course->id);
$categoryid = quizgenerator_get_question_category($course->id);

// if (!$categoryid) {
//     echo $OUTPUT->notification('Kategori soal tidak ditemukan.', 'notifyproblem');
//     echo $OUTPUT->footer();
//     exit;
// }

if (optional_param('save', false, PARAM_BOOL)) {
    require_once('success.php');
} elseif (optional_param('generate', false, PARAM_BOOL)) {
    require_once('preview.php');
} else {
    require_once('form.php');
}

echo $OUTPUT->footer();

?>

<style>
    .activity-header {
        display: none;
    }
</style>