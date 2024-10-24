<?php
ob_start();

require dirname(__DIR__, 3) . '/vendor/autoload.php';
include_once('./config/db.php');

use Fpdf\Fpdf;

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Akta Sidang', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    function formatTanggal($tanggal) {
        // Mengubah format dari yyyy-mm-dd ke dd-mm-yyyy
        $dateTime = DateTime::createFromFormat('Y-m-d', $tanggal);
        return $dateTime ? $dateTime->format('d-m-Y') : $tanggal;
    }

    function CreateTable($data) {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Judul Akta Sidang: ' . htmlspecialchars($data['judul_akta_sidang']), 0, 1);
        $this->Ln(5);
    
        // Parse data lists
        $judul_rapat_list = explode('||', $data['judul_rapat_list']);
        $tgl_rapat_list = explode('||', $data['tgl_rapat_list']);
        $tgl_rapat_list = array_map([$this, 'formatTanggal'], $tgl_rapat_list);
        $uraian_rapat_list = explode('||', $data['uraian_rapat_list']);
        $bahasan_list = explode('||', $data['bahasan_list']);
        $usulan_list = explode('||', $data['usulan_list']);
        $disposisi_list = explode('||', $data['disposisi_list']);
        $status_list = explode('||', $data['status_list']);
        $solusi_list = explode('||', $data['solusi_list']);
    
        $this->SetFont('Arial', '', 10);
        
        // Define label width and content width
        $labelWidth = 50;
        $contentWidth = $this->GetPageWidth() - $labelWidth - 20; // 20 for margins
        
        // Create rows with better alignment
        $this->MultiCellRow('Judul Rapat', implode(", ", array_map('htmlspecialchars', $judul_rapat_list)), $labelWidth, $contentWidth);
        $this->MultiCellRow('Tanggal Rapat', implode(", ", $tgl_rapat_list), $labelWidth, $contentWidth);
        $this->MultiCellRow('Uraian Rapat', implode("\n", array_map('htmlspecialchars', $uraian_rapat_list)), $labelWidth, $contentWidth);
        $this->MultiCellRow('Bahasan', implode("\n", array_map('htmlspecialchars', $bahasan_list)), $labelWidth, $contentWidth, true);
        $this->MultiCellRow('Isi Bahasan', implode("\n", array_map('htmlspecialchars', $usulan_list)), $labelWidth, $contentWidth, true);
        $this->MultiCellRow('Disposisi', implode(", ", array_map('htmlspecialchars', $disposisi_list)), $labelWidth, $contentWidth);
        $this->MultiCellRow('Status', implode(", ", array_map('htmlspecialchars', $status_list)), $labelWidth, $contentWidth);
        $this->MultiCellRow('Solusi', implode("\n", array_map('htmlspecialchars', $solusi_list)), $labelWidth, $contentWidth, true);
    }
    
    function MultiCellRow($label, $content, $labelWidth, $contentWidth, $isMultiLine = false) {
        $startX = $this->GetX();
        $startY = $this->GetY();
        
        // Calculate content height before drawing
        $this->SetFont('Arial', '', 10);
        $contentLines = $this->NbLines($contentWidth, $content);
        $contentHeight = max(10, $contentLines * 10); // Minimum height of 10
        
        // Draw label cell
        $this->SetFont('Arial', 'B', 10);
        
        // Draw label background and text
        if ($isMultiLine) {
            $this->Rect($startX, $startY, $labelWidth, $contentHeight);
            $this->SetXY($startX, $startY);
            $this->Cell($labelWidth, $contentHeight, $label, 0, 0, 'L');
        } else {
            $this->Rect($startX, $startY, $labelWidth, $contentHeight);
            $this->SetXY($startX, $startY);
            $this->Cell($labelWidth, $contentHeight, $label, 0, 0, 'L');
        }
        
        // Draw content
        $this->SetFont('Arial', '', 10);
        $this->SetXY($startX + $labelWidth, $startY);
        
        // Create content cell with border
        $this->MultiCell($contentWidth, 10, $content, 1, 'L');
        
        // Move to next row
        $this->SetXY($startX, $startY + $contentHeight);
    }
    
    // Helper function to calculate number of lines
    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'',$txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i<$nb) {
            $c = $s[$i];
            if($c=="\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep = $i;
            $l += $cw[$c];
            if($l>$wmax) {
                if($sep==-1) {
                    if($i==$j)
                        $i++;
                }
                else
                    $i = $sep+1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
}


if (isset($_GET['id'])) {
    $id = $_GET['id'];
    include_once('./config/db.php');

    // Ambil data akta sidang berdasarkan ID
    $sql = "SELECT a.*, 
         GROUP_CONCAT(DISTINCT ag.judul_rapat SEPARATOR '||') as judul_rapat_list,
         GROUP_CONCAT(DISTINCT ag.tgl_rapat SEPARATOR '||') as tgl_rapat_list,
         GROUP_CONCAT(DISTINCT ag.uraian_rapat SEPARATOR '||') as uraian_rapat_list,
         GROUP_CONCAT(DISTINCT d.bahasan SEPARATOR '||') as bahasan_list,
         GROUP_CONCAT(DISTINCT d.usulan SEPARATOR '||') as usulan_list,
         GROUP_CONCAT(DISTINCT d.disposisi SEPARATOR '||') as disposisi_list,
         GROUP_CONCAT(DISTINCT d.status SEPARATOR '||') as status_list,
         GROUP_CONCAT(DISTINCT d.solusi SEPARATOR '||') as solusi_list
         FROM akta_sidang a
         LEFT JOIN detail_rapat d ON FIND_IN_SET(d.id, a.id_rapat_list)
         LEFT JOIN agenda ag ON d.id_agenda = ag.id
         WHERE a.id = ?
         GROUP BY a.id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        $pdf = new PDF();
        $pdf->AddPage();

        // Buat tabel dengan data
        $pdf->CreateTable($data);
    } else {
        echo "Data tidak ditemukan.";
    }
} else {
    echo "ID tidak diberikan.";
}

ob_end_clean();

ob_end_clean();
// Output PDF
$pdf->Output('I', 'akta_sidang.pdf');
exit;
?>