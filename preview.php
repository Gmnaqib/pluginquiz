<?php
session_start();

$questionsdata = [
    1 => [
        'name' => "Algorithm Basics - Question 1",
        'text' => "Which sorting algorithm has the best average-case time complexity?",
        'answers' => [
            ['text' => 'Bubble Sort', 'fraction' => 0.0],
            ['text' => 'Quick Sort', 'fraction' => 1.0],
            ['text' => 'Insertion Sort', 'fraction' => 0.0]
        ],
        'type' => 'multiplechoice'
    ],
    2 => [
        'name' => "Algorithm Basics - Question 2",
        'text' => "Explain how Dijkstra's algorithm works and its typical use cases.",
        'answers' => null,
        'type' => 'essay'
    ],
    3 => [
        'name' => "Data Structures - Question 3",
        'text' => "Which data structure uses FIFO (First-In-First-Out) principle?",
        'answers' => [
            ['text' => 'Stack', 'fraction' => 0.0],
            ['text' => 'Queue', 'fraction' => 1.0],
            ['text' => 'Tree', 'fraction' => 0.0]
        ],
        'type' => 'multiplechoice'
    ],
    4 => [
        'name' => "Complexity - Question 4",
        'text' => "What is the time complexity of binary search algorithm?",
        'answers' => [
            ['text' => 'O(n)', 'fraction' => 0.0],
            ['text' => 'O(log n)', 'fraction' => 1.0],
            ['text' => 'O(n^2)', 'fraction' => 0.0]
        ],
        'type' => 'multiplechoice'
    ],
    5 => [
        'name' => "Graph Theory - Question 5",
        'text' => "Compare and contrast BFS and DFS algorithms. Provide examples where each would be preferable.",
        'answers' => null,
        'type' => 'essay'
    ]
];

$_SESSION['generated_questions'] = $questionsdata;
?>

<h3>Algorithm Questions Preview</h3>
<button type="button" id="toggleSelectAll" class="btn btn-primary">Select All</button>
<form action="<?= $saveurl ?>" method="post" class="mform">
    <input type="hidden" name="id" value="<?= $id ?>">
    <?php foreach ($questionsdata as $key => $qdata) : ?>
        <div class="card">
            <div class="card-body">
                <input type="checkbox" name="selected_questions[]" value="<?= $key ?>">
                <strong><?= format_string($qdata['name']) ?></strong>
                <p><?= format_text($qdata['text']) ?></p>
                <p><strong>Question Type:</strong> <?= ucfirst($qdata['type']) ?></p>
                <?php if ($qdata['type'] === 'multiplechoice' && !empty($qdata['answers'])) : ?>
                    <ul>
                        <?php foreach ($qdata['answers'] as $index => $answer) : ?>
                            <li>
                                <input type="text" name="answers[<?= $key ?>][<?= $index ?>][text]"
                                    value="<?= format_string($answer['text']) ?>"
                                    class="form-control d-inline w-auto">
                                <input type="radio" name="correct_answer[<?= $key ?>]"
                                    value="<?= $index ?>" <?= $answer['fraction'] > 0 ? 'checked' : '' ?>>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><em>(Essay Question - Requires written answer)</em></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="fitem">
        <div class="felement">
            <input type="submit" name="save" value="Save Selected Questions" class="btn btn-success">
        </div>
    </div>
</form>

<script>
    document.getElementById('toggleSelectAll').addEventListener('click', function() {
        let checkboxes = document.querySelectorAll('input[name="selected_questions[]"]');
        let allChecked = [...checkboxes].every(checkbox => checkbox.checked);

        checkboxes.forEach(checkbox => {
            checkbox.checked = !allChecked;
        });

        this.textContent = allChecked ? "Select All" : "Deselect All";
    });
</script>

<style>
    .card {
        margin: 1rem 0;
        padding: 1rem;
        border: 1px solid #ddd;
        border-radius: 8px;
    }

    ul {
        list-style-type: none;
        padding-left: 0;
    }

    li {
        margin: 0.5rem 0;
    }
</style>