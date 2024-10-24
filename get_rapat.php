<?php
include_once('./config/db.php');

$sql = "SELECT d.id, a.judul_rapat, a.tgl_rapat, a.uraian_rapat, 
                d.bahasan
        FROM agenda a
        JOIN detail_rapat d ON a.id = d.id_agenda
        WHERE d.status = 'selesai' AND d.id NOT IN (SELECT id_rapat FROM akta_sidang)";

$result = $conn->query($sql);
$output = '';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $output .= '<tr>
                        <td>
                            <input class="form-check-input rapat-checkbox" type="checkbox" name="rapat[]" id="rapat' . $row['id'] . '" value="' . $row['id'] . '">
                            <label class="form-check-label" for="rapat' . $row['id'] . '">Pilih</label>
                        </td>
                        <td>' . htmlspecialchars($row['judul_rapat']) . '</td>
                        <td>' . date('d/m/Y', strtotime($row['tgl_rapat'])) . '</td>
                        <td>' . htmlspecialchars($row['bahasan']) . '</td>
                    </tr>';
    }
} else {
    $output .= '<tr><td colspan="4">Tidak ada rapat yang berstatus selesai.</td></tr>';
}

echo $output;
?>