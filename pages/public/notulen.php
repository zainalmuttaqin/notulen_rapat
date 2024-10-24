<div class="row mt-3">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">
            <?php
// Koneksi database
include_once('./config/db.php');

// Ambil halaman yang diminta dari query string, default adalah 1
$currentPage = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$perPage = 10; // Jumlah data per halaman

// Hitung offset
$offset = ($currentPage - 1) * $perPage;

// Inisialisasi variabel pencarian dan query pencarian
$searchQuery = '';

if (isset($_GET['cari']) || isset($_GET['tgl_rapat']) || isset($_GET['status'])) {
    $cari = $_GET['cari'] ?? '';
    $tgl_rapat = $_GET['tgl_rapat'] ?? '';
    $status = $_GET['status'] ?? '';

    if (!empty($cari)) {
        $searchQuery .= " WHERE a.judul_rapat LIKE '%$cari%' OR d.bahasan LIKE '%$cari%' OR d.usulan LIKE '%$cari%'";
    }

    if (!empty($tgl_rapat)) {
        $searchQuery .= (!empty($searchQuery) ? " AND" : " WHERE") . " a.tgl_rapat = '$tgl_rapat'";
    }

    if (!empty($status)) {
        $searchQuery .= (!empty($searchQuery) ? " AND" : " WHERE") . " d.status = '$status'";
    }
}

// Ambil data dari tabel dengan pencarian
$query = "SELECT d.*, a.judul_rapat, a.tgl_rapat FROM detail_rapat AS d 
          JOIN agenda AS a ON d.id_agenda = a.id 
          $searchQuery ORDER BY d.id DESC LIMIT $perPage OFFSET $offset";
$result = $conn->query($query);

// Hitung total data dengan pencarian
$totalQuery = "SELECT COUNT(*) as total FROM detail_rapat AS d 
               JOIN agenda AS a ON d.id_agenda = a.id $searchQuery";
$totalResult = $conn->query($totalQuery);
$totalData = $totalResult->fetch_assoc()['total'];

// Hitung total halaman
$totalPages = ceil($totalData / $perPage);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notulen Rapat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <h2>Notulen Rapat</h2>
    <div class="col-12 col-md-6">
    <form method="get" class="form-inline float-right">
        <!-- Tambahkan input hidden untuk tetap berada di halaman yang benar -->
        <input type="hidden" name="page" value="notulen">
        <div class="input-group">
            <input type="text" class="form-control" name="cari" placeholder="Cari Judul / Bahasan / Usulan" value="<?= @$_GET['cari'] ?>">
            <input type="date" class="form-control" name="tgl_rapat" placeholder="Tanggal Rapat" value="<?= @$_GET['tgl_rapat'] ?>">
            <select class="form-control" name="status">
                <option value="">Pilih Status</option>
                <option value="belum diproses" <?= (@$_GET['status'] == 'belum diproses') ? 'selected' : ''; ?>>Belum Diproses</option>
                <option value="disposisi" <?= (@$_GET['status'] == 'disposisi') ? 'selected' : ''; ?>>Disposisi</option>
                <option value="selesai" <?= (@$_GET['status'] == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
            </select>
            <button class="btn btn-primary" type="submit" name="btn-cari" title="Cari"><i class="fa fa-search"></i></button>
        </div>
    </form>
</div>
<br>
<form method="GET" action="">
    <input type="hidden" name="page" value="notulen">
    <button class="btn btn-secondary" type="submit">Reset</button>
</form>
<br>
    <div class="table-responsive">
        <table class="table table-striped table-bordered text-justify" id="data-table">
            <thead>
            <tr>
                <th>No</th>
                <th>Judul Rapat</th>
                <th>Tanggal Rapat</th>
                <th>Bahasan</th>
                <th>Isi Bahasan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
                <?php
                $no = $offset + 1;
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <tr>
                        <td><?= $no ?></td>
                        <td><?= htmlspecialchars($row['judul_rapat']) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tgl_rapat'])) ?></td>
                        <td style="text-align: justify"><?= htmlspecialchars($row['bahasan']) ?></td>
                        <td style="text-align: justify"><?= htmlspecialchars($row['usulan']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#modal-<?= $row['id'] ?>">
                                <i class="fas fa-eye" title="Lihat"></i>
                            </button>
                        </td>
                    </tr>
                    <?php
                    $no++;
                }
                ?>
            </tbody>
        </table>
    </div>

    <nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <?php if ($currentPage > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=notulen&halaman=<?= $currentPage - 1; ?>&cari=<?= @$_GET['cari'] ?>&tgl_rapat=<?= @$_GET['tgl_rapat'] ?>&status=<?= @$_GET['status'] ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link">&laquo;</span>
            </li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= ($i == $currentPage) ? 'active' : ''; ?>">
                <a class="page-link" href="?page=notulen&halaman=<?= $i; ?>&cari=<?= @$_GET['cari'] ?>&tgl_rapat=<?= @$_GET['tgl_rapat'] ?>&status=<?= @$_GET['status'] ?>"><?= $i; ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=notulen&halaman=<?= $currentPage + 1; ?>&cari=<?= @$_GET['cari'] ?>&tgl_rapat=<?= @$_GET['tgl_rapat'] ?>&status=<?= @$_GET['status'] ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link">&raquo;</span>
            </li>
        <?php endif; ?>
    </ul>
</nav>


<!-- Modal untuk melihat disposisi dan solusi -->
<?php
mysqli_data_seek($result, 0); // Reset pointer hasil query
while ($row = mysqli_fetch_assoc($result)) {
    ?>
    <div class="modal fade" id="modal-<?= $row['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document"> <!-- Add modal-lg class to make the modal larger -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Disposisi dan Solusi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Disposisi: <?= htmlspecialchars($row['disposisi']) ?: 'Belum ada disposisi' ?></p>
                    <p style="text-align: justify">Solusi: <?= htmlspecialchars($row['solusi']) ?: 'Belum ada solusi' ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
</div>
<script>
    $(document).ready(function() {
        // Fungsi untuk mereset pencarian
        $('#reset-button').on('click', function() {
            window.location.href = '?page=notulen'; // Mengarahkan ke halaman tanpa parameter pencarian
        });
    });
</script>
</body>
</html>
