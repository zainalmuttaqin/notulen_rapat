<div class="row mt-3">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">
            <?php
                include_once('./config/db.php');

                // Ambil data dari detail_rapat yang berstatus selesai
                // Ubah query SQL untuk mengambil data yang belum masuk ke akta_sidang
$sql1 = "SELECT d.id, a.judul_rapat, a.tgl_rapat, a.uraian_rapat, 
d.bahasan, d.usulan, d.disposisi, d.status, d.solusi
FROM agenda a
JOIN detail_rapat d ON a.id = d.id_agenda 
WHERE d.status = 'selesai' 
AND d.id NOT IN (
    SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(id_rapat_list, ',', numbers.n), ',', -1) as id
    FROM akta_sidang
    CROSS JOIN (
        SELECT 1 + ones.n + tens.n * 10 as n
        FROM (SELECT 0 as n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) ones
        CROSS JOIN (SELECT 0 as n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
        WHERE 1 + ones.n + tens.n * 10 <= (
            SELECT MAX(LENGTH(id_rapat_list) - LENGTH(REPLACE(id_rapat_list, ',', '')) + 1)
            FROM akta_sidang
        )
    ) numbers
    WHERE id_rapat_list != ''
)
ORDER BY d.id DESC";

                $result1 = $conn->query($sql1);

                // Periksa apakah query berhasil
                if (!$result1) {
                    die("Query gagal: " . $conn->error);
                }

                // Tentukan jumlah item per halaman
                $items_per_page = 10;

                // Tentukan halaman saat ini
                $current_page = isset($_GET['page_number']) ? (int)$_GET['page_number'] : 1;

                // Hitung offset
                $offset = ($current_page - 1) * $items_per_page;

                // Modifikasi query untuk pagination
            $sql2 = "SELECT a.*, 
            GROUP_CONCAT(DISTINCT ag.judul_rapat ORDER BY ag.id SEPARATOR '<br>') as judul_rapat,
            GROUP_CONCAT(DISTINCT ag.tgl_rapat ORDER BY ag.id SEPARATOR '<br>') as tgl_rapat,
            GROUP_CONCAT(DISTINCT ag.uraian_rapat ORDER BY ag.id SEPARATOR '<br>') as uraian_rapat,
            GROUP_CONCAT(DISTINCT d.bahasan ORDER BY d.id SEPARATOR '<br>') as bahasan,
            GROUP_CONCAT(DISTINCT d.usulan ORDER BY d.id SEPARATOR '<br>') as usulan,
            GROUP_CONCAT(DISTINCT d.disposisi ORDER BY d.id SEPARATOR '<br>') as disposisi,
            GROUP_CONCAT(DISTINCT d.status ORDER BY d.id SEPARATOR '<br>') as status,
            GROUP_CONCAT(DISTINCT d.solusi ORDER BY d.id SEPARATOR '<br>') as solusi
            FROM akta_sidang a
            LEFT JOIN detail_rapat d ON FIND_IN_SET(d.id, a.id_rapat_list)
            LEFT JOIN agenda ag ON d.id_agenda = ag.id
            GROUP BY a.id
            ORDER BY a.id DESC 
            LIMIT $items_per_page OFFSET $offset";
                $result2 = $conn->query($sql2);

                // Periksa apakah query berhasil
                if (!$result2) {
                    die("Query gagal: " . $conn->error);
                }

                // Hitung total data
                $total_sql = "SELECT COUNT(*) as total FROM akta_sidang";
                $total_result = $conn->query($total_sql);

                // Periksa apakah query berhasil
                if (!$total_result) {
                    die("Query gagal: " . $conn->error);    
                }

                $total_row = $total_result->fetch_assoc();
                $total_items = $total_row['total'];

                // Hitung total halaman
                $total_pages = ceil($total_items / $items_per_page);


                if (isset($_GET['action']) && $_GET['action'] == 'hapus') {
                    $id = $_GET['id'];
                    $query = "DELETE FROM akta_sidang WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $id);

                    if ($stmt->execute()) {
                        $stmt->close();
                        ?>
                        <script src="vendors/sweetalert/sweetalert.min.js"></script>
                        <script type="text/javascript">
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Akta sidang berhasil dihapus.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "index.php?page=akta";
                                    loadRapat();
                                }
                            });
                        </script>
                        <?php
                    } else {
                        $stmt->close();
                        ?>
                        <script src="vendors/sweetalert/sweetalert.min.js"></script>
                        <script type="text/javascript">
                            Swal.fire({
                                title: 'Gagal!',
                                text: 'Terjadi kesalahan saat menghapus akta sidang.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        </script>
                        <?php
                    }
                }
                ?>
                <div class="container">
                <h2>Akta Sidang</h2>

                <button id="buatAktaSidang" class="btn btn-primary">Buat Akta Sidang</button>
                <button id="cetakExcel" class="btn btn-success float-right">Cetak Excel</button>
                <br>
                <br>
                <table class="table table-striped table-bordered table-responsive">
                <thead>
                        <tr>
                            <th class="text-center">No.</th>
                            <th class="text-center">Judul Akta Sidang</th>
                            <th class="text-center">Judul Rapat</th>
                            <th class="text-center">Tanggal Rapat</th>
                            <th class="text-center">Uraian Rapat</th>
                            <th class="text-center">Bahasan</th>
                            <th class="text-center">Isi Bahasan</th>
                            <th class="text-center">Disposisi</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Solusi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = ($current_page - 1) * $items_per_page + 1;
                        while ($row = $result2->fetch_assoc()) {
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['judul_akta_sidang']); ?></td>
                                <td class="text-center"><?= $row['judul_rapat']; ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_rapat'])); ?></td>
                                <td style="text-align: justify"><?= $row['uraian_rapat']; ?></td>
                                <td style="text-align: justify"><?= $row['bahasan']; ?></td>
                                <td style="text-align: justify"><?= $row['usulan']; ?></td>
                                <td class="text-center"><?= $row['disposisi']; ?></td>
                                <td class="text-center"><?= $row['status']; ?></td>
                                <td style="text-align: justify"><?= $row['solusi']; ?></td>
                                <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="akta_sidang.php?action=hapus&id=<?= $row['id']; ?>" data-id="<?= $row['id']; ?>" data-judul="<?= $row['judul_akta_sidang']; ?>" class="btn btn-danger btn-sm me-2" title="Hapus"><i class="fa fa-trash"></i></a>
                                    <a href="?page=akta&action=cetak&id=<?= $row['id']; ?>" class="btn btn-primary btn-sm" target="_blank" title="Cetak PDF"><i class="fa fa-print"></i></a>
                                </div>    
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <nav aria-label="Page navigation example">
    <ul class="pagination">
        <?php
        for ($i = 1; $i <= $total_pages; $i++) {
            ?>
            <li class="page-item <?= ($i == $current_page) ? 'active' : ''; ?>">
                <a class="page-link" href="?page=akta&page_number=<?= $i; ?>"><?= $i; ?></a>
            </li>
            <?php
        }
        ?>
    </ul>
</nav>
            </div>
        </div>
    </div>
</div>  

<div class="modal fade" id="rapatModal" tabindex="-1" role="dialog" aria-labelledby="rapatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rapatModalLabel">Pilih Detail Rapat</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="rapatForm">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" name="selectAll">
                                    <label for="selectAll">Pilih Semua</label>
                                </th>
                                <th>Judul Rapat</th>
                                <th>Tanggal Rapat</th>
                                <th>Bahasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result1->num_rows > 0): ?>
                                <?php while($row = $result1->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <input class="form-check-input rapat-checkbox" type="checkbox" name="rapat[]" id="rapat<?php echo $row['id']; ?>" value="<?php echo $row['id']; ?>">
                                            <label class="form-check-label" for="rapat<?php echo $row['id']; ?>">Pilih</label>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['judul_rapat']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tgl_rapat'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['bahasan']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">Tidak ada rapat yang berstatus selesai.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="form-group">
                        <label for="judulAktaSidang">Judul Akta Sidang:</label>
                        <input type="text" id="judulAktaSidang" name="judulAktaSidang" class="form-control">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="simpanRapat">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  document.querySelectorAll(".btn-danger").forEach(function(button) {
    button.addEventListener("click", function(event) {
      event.preventDefault();
      var id = this.getAttribute("data-id");
      var judul = this.getAttribute("data-judul");
      Swal.fire({
        title: "Anda yakin?",
        text: "Akta Sidang '" + judul + "' akan dihapus!",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Ya!",
        cancelButtonText: "Tidak!",
      }).then((response) => {
        if (response.isConfirmed) {
          // Kirim request hapus ke server
          window.location.href = "?page=akta&action=hapus&id=" + id;
        }
      });
    });
  });
});

$(document).ready(function() {
    $('#buatAktaSidang').on('click', function() {
        $('#rapatModal').modal('show');
    });

    $('#selectAll').on('click', function() {
        if ($(this).is(':checked')) {
            $('.rapat-checkbox').prop('checked', true);
        } else {
            $('.rapat-checkbox').prop('checked', false);
        }
    });

    $('#simpanRapat').on('click', function() {
    var rapat = [];
    $(this).prop('disabled', true);
    $('.rapat-checkbox:checked').each(function() {
        rapat.push($(this).val());
    });

    var judulAktaSidang = $('#judulAktaSidang').val();

    if (rapat.length > 0 && judulAktaSidang.trim() !== "") {
        $.ajax({
            type: 'POST',
            url: 'add_akta_sidang.php',
            data: {rapat: rapat, judul: judulAktaSidang},
            dataType: 'json',
            success: function(response) {
                $('#simpanRapat').prop('disabled', false);
                if(response.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat mengirim data.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    } else {
        Swal.fire({
            title: 'Peringatan!',
            text: 'Silakan pilih rapat dan isi judul akta sidang.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
    }
});
});

function loadRapat() {
    $.ajax({
        url: 'get_rapat.php', // Buat file ini untuk mengembalikan daftar rapat
        method: 'GET',
        success: function(data) {
            // Update isi modal dengan data baru
            $('#rapatModal .modal-body').html(data);
        },
        error: function() {
            Swal.fire({
                title: 'Error!',
                text: 'Terjadi kesalahan saat memuat data rapat.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
}

$(document).ready(function() {
    $('#cetakExcel').on('click', function() {
        window.location.href = 'export_akta_excel.php'; // Ganti dengan URL atau script yang sesuai untuk meng-export data ke Excel
    });
});
</script>
</body>
</html>
<?php
$conn->close();
?>