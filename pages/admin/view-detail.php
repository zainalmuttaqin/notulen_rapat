<?php
require_once('../../config/db.php');

$id_detail = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = "SELECT a.judul_rapat, a.tgl_rapat, a.uraian_rapat, dr.bahasan, dr.usulan, dr.solusi, dr.status, dr.disposisi FROM agenda a JOIN detail_rapat dr ON a.id = dr.id_agenda WHERE dr.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_detail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Display the data in a structured format
    echo "<h4><strong>Judul Rapat:</strong> " . htmlspecialchars($row['judul_rapat']) . "</h4>";
    echo "<p><strong>Tanggal Rapat:</strong> " . date('d/m/Y', strtotime($row['tgl_rapat'])) . "</p>";
    echo "<p style='text-align: justify'><strong>Uraian Rapat:</strong> " . htmlspecialchars($row['uraian_rapat']) . "</p>";
    echo "<h5><strong>Detail Rapat:</strong></h5>";
    echo "<p><strong>Bahasan:</strong> " . htmlspecialchars($row['bahasan']) . "</p>";
    echo "<p style='text-align: justify'><strong>Isi Bahasan:</strong> " . htmlspecialchars($row['usulan']) . "</p>";
    echo "<p style='text-align: justify'><strong>Solusi:</strong> " . htmlspecialchars($row['solusi']) . "</p>";
    echo "<p><strong>Status:</strong> " . htmlspecialchars($row['status']) . "</p>";
    echo "<p><strong>Disposisi:</strong> " . htmlspecialchars($row['disposisi']) . "</p>";
} else {
    echo "Tidak ada data detail yang ditemukan.";
}

$stmt->close();
$conn->close();
?>