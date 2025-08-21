<?php
// File: mod/quizgenerator/bank.php
require_once('../../config.php');

// Dapatkan parameter courseid dari URL.
$courseid = required_param('courseid', PARAM_INT);
$context = context_course::instance($courseid);
require_login($courseid);

// Jika ingin membatasi hak akses, aktifkan kembali baris di bawah:
// require_capability('moodle/question:view', $context);

global $DB, $OUTPUT, $PAGE;

$PAGE->set_url('/mod/quizgenerator/bank.php', ['courseid' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_title('Bank Soal');
$PAGE->set_heading('Bank Soal');

// Contoh: Ambil semua soal dari tabel 'question' tanpa filter.
// Jika Anda ingin hanya soal di course ini, perlu filter berdasarkan 'question_categories.contextid = $context->id'
$questions = $DB->get_records('question', array(), 'id ASC');

echo $OUTPUT->header();
echo $OUTPUT->heading('Daftar Soal (Bank Soal)');

if ($questions) {
    // Definisikan kolom tabel
    $table = new html_table();
    $table->head = [
        'ID Soal',
        // 'Category ID',
        // 'Nama Kategori',
        'Nama Soal',
        'Tipe Soal',
        'Nilai Default',
        'Teks Soal'
    ];

    foreach ($questions as $question) {
        // Ambil data kategori
        $category = $DB->get_record('question_categories', ['id' => $question->category], 'id, name', IGNORE_MISSING);

        // Format teks soal (agar HTML nya dirender dengan benar)
        $formattedtext = format_text($question->questiontext, $question->questiontextformat);

        // Susun satu baris data untuk tabel
        $table->data[] = [
            $question->id,
            // $question->category,                   // Menampilkan ID kategori
            // $category ? $category->name : '-',     // Menampilkan nama kategori
            format_string($question->name),        // Nama soal
            $question->qtype,                      // Tipe soal (multichoice, essay, dll.)
            $question->defaultmark,                // Nilai default
            $formattedtext                         // Teks soal
        ];
    }

    echo html_writer::table($table);
} else {
    echo $OUTPUT->notification('Tidak ada soal yang ditemukan.', 'notifyproblem');
}

echo $OUTPUT->footer();
