<?php
include_once('./config/db.php');

// Pastikan tidak ada spasi atau baris kosong sebelum tag PHP

header('Content-Type: application/json'); // Set header untuk JSON

// Query untuk mengambil detail rapat dan judul rapat
$query = "SELECT dr.id, dr.bahasan, a.judul_rapat 
          FROM detail_rapat dr
          JOIN agenda a ON dr.id_agenda = a.id
          WHERE dr.status IN ('belum diproses', 'disposisi')";

$result = $conn->query($query);

$details = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $details[] = $row;
    }
}

echo json_encode($details);
?>