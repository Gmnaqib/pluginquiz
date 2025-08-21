<?php
$formurl = new moodle_url('/mod/quizgenerator/generate.php', ['id' => $id]);
?>
<h3>Generate Quiz</h3>
<form action="<?= $formurl ?>" method="post" class="mform">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="fitem">
        <label for="quizquery" class="fitemtitle">Quiz Query</label>
        <div class="felement">
            <input type="text" id="quizquery" name="quizquery" class="form-control" placeholder="Type keywords...">
        </div>
    </div>

    <div class="fitem">
        <label for="dummyselect" class="fitemtitle">Quiz Material</label>
        <div class="felement">
            <select id="dummyselect" name="dummyselect" class="custom-select">
                <?php if (!empty($documents)) : ?>
                    <?php foreach ($documents as $doc) : ?>
                        <option value="<?= $doc['filename'] ?>">
                            <?= format_string($doc['coursemodule']) . ' - ' . format_string($doc['filename']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php else : ?>
                    <option value="">Tidak ada dokumen</option>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <div class="fitem">
        <label class="fitemtitle">Quiz Type</label>
        <div class="felement">
            <label><input type="checkbox" name="quiztype[]" value="multiple_choice" checked> Multiple Choice</label>
            <label><input type="checkbox" name="quiztype[]" value="essay"> Essay</label>
        </div>
    </div>

    <div class="fitem">
        <label for="totalquiz" class="fitemtitle">Total Quiz</label>
        <div class="felement">
            <input type="number" id="totalquiz" name="totalquiz" class="form-control" min="1" value="5">
        </div>
    </div>

    <div class="fitem">
        <div class="felement">
            <input type="submit" name="generate" value="Generate Questions" class="btn btn-primary">
        </div>
    </div>
</form>

<style>
    .fitem {
        margin-bottom: 1rem;
    }
</style>