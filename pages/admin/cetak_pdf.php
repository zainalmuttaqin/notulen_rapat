<?php
// Mulai output buffering
ob_start();

require dirname(__DIR__, 3) . '/vendor/autoload.php';
include_once('./config/db.php');

use Fpdf\Fpdf;

class PDF extends Fpdf
{
    // Method untuk header
    function Header()
    {
        if ($this->PageNo() == 1) {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(80);
            $this->Cell(30, 10, 'Laporan Notulen Rapat', 0, 1, 'C');
            $this->Ln(2);
        }
    }

    // Method untuk footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    // Custom method untuk justified text dengan label yang diperbaiki
    function JustifyText($label, $text, $width)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(30, 5, $label, 0, 0, 'L'); // Menambah lebar untuk label

        // Tambahkan ":" di posisi tetap
        $this->Cell(10, 5, ':', 0, 0, 'L');

        $this->SetFont('Arial', '', 10);
        $this->MultiCell($width - 40, 5, $text, 0, 'J'); // Sesuaikan lebar isi konten
    }

        // Fungsi untuk menghitung tinggi teks dalam sebuah kolom
    function GetStringHeight($text, $width)
    {
        // Simpan posisi dan pengaturan sebelumnya
        $prevY = $this->GetY();
        $prevFont = $this->FontSizePt;

        // Simulasikan tinggi dengan MultiCell
        $this->MultiCell($width, 6, $text, 0, 'L');
        $newY = $this->GetY();
        
        // Kembalikan ke posisi semula
        $this->SetY($prevY);
        $this->SetFont('Arial', '', $prevFont);

        // Kembalikan tinggi teks yang diperlukan
        return $newY - $prevY;
    }

    // Fungsi tambahan untuk menghitung tinggi teks di dalam MultiCell
    function GetMultiCellHeight($width, $lineHeight, $text) {
        $pdf = new FPDF();
        $pdf->SetFont('Arial', '', 10);
        $pdf->AddPage();
        $pdf->SetXY(0, 0); // Posisi awal
        $pdf->MultiCell($width, $lineHeight, $text);
        $height = $pdf->GetY(); // Dapatkan posisi Y akhir setelah MultiCell
        return $height; // Kembalikan tinggi dari MultiCell
    }
}

// Buat instance PDF baru
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Ubah nama parameter dalam URL menjadi id_agenda
$id_agenda = $_GET['id'];

// Query the database
$query = "SELECT * FROM agenda WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_agenda);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die("Query gagal dijalankan");
}

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $judul_rapat = $row['judul_rapat'];
    $tgl_rapat = date('d-m-Y', strtotime($row['tgl_rapat']));
    $uraian_rapat = $row['uraian_rapat'];
} else {
    die("No data found");
}

// Generate the PDF report
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(30, 5, 'Judul Rapat', 0, 0, 'L');
$pdf->Cell(10, 5, ':', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 5, $judul_rapat, 0, 'L');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(30, 5, 'Tanggal Rapat', 0, 0, 'L');
$pdf->Cell(10, 5, ':', 0, 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 5, $tgl_rapat, 0, 'L');

// Justify uraian rapat dengan label yang diperbaiki
$pdf->JustifyText('Uraian', $uraian_rapat, 180);

// Tambahkan detail rapat
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Detail Rapat', 0, 1, 'C');
$pdf->Ln(5);

// Header tabel Detail Rapat
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 7, 'No', 1, 0, 'C');
$pdf->Cell(30, 7, 'Bahasan', 1, 0, 'C');
$pdf->Cell(50, 7, 'Isi Bahasan', 1, 0, 'C');
$pdf->Cell(30, 7, 'Disposisi', 1, 0, 'C');
$pdf->Cell(20, 7, 'Status', 1, 0, 'C');
$pdf->Cell(50, 7, 'Solusi', 1, 1, 'C');

// Isi tabel Detail Rapat
$query = "SELECT * FROM detail_rapat WHERE id_agenda = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_agenda);
$stmt->execute();
$result = $stmt->get_result();

$no = 1;
while ($row = $result->fetch_assoc()) {
    $pdf->SetFont('Arial', '', 10);

    // Hitung tinggi maksimum dari setiap sel dalam satu baris
    $bahasanHeight = $pdf->GetMultiCellHeight(30, 6, $row['bahasan']);
    $usulanHeight = $pdf->GetMultiCellHeight(50, 6, $row['usulan']);
    $solusiHeight = $pdf->GetMultiCellHeight(50, 6, $row['solusi']);
    
    // Dapatkan tinggi maksimum dari semua kolom
    $rowHeight = max($bahasanHeight, $usulanHeight, $solusiHeight);

    // Simpan posisi awal (X, Y) untuk memulai baris baru
    $xStart = $pdf->GetX();
    $yStart = $pdf->GetY();

    // Cetak kolom "No"
    $pdf->Cell(10, $rowHeight, $no, 1, 0, 'C');

    // Cetak kolom "Bahasan" dengan MultiCell
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell(30, 6, $row['bahasan'], 0, 'L');
    $pdf->SetXY($x + 30, $yStart); // Kembali ke posisi setelah MultiCell

    // Cetak kolom "Isi Bahasan" (Usulan) dengan MultiCell
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell(50, 6, $row['usulan'], 0, 'L');
    $pdf->SetXY($x + 50, $yStart); // Kembali ke posisi setelah MultiCell

    // Cetak kolom "Disposisi"
    $pdf->Cell(30, $rowHeight, $row['disposisi'], 1, 0, 'C');

    // Cetak kolom "Status"
    $pdf->Cell(20, $rowHeight, $row['status'], 1, 0, 'C');

    // Cetak kolom "Solusi" dengan MultiCell
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell(50, 6, $row['solusi'], 0, 'L');
    $pdf->SetXY($x + 50, $yStart); // Kembali ke posisi setelah MultiCell

    // Gambar manual garis vertikal untuk semua kolom
    $pdf->Rect($xStart, $yStart, 10, $rowHeight); // Kolom No
    $pdf->Rect($xStart + 10, $yStart, 30, $rowHeight); // Kolom Bahasan
    $pdf->Rect($xStart + 40, $yStart, 50, $rowHeight); // Kolom Isi Bahasan
    $pdf->Rect($xStart + 90, $yStart, 30, $rowHeight); // Kolom Disposisi
    $pdf->Rect($xStart + 120, $yStart, 20, $rowHeight); // Kolom Status
    $pdf->Rect($xStart + 140, $yStart, 50, $rowHeight); // Kolom Solusi

    // Pindah ke baris berikutnya
    $pdf->Ln($rowHeight);

    $no++;
}



// Setelah tabel Detail Rapat, tambahkan jarak vertikal untuk memastikan tabel berikutnya tidak bertumpuk
$pdf->Ln(10);

// Tabel Absensi
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Daftar Hadir', 0, 1, 'C');
$pdf->Ln(5);

// Header tabel Absensi
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 7, 'No', 1, 0, 'C');
$pdf->Cell(50, 7, 'Nama', 1, 1, 'C');

// Isi tabel Absensi
$query_absen = "SELECT * FROM absen WHERE id_agenda = ?";
$stmt_absen = $conn->prepare($query_absen);
$stmt_absen->bind_param("i", $id_agenda);
$stmt_absen->execute();
$result_absen = $stmt_absen->get_result();

$no_absen = 1;
while ($row_absen = $result_absen->fetch_assoc()) {
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(10, 7, $no_absen, 1, 0, 'C');
    $pdf->Cell(50, 7, $row_absen['nama'], 1, 1, 'L');
    $no_absen++;
}

ob_end_clean();

// Output PDF
ob_end_clean();
$pdf->Output('I', 'notulen_rapat.pdf');
exit;
