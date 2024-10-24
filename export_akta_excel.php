<?php
require 'C:/xampp/htdocs/notulen-rapat/vendor/autoload.php';
include_once('./config/db.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Ambil data dari tabel akta_sidang
$sql = "SELECT a.*, 
    GROUP_CONCAT(DISTINCT ag.judul_rapat SEPARATOR ', ') as judul_rapat,
    GROUP_CONCAT(DISTINCT ag.tgl_rapat SEPARATOR ', ') as tgl_rapat,
    GROUP_CONCAT(DISTINCT ag.uraian_rapat SEPARATOR ', ') as uraian_rapat,
    GROUP_CONCAT(DISTINCT d.bahasan SEPARATOR ', ') as bahasan,
    GROUP_CONCAT(DISTINCT d.usulan SEPARATOR ', ') as usulan,
    GROUP_CONCAT(DISTINCT d.disposisi SEPARATOR ', ') as disposisi,
    GROUP_CONCAT(DISTINCT d.status SEPARATOR ', ') as status,
    GROUP_CONCAT(DISTINCT d.solusi SEPARATOR ', ') as solusi
    FROM akta_sidang a
    LEFT JOIN detail_rapat d ON FIND_IN_SET(d.id, a.id_rapat_list)
    LEFT JOIN agenda ag ON d.id_agenda = ag.id
    GROUP BY a.id
    ORDER BY a.id DESC";

$result = $conn->query($sql);

// Buat spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set header kolom
$headers = [
    'A1' => 'No.',
    'B1' => 'Judul Akta Sidang',
    'C1' => 'Judul Rapat',
    'D1' => 'Tanggal Rapat',
    'E1' => 'Uraian Rapat',
    'F1' => 'Bahasan',
    'G1' => 'Isi Bahasan',
    'H1' => 'Disposisi',
    'I1' => 'Status',
    'J1' => 'Solusi'
];

foreach ($headers as $cell => $label) {
    $sheet->setCellValue($cell, $label);
    // Set font bold
    $sheet->getStyle($cell)->getFont()->setBold(true);
    // Set fill color
    $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID);
    $sheet->getStyle($cell)->getFill()->getStartColor()->setARGB('FFC6EFCE');
    // Set border
    $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    // Set alignment
    $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

// Isi data ke dalam spreadsheet
$rowNumber = 2;
$no = 1;
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowNumber, $no++);
    $sheet->setCellValue('B' . $rowNumber, htmlspecialchars($row['judul_akta_sidang']));
    $sheet->setCellValue('C' . $rowNumber, $row['judul_rapat']);
    $sheet->setCellValue('D' . $rowNumber, $row['tgl_rapat']);
    $sheet->setCellValue('E' . $rowNumber, $row['uraian_rapat']);
    $sheet->setCellValue('F' . $rowNumber, $row['bahasan']);
    $sheet->setCellValue('G' . $rowNumber, $row['usulan']);
    $sheet->setCellValue('H' . $rowNumber, $row['disposisi']);
    $sheet->setCellValue('I' . $rowNumber, $row['status']);
    $sheet->setCellValue('J' . $rowNumber, $row['solusi']);
    $rowNumber++;
}

// Set wrap text pada kolom yang diinginkan
$wrapTextColumns = ['E', 'F', 'G', 'H', 'I', 'J']; // Kolom E, F, G, H, I, dan J
foreach ($wrapTextColumns as $column) {
    $sheet->getStyle($column . '1:' . $column . ($rowNumber - 1))->getAlignment()->setWrapText(true);
}

// Set alignment untuk kolom yang diinginkan $alignmentColumns = ['A', 'B', 'C', 'D']; // Kolom A, B, C, dan D
foreach ($alignmentColumns as $column) {
    $sheet->getStyle($column . '1:' . $column . ($rowNumber - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

// Set lebar kolom
$columnWidths = [
    'A' => 5,
    'B' => 20,
    'C' => 20,
    'D' => 15,
    'E' => 40,
    'F' => 30,
    'G' => 40,
    'H' => 20,
    'I' => 10,
    'J' => 20
];
foreach ($columnWidths as $column => $width) {
    $sheet->getColumnDimension($column)->setWidth($width);
}

// Set border untuk semua cell
$sheet->getStyle('A1:J' . ($rowNumber - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Menulis file Excel
$writer = new Xlsx($spreadsheet);

// Set nama file
$filename = 'akta_sidang.xlsx';

// Header HTTP untuk mendownload file Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Content-Transfer-Encoding: binary');
header('Pragma: public');
header('Expires: 0');

ob_end_clean(); // Add this to ensure no extra output is sent

// Tulis ke output
$writer->save('php://output');
exit;