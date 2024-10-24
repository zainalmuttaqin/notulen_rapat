<?php
include_once('./config/db.php');

// Pastikan request menggunakan metode POST dan data rapat serta judul ada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rapat']) && isset($_POST['judul'])) {
    $rapatIds = $_POST['rapat'];
    $judulAktaSidang = trim($_POST['judul']);
    
    // Pastikan ada data rapat yang dipilih dan judul akta tidak kosong
    if (count($rapatIds) > 0 && !empty($judulAktaSidang)) {
        // Gabungkan semua id_rapat menjadi satu string
        $id_rapat_list = !empty($rapatIds) ? implode(',', $rapatIds) : null;        
        // Ambil data dari rapat pertama sebagai contoh
        $firstRapatId = intval($rapatIds[0]); // Konversi ke integer untuk id_rapat
        
        // Query untuk mengambil detail rapat berdasarkan ID
        $sql = "SELECT a.judul_rapat, a.tgl_rapat, a.uraian_rapat, 
                       d.bahasan, d.usulan, d.disposisi, d.status, d.solusi
                FROM agenda a
                JOIN detail_rapat d ON a.id = d.id_agenda
                WHERE d.id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $firstRapatId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Periksa apakah data rapat ditemukan
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Query untuk memasukkan data akta sidang
            $sql_insert = "INSERT INTO akta_sidang (judul_akta_sidang, id_rapat, id_rapat_list, judul_rapat, tgl_rapat, uraian_rapat, bahasan, usulan, disposisi, status, solusi)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param(
                "sisssssssss",
                $judulAktaSidang,
                $firstRapatId,
                $id_rapat_list,
                $row['judul_rapat'],
                $row['tgl_rapat'],
                $row['uraian_rapat'],
                $row['bahasan'],
                $row['usulan'],
                $row['disposisi'],
                $row['status'],
                $row['solusi']
            );

            // Eksekusi query insert
            if ($stmt_insert->execute()) {
                header('Content-Type: application/json');
                echo json_encode(array('success' => true, 'message' => 'Akta sidang berhasil dibuat.'));
            } else {
                error_log("Error inserting akta sidang: " . $stmt_insert->error);
                header('Content-Type: application/json');
                echo json_encode(array('success' => false, 'message' => 'Terjadi kesalahan saat memasukkan data: ' . $stmt_insert->error));
            }
            $stmt_insert->close();
        } else {
            // Jika rapat tidak ditemukan
            header('Content-Type: application/json');
            echo json_encode(array('success' => false, 'message' => 'Data rapat tidak ditemukan.'));
        }
        $stmt->close();
    } else {
        // Jika tidak ada rapat yang dipilih atau judul kosong
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'Pilih rapat dan masukkan judul akta sidang.'));
    }
} else {
    // Jika request tidak valid
    header('Content-Type: application/json');
    echo json_encode(array('success' => false, 'message' => 'Request tidak valid.'));
}

$conn->close();
?>
