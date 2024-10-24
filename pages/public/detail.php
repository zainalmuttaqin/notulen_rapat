<a class="btn btn-primary" href="?page=agenda"><i class="fa fa-arrow-left"></i> Kembali</a>
<div class="row mt-1">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">  

<?php
include_once('./config/db.php');

// Mengambil id_agenda dari URL atau form
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_agenda = $_GET['id'];
} elseif (isset($_POST['id_agenda']) && is_numeric($_POST['id_agenda'])) {
    $id_agenda = $_POST['id_agenda'];
} else {
    echo "<p>ID Agenda tidak valid.</p>";
    exit;
}

// Query untuk mengambil judul rapat
$query = "SELECT judul_rapat FROM agenda WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_agenda);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$judul_rapat = $row['judul_rapat'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Rapat</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>

<body>
<div class="container">
<h2>Detail Rapat: <?php echo $judul_rapat; ?></h2>
    <!-- Tabel Detail Rapat -->
    <form method="GET" action="" class="d-flex">
    <input type="hidden" name="page" value="agenda">
    <input type="hidden" name="action" value="detail">
    <input type="hidden" name="id" value="<?php echo $id_agenda; ?>">
    <div class="input-group mb-3">
        <input type="text" name="search" class="form-control" placeholder="Cari bahasan, isi bahasan" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" style="max-width: 300px;">
        <select name="status" class="form-control"  style="max-width: 300px;">
            <option value="">Pilih Status</option>
            <option value="belum diproses">Belum Diproses</option>
            <option value="disposisi">Disposisi</option>
            <option value="selesai">Selesai</option>
        </select>
        <button class="btn btn-primary" type="submit"><i class="fa fa-search" title="Cari"></i></button>
        <button class="btn btn-secondary ms-2" title="Reset" type="button" onclick="window.location.href='?page=agenda&action=detail&id=<?php echo $id_agenda; ?>'">Reset</button>
    </div>
    </form>
    <br>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th class="text-center">Bahasan</th>
                <th class="text-center">Isi Bahasan</th>
                <th class="text-center">Status</th>
                <th class="text-center">Disposisi</th>
                <th class="text-center">Solusi</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Ambil parameter pencarian dari URL
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Definisikan query pencarian
$searchQuery = "";
if ($search) {
    $searchQuery = " AND (bahasan LIKE ? OR usulan LIKE ? OR status LIKE ?)";
}
if ($status) {
    $searchQuery .= " AND status = ?";
}

// Tentukan batasan dan offset untuk paging
$items_per_page = 5; // Jumlah item per halaman
$page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$offset = ($page - 1) * $items_per_page;

$start_number = $offset + 1;

// Query untuk menghitung total jumlah data
$totalQuery = "SELECT COUNT(*) as total FROM detail_rapat WHERE id_agenda = ?" . $searchQuery;
$totalStmt = $conn->prepare($totalQuery);
if ($search) {
    $totalStmt->bind_param("isss", $id_agenda, $search, $search, $search);
} elseif ($status) {
    $totalStmt->bind_param("is", $id_agenda, $status);
} else {
    $totalStmt->bind_param("i", $id_agenda);
}
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalRow = $totalResult->fetch_assoc();
$total_items = $totalRow['total'];
$total_pages = ceil($total_items / $items_per_page);

// Query untuk mengambil data dengan paging
$query = "SELECT * FROM detail_rapat WHERE id_agenda = ?" . $searchQuery . " ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
if ($search) {
    $likeSearch = "%" . $search . "%";
    $stmt->bind_param("isssii", $id_agenda, $likeSearch, $likeSearch, $likeSearch, $items_per_page, $offset);
} elseif ($status) {
    $stmt->bind_param("isii", $id_agenda, $status, $items_per_page, $offset);
} else {
    $stmt->bind_param("iii", $id_agenda, $items_per_page, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
        
        $detail_rapat = array();

        if ($result->num_rows > 0) {
            $detail_rapat = $result->fetch_all(MYSQLI_ASSOC);
            foreach ($detail_rapat as $index => $detail) {
                $nomor_urut = $start_number + $index ;
                $id = $detail['id'];
                $bahasan = $detail['bahasan'];
                $usulan = $detail['usulan'];
                $status = $detail['status'];
                $disposisi = $detail['disposisi'];
                $solusi = $detail['solusi'];

                echo "<tr>
                        <td style='text-align:center'>{$nomor_urut}</td>
                        <td style='text-align:justify'>{$bahasan}</td>
                        <td style='text-align:justify'>{$usulan}</td>
                        <td style='text-align:center'>{$status}</td>
                        <td style='text-align:center'>{$disposisi}</td>
                        <td style='text-align:justify'>{$solusi}</td>
                        <td style='text-align:center'>
                            <button type='button' class='btn btn-info btn-sm me-2 view-btn' data-id='" .$detail['id']." ' data-bs-target='#viewDetailModal' data-bs-toggle='modal' title='Lihat'>
                                <i class='fas fa-eye'></i>
                            </button>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>Tidak ada detail rapat yang ditemukan.</td></tr>";
        }
        ?>
        </tbody>
    </table>

<div class="pagination justify-content-center">
    <ul class="pagination">
        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=agenda&action=detail&id=<?php echo $id_agenda; ?>&search=<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>&page_num=<?php echo $page - 1; ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link">&laquo;</span>
            </li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=agenda&action=detail&id=<?php echo $id_agenda; ?>&search=<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>&page_num=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=agenda&action=detail&id=<?php echo $id_agenda; ?>&search=<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>&page_num=<?php echo $page + 1; ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
            <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link">&raquo;</span>
            </li>
        <?php endif; ?>
    </ul>
</div>

<div class="modal fade" id="viewDetailModal" tabindex="-1" aria-labelledby="viewDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDetailModalLabel">Detail Rapat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- The detail data will be inserted here -->
                <div id="detail-data"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Event listener for View button
    document.querySelectorAll(".view-btn").forEach(function(button) {
        button.addEventListener("click", function(event) {
            event.preventDefault(); // Prevent the default action

            // Get the ID from the clicked button
            let id_agenda = this.getAttribute("data-id");

            // Fetch the data from the server
            fetch('pages/admin/view-detail.php?id=' + id_agenda)
             .then(response => response.text())
            .then(data => {
                // Insert the data into the modal body
                document.getElementById('detail-data').innerHTML = data;
                // Show the modal
                $('#viewDetailModal').modal('show');
            })
            .catch(error => {
                console.error('Error fetching detail data:', error);
            });
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
