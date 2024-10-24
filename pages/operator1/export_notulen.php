<?php
require dirname(__DIR__, 3) . '/vendor/autoload.php'; // Pastikan ini merujuk ke lokasi yang benar dari autoloader Composer atau file PhpSpreadsheet
include_once('./config/db.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Buat objek Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set default font and font size
$sheet->getStyleByColumnAndRow(1, 1, 6, 1)->getFont()->setName('Calibri');
$sheet->getStyleByColumnAndRow(1, 1, 6, 1)->getFont()->setSize(11);

// Ambil data dari database
$query = "SELECT d.*, a.judul_rapat, a.tgl_rapat, a.uraian_rapat
          FROM detail_rapat AS d
          JOIN agenda AS a ON d.id_agenda = a.id
          ORDER BY a.judul_rapat DESC, a.tgl_rapat DESC, d.bahasan DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

// Header kolom
$sheet->setCellValue('A1', 'No');
$sheet->setCellValue('B1', 'Judul Rapat');
$sheet->setCellValue('C1', 'Tanggal Rapat');
$sheet->setCellValue('D1', 'Bahasan');
$sheet->setCellValue('E1', 'Isi Bahasan');
$sheet->setCellValue('F1', 'Status');
$sheet->setCellValue('G1', 'Disposisi');
$sheet->setCellValue('H1', 'Solusi');

// Set header style
$sheet->getStyle('A1:H1')->getFont()->setBold(true);
$sheet->getStyle('A1:H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID);
$sheet->getStyle('A1:H1')->getFill()->getStartColor()->setARGB('FFC6EFCE');

// Isi data
$rowNumber = 2;
$no = 1;
$judulRapatSebelumnya = '';
while ($row = mysqli_fetch_assoc($result)) {
    if ($row['judul_rapat'] != $judulRapatSebelumnya) {
        $sheet->setCellValue('A' . $rowNumber, $no);
        $sheet->setCellValue('B' . $rowNumber, htmlspecialchars($row['judul_rapat']));
        $sheet->setCellValue('C' . $rowNumber, htmlspecialchars($row['tgl_rapat']));
        $judulRapatSebelumnya = $row['judul_rapat'];
        $no++;
    } else {
        $sheet->setCellValue('A' . $rowNumber, '');
    }
    $sheet->setCellValue('D' . $rowNumber, htmlspecialchars($row['bahasan']));
    $sheet->setCellValue('E' . $rowNumber, htmlspecialchars($row['usulan']));
    $sheet->setCellValue('F' . $rowNumber, htmlspecialchars($row['status']));
    $sheet->setCellValue('G' . $rowNumber, htmlspecialchars($row['disposisi']));
    $sheet->setCellValue('H' . $rowNumber, htmlspecialchars($row['solusi']));
    $rowNumber++;
}

// Set wrap text untuk kolom Isi Bahasan dan Usulan
$sheet->getStyle('E')->getAlignment()->setWrapText(true);
$sheet->getStyle('E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Add this line

$sheet->getStyle('D')->getAlignment()->setWrapText(true);
$sheet->getStyle('D')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Add this line

$sheet->getStyle('H')->getAlignment()->setWrapText(true);
$sheet->getStyle('H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Add this line
// Set lebar kolom
$sheet->getColumnDimension('A')->setWidth(5);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(30);
$sheet->getColumnDimension('E')->setWidth(40);
$sheet->getColumnDimension('F')->setWidth(10);
$sheet->getColumnDimension('G')->setWidth(15);
$sheet->getColumnDimension('H')->setWidth(20);

// Set border
$sheet->getStyle('A1:H' . $rowNumber)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Menulis file Excel
$writer = new Xlsx($spreadsheet);

// Set nama file
$filename = 'notulen_rapat.xlsx';

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