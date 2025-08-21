<?php
session_start();

if (isset($_POST['answers'])) {
    foreach ($_POST['answers'] as $questionKey => $answers) {
        foreach ($answers as $answerIndex => $answerData) {
            $_SESSION['generated_questions'][$questionKey]['answers'][$answerIndex]['text'] = $answerData['text'];
        }
    }
}

if (isset($_POST['correct_answer'])) {
    foreach ($_POST['correct_answer'] as $questionKey => $correctIndex) {
        foreach ($_SESSION['generated_questions'][$questionKey]['answers'] as $answerIndex => &$answer) {
            $answer['fraction'] = ($answerIndex == $correctIndex) ? 1.0 : 0.0;
        }
    }
}

$selected = optional_param_array('selected_questions', [], PARAM_INT);
$generated = $_SESSION['generated_questions'] ?? [];
$saved_ids = [];

if (!empty($selected) && !empty($generated)) {
    foreach ($generated as $key => $qdata) {
        if (in_array($key, $selected)) {
            $qdataobj = new stdClass();
            $qdataobj->name = $qdata['name'];
            $qdataobj->text = $qdata['text'];
            $qdataobj->answers = $qdata['answers'];
            $questionid = quizgenerator_create_question($categoryid, $qdataobj);
            if ($questionid) {
                $saved_ids[] = $questionid;
            }
        }
    }
    unset($_SESSION['generated_questions']);
    echo $OUTPUT->notification(count($saved_ids) . ' questions were successfully saved to the questions bank!', 'notifysuccess');
} else {
    echo $OUTPUT->notification('There are no selected questions.', 'notifyproblem');
}
