<div class="row mt-3">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">        
            <?php
include_once('./config/db.php');

// Ambil halaman yang diminta dari query string, default adalah 1
$currentPage = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$perPage = 10; // Jumlah data per halaman

// Hitung offset
$offset = ($currentPage - 1) * $perPage;

// Inisialisasi variabel pencarian dan query pencarian
$searchQuery = '';

if (isset($_GET['cari']) || isset($_GET['tgl_rapat'])) {
    $cari = $_GET['cari'] ?? '';
    $tgl_rapat = $_GET['tgl_rapat'] ?? '';

    if (!empty($cari)) {
        $searchQuery .= " WHERE judul_rapat LIKE '%$cari%' OR uraian_rapat LIKE '%$cari%'";
    }

    if (!empty($tgl_rapat)) {
        // Jika $searchQuery sudah ada klausa WHERE, tambahkan AND, jika tidak tambahkan WHERE
        $searchQuery .= (!empty($searchQuery) ? " AND" : " WHERE") . " tgl_rapat = '$tgl_rapat'";
    }
}

// Ambil data dari tabel dengan pencarian
$query = "SELECT * FROM agenda $searchQuery ORDER BY id DESC LIMIT $perPage OFFSET $offset";
$result = $conn->query($query);

// Hitung total data dengan pencarian
$totalQuery = "SELECT COUNT(*) as total FROM agenda $searchQuery";
$totalResult = $conn->query($totalQuery);
$totalData = $totalResult->fetch_assoc()['total'];

// Hitung total halaman
$totalPages = ceil($totalData / $perPage);
?>

                <div class="container">
                <h2>Agenda Rapat</h2>
                <div class="col-12 col-md-6">
                <form method="get" class="form-inline float-right">
    <!-- Tambahkan input hidden untuk tetap berada di halaman yang benar -->
                    <input type="hidden" name="page" value="agenda">
                    <div class="input-group">
                        <input type="text" class="form-control" name="cari" placeholder="Cari Judul / Uraian Rapat" aria-label="Cari berdasarkan judul atau uraian" aria-describedby="btn-cari" value="<?= @$_GET['cari'] ?>">
                        <input type="date" class="form-control" name="tgl_rapat" placeholder="Tanggal Rapat" value="<?php if (!empty($_GET['tgl_rapat'])) echo $_GET['tgl_rapat']; ?>">
                        <button class="btn btn-primary" type="submit" name="btn-cari" id="btn-cari" title="Cari"><i class="fa fa-search"></i></button>
                    </div>
                </form>
                </div>
                <br>
                <form method="GET" action="">
                    <input type="hidden" name="page" value="agenda">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <button class="btn btn-secondary" type="submit">Reset</button>
                </form>
                <br>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">Judul</th>
                            <th class="text-center">Tanggal</th>
                            <th class="text-center">Uraian</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php $no = $offset + 1; ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= $row['judul_rapat'] ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($row['tgl_rapat'])) ?></td>
                                    <td style="text-align: justify"><?= $row['uraian_rapat'] ?></td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="?page=agenda&action=detail&id=<?= $row['id'] ?>" class="btn btn-info btn-sm me-2" title="Detail Agenda"><i class="fa fa-book"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">Tidak ada agenda rapat.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=agenda&halaman=<?= $currentPage - 1; ?>" aria-label="Previous">
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
                            <a class="page-link" href="?page=agenda&halaman=<?= $i; ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=agenda&halaman=<?= $currentPage + 1; ?>" aria-label="Next">
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
        </div>
    <script>
        var deleteButton = document.querySelectorAll('.btn-danger');
        deleteButton.forEach((button) => {
          button.addEventListener("click", function (e) {
            e.preventDefault();
            var id = this.getAttribute("data-id");
            var judul = this.getAttribute("data-judul");
            Swal.fire({
              title: "Anda yakin?",
              html: `Agenda rapat <b>${judul}</b> akan dihapus!`,
              icon: "question",
              confirmButtonText: "Ya!",
              showCancelButton: true,
              cancelButtonText: "Tidak!",
            }).then((response) => {
              if (response.isConfirmed) {
                window.location.href = `?page=agenda&action=hapus&id=${id}`;
              }
            });
          });
        });
    </script>
    </div>
</div>
