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


// Pengambilan data detail notulen dan penyimpanan detail baru
if (isset($_POST['add_detail'])) {
    if (!empty($id_agenda)) {
        $bahasan = $_POST['bahasan'];
        $usulan = $_POST['usulan'];
        $status = $_POST['status'];
        $prioritas = $_POST['prioritas'];

        $stmt_detail = $conn->prepare("INSERT INTO detail_rapat (id_agenda, bahasan, usulan, status, prioritas) VALUES (?, ?, ?, ?, ?)");
        $stmt_detail->bind_param("issss", $id_agenda, $bahasan, $usulan, $status, $prioritas);
        $stmt_detail->execute();
        $stmt_detail->close();

        ?>
        <script src="vendors/sweetalert/sweetalert.min.js"></script>
        <script type="text/javascript">
            Swal.fire({
                title: "Sukses!",
                text: "Detail rapat berhasil ditambahkan.",
                icon: 'success'
            }).then(() => {
                window.location.href = "?page=agenda&action=detail&id=<?php echo $id_agenda; ?>";
            });
        </script>
        <?php
    } else {
        ?>
        <script src="vendors/sweetalert/sweetalert.min.js"></script>
        <script type="text/javascript">
            Swal.fire({
                title: "Error!",
                text: "ID Agenda tidak valid.",
                icon: 'error'
            }).then(() => {
                window.history.back();
            });
        </script>
        <?php
    }
}

if (isset($_POST['edit_detail'])) {
    $id_detail = $_POST['id_detail'];
    $bahasan = $_POST['bahasan'];
    $usulan = $_POST['usulan'];
    $solusi = $_POST['solusi'];

    $stmt = $conn->prepare("UPDATE detail_rapat SET bahasan = ?, usulan = ?, solusi = ? WHERE id = ?");
    $stmt->bind_param("sssi", $bahasan, $usulan, $solusi,  $id_detail);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        ?>
        <script src="vendors/sweetalert/sweetalert.min.js"></script>
        <script type="text/javascript">
            Swal.fire({
                title: "Sukses!",
                text: "Detail rapat berhasil diubah.",
                icon: 'success'
            }).then(() => {
                window.location.href = "?page=agenda&action=detail&id=<?php echo $id_agenda; ?>";
            });
        </script>
        <?php
    } else {
        $errorText = "Gagal mengubah detail rapat";
        ?>
        <script src="vendors/sweetalert/sweetalert.min.js"></script>
        <script type="text/javascript">
            Swal.fire({
                title: "Error!",
                text: "<?php echo $errorText; ?>",
                icon: 'error'
            }).then(() => {
                window.history.back();
            });
        </script>
        <?php
    }
}

// Proses perubahan status dan disposisi
if (isset($_POST['change-status'])) {
    // Update status and disposisi
    $id_detail = $_POST['id_detail'];
    $status = $_POST['status'];
    $disposisi = $_POST['disposisi_roles'];
    $stmt = $conn->prepare("UPDATE detail_rapat SET status = ?, disposisi = ?, status_updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssi", $status, $disposisi, $id_detail);

    if ($stmt->execute()) {
        $stmt->close();
        ?>
        <script src="vendors/sweetalert/sweetalert.min.js"></script>
        <script type="text/javascript">
            Swal.fire({
                title: "Sukses!",
                text: "Status rapat berhasil diubah.",
                icon: 'success'
            }).then(() => {
                window.location.href = "?page=agenda&action=detail&id=<?php echo $id_agenda; ?>";
            });
        </script>
        <?php
    } else {
        $errorText = "Gagal mengubah status rapat";
        ?>
        <script src="vendors/sweetalert/sweetalert.min.js"></script>
        <script type="text/javascript">
            Swal.fire({
                title: "Error!",
                text: "<?php echo $errorText; ?>",
                icon: 'error'
            }).then(() => {
                window.history.back();
            });
        </script>
        <?php
    }
}

// Mengupdate solusi
if (isset($_POST['save-solusi'])) {
    $id_detail = $_POST['id_detail'];
    $solusi = $_POST['solusi'];

    // Ambil disposisi saat ini dari database
    $query = "SELECT disposisi FROM detail_rapat WHERE id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("i", $id_detail);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $current_disposisi = $row['disposisi'];

    // Logika untuk update disposisi
    $new_disposisi = empty($current_disposisi) ? 'admin' : $current_disposisi;

    // Update solusi, disposisi, and status
    $update_query = "UPDATE detail_rapat SET status = 'selesai', solusi = ?, disposisi = ?, status_updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    if ($update_stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $update_stmt->bind_param("ssi", $solusi, $new_disposisi, $id_detail);
    $update_result = $update_stmt->execute();

    if ($update_result) {
        $update_stmt->close();
        ?>
        <script src="vendors/sweetalert/sweetalert.min.js"></script>
        <script type="text/javascript">
            Swal.fire({
                title: "Sukses!",
                text: "Solusi berhasil disimpan dan status rapat diubah menjadi selesai.",
                icon: 'success'
            }).then(() => {
                window.location.href = "?page=agenda&action=detail&id=<?php echo $id_agenda; ?>";
            });
        </script>
        <?php
    } else {
        $errorText = "Gagal menyimpan solusi dan mengubah status rapat";
        ?>
        <script src="vendors/sweetalert/sweetalert.min.js"></script>
        <script type="text/javascript">
            Swal.fire({
                title: "Error!",
                text: "<?php echo $errorText; ?>",
                icon: 'error'
            }).then(() => {
                window.history.back();
            });
        </script>
        <?php
    }
}

// Menghapus detail rapat
if (isset($_POST['delete_detail'])) {
    $id_detail = $_POST['id_detail'];

    // Persiapan query untuk menghapus data
    $stmt = $conn->prepare("DELETE FROM detail_rapat WHERE id = ?");
    $stmt->bind_param("i", $id_detail);

    if ($stmt->execute()) {
        $stmt->close();
        ?>
        <script src="vendors/sweetalert/sweetalert.min.js"></script>
        <script type="text/javascript">
            Swal.fire({
                title: "Sukses!",
                text: "Detail rapat berhasil dihapus.",
                icon: 'success'
            }).then(() => {
                window.location.href = "?page=agenda&action=detail&id=<?php echo $id_agenda; ?>";
            });
        </script>
        <?php
    } else {
        $errorText = "Gagal menghapus detail rapat";
        ?>
        <script src="vendors/sweetalert/sweetalert.min.js"></script>
        <script type="text/javascript">
            Swal.fire({
                title: "Error!",
                text: "<?php echo $errorText; ?>",
                icon: 'error'
            }).then(() => {
                window.history.back();
            });
        </script>
        <?php
    }
    exit;
}

if (isset($_POST['confirm_tarik_detail'])) {
    $selected_details = $_POST['selected_details'] ?? [];

    if (!empty($selected_details)) {
        foreach ($selected_details as $detail_id) {
            // Proses tarik detail di sini, misalnya mengubah status atau melakukan tindakan lain
            $stmt = $conn->prepare("UPDATE detail_rapat SET id_agenda = ? WHERE id = ?");
            $stmt->bind_param("ii", $id_agenda, $detail_id); // Mengikat kedua parameter
            $stmt->execute();
        }
        
        ?>
        <script src="vendors/sweetalert/sweetalert.min.js"></script>
        <script type="text/javascript">
            Swal.fire({
                title: "Sukses!",
                text: "Detail rapat berhasil ditarik.",
                icon: 'success'
            }).then(() => {
                window.location.href = "?page=agenda&action=detail&id=<?php echo $id_agenda; ?>";
            });
        </script>
        <?php
    } else {
        ?>
        <script src="vendors/sweetalert/sweetalert.min.js"></script>
        <script type="text/javascript">
            Swal.fire({
                title: "Error!",
                text: "Tidak ada detail yang dipilih.",
                icon: 'error'
            }).then(() => {
                window.history.back();
            });
        </script>
        <?php
    }
}
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

    <!-- Form Tambah Detail Notulen -->
    <div class="btn-group">
        <button type="button" class="btn btn-primary me-3" data-bs-toggle="modal" data-bs-target="#addDetailModal">Tambah Detail</button>
        <button type="button" class="btn btn-secondary" id="tarikDetailBtn">Tarik Bahasan Yang Belum Selesai</button>    
    </div>
    <br>
<!-- Modal Pop-up untuk tambah detail -->
<div class="modal fade" id="addDetailModal" tabindex="-1" aria-labelledby="addDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="addDetailModalLabel">Tambah Detail Notulen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="bahasan">Bahasan</label>
            <textarea class="form-control" id="bahasan" name="bahasan" required></textarea>
          </div>
          <div class="form-group">
            <label for="usulan">Isi Bahasan</label>
            <textarea class="form-control" id="usulan" name="usulan" required rows="12"></textarea>
          </div>
          <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control" id="status" name="status">
              <option value="belum diproses">Belum Diproses</option>
            </select>
          </div>
          <div class="form-group">
            <label for="status">Prioritas</label>
            <select class="form-control" id="prioritas" name="prioritas">
              <option value="P1">P1</option>
              <option value="P2">P2</option>
              <option value="P3">P3</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name="add_detail" class="btn btn-primary">Tambah Detail</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Pop-up untuk memilih detail yang akan ditarik -->
<div class="modal fade" id="tarikDetailModal" tabindex="-1" aria-labelledby="tarikDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" id="tarikDetailForm">
        <div class="modal-header">
          <h5 class="modal-title" id="tarikDetailModalLabel">Pilih Bahasan untuk Ditarik</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th class="text-center">
                    <input type="checkbox" id="selectAll" />
                    <label for="selectAll">Pilih Semua</label>
                  </th>
                  <th class="text-center">Judul Rapat</th>
                  <th class="text-center">Bahasan</th>
                </tr>
              </thead>
              <tbody id="detailList">
                <!-- Daftar detail akan dimuat di sini oleh JavaScript -->
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name="confirm_tarik_detail" class="btn btn-primary">Tarik Bahasan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<br>
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
                <th class="text-center">Prioritas</th>
                <th class="text-center">Disposisi</th>
                <th class="text-center">Solusi</th>
                <th class="text-center"></th>
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
                $prioritas = $detail['prioritas'];
                $disposisi = $detail['disposisi'];
                $solusi = $detail['solusi'];

                $statusUpdatedAt = !empty($detail['status_updated_at']) ? date('d-m-Y H:i', strtotime($detail['status_updated_at'])) : "Belum diperbarui";


                echo "<tr>
                        <td style='text-align:center'>{$nomor_urut}</td>
                        <td style='text-align:justify'>{$bahasan}</td>
                        <td style='text-align:justify'>{$usulan}</td>
                        <td style='text-align:center' title='Status diubah pada: {$statusUpdatedAt}'>
                            {$status}
                        </td>                   
                        <td style='text-align:center'>{$prioritas}</td>
                        <td style='text-align:center'>{$disposisi}</td>
                        <td style='text-align:justify'>{$solusi}</td>
                        <td style='text-align:center'>
                            <input type='hidden' name='id_detail' value='{$id}'>";

                        // Logika PHP untuk menentukan ikon sesuai status
                        $statusUpdatedAt = !empty($detail['status_updated_at']) ? date('d-m-Y H:i', strtotime($detail['status_updated_at'])) : "Belum diperbarui";
                        if ($status == 'belum diproses') {
                            echo "<button type='button' class='btn btn-primary btn-sm me-2 status-btn' data-status-updated-at='{$statusUpdatedAt}'>
                                    <i class='fas fa-clock'></i>
                                </button>";
                        } elseif ($status == 'disposisi') {
                            echo "<button type='button' class='btn btn-primary btn-sm me-2 status-btn' data-status-updated-at='{$statusUpdatedAt}'>
                                    <i class='fas fa-user'></i>
                                </button>";
                        } elseif ($status == 'selesai') {
                            echo "<button type='button' class='btn btn-primary btn-sm me-2 status-btn' data-status-updated-at='{$statusUpdatedAt}'>
                                    <i class='fas fa-check-circle'></i>
                                </button>";
                        }


                // Lanjutkan echo setelah logika PHP selesai
                echo "</td>
                    <td style='text-align:center'>
                        <div class='btn-group' role='group'>
                            <form method='post'>
                                <input type='hidden' name='id_detail' value='{$id}'>
                                <div class='btn-group'>
                                    <button type='submit' name='status' value='disposisi' class='btn btn-" . ($status == 'disposisi' ? 'primary' : 'secondary') . " btn-sm me-2' title='Disposisi'>
                                        <i class='fas fa-user'></i>
                                    </button>
                                    <button type='submit' name='status' value='selesai' class='btn btn-" . ($status == 'selesai' ? 'primary' : 'secondary') . " btn-sm me-2' title='Selesai'>
                                        <i class='fas fa-check-circle'></i>
                                    </button>
                                </div>
                            </form>
                            <div class='btn-group'>
                                <button type='button' class='btn btn-warning btn-sm me-2 edit-btn' data-id='" . $detail['id'] .  "' data-bs-toggle='modal' data-bs-target='#editModal' '" . $detail['id'] . "' title='Edit'>
                                    <i class='fa fa-pencil-alt'></i>
                                </button>
                                <button type='button' class='btn btn-danger btn-sm me-2' data-id='{$id}' title='Hapus'>
                                    <i class='fa fa-trash'></i>
                                </button>
                                <button type='button' class='btn btn-info btn-sm me-2 view-btn' data-id='" .$detail['id']." ' data-bs-target='#viewDetailModal' data-bs-toggle='modal' title='Lihat'>
                                    <i class='fas fa-eye'></i>
                                </button>
                            </div>
                        </div>
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

<!-- Modal Pop-up untuk edit detail -->
<?php foreach ($detail_rapat as $detail): ?>
    <div class="modal fade" id="editModal<?php echo $detail['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $detail['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post">
                    <input type="hidden" name="id_detail" value="<?php echo $detail['id']; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel<?php echo $detail['id']; ?>">Edit Detail Notulen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="bahasan<?php echo $detail['id']; ?>">Bahasan</label>
                            <textarea class="form-control" id="bahasan<?php echo $detail['id']; ?>" name="bahasan" required rows="3"><?php echo $detail['bahasan']; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="usulan<?php echo $detail['id']; ?>">Isi Bahasan</label>
                            <textarea class="form-control" id="usulan<?php echo $detail['id']; ?>" name="usulan" required rows="10"><?php echo $detail['usulan']; ?></textarea>
                        </div>
                        <?php if (!empty($detail['solusi'])): ?>
                        <div class="form-group">
                            <label for="solusi<?php echo $detail['id']; ?>">Solusi</label>
                            <textarea class="form-control" id="solusi<?php echo $detail['id']; ?>" name="solusi" required rows="10"><?php echo $detail['solusi']; ?></textarea>
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="solusi" value="">
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_detail" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

   <!-- Modal Pop-up untuk memilih role disposisi -->
<div class="modal fade" id="disposisiModal" tabindex="-1" aria-labelledby="disposisiModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" id="disposisiForm">
          <div class="modal-header">
            <h5 class="modal-title" id="disposisiModalLabel">Pilih Role untuk Disposisi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <input type="hidden" name="id_detail" id="disposisi_id_detail">
              <input type="hidden" name="status" value="disposisi">
              <div class="form-group">
                  <label for="disposisi_roles">Disposisi kepada:</label>
                  <select class="form-control" id="disposisi_roles" name="disposisi_roles">
                  <?php
                      $id_role = $_SESSION['id_role']; // assume you have a session variable for the user's role
                      $rolesQuery = "SELECT nama_role FROM roles";
                      $rolesResult = $conn->query($rolesQuery);
                      while ($roles = $rolesResult->fetch_assoc()) {
                          if ($id_role == '1') { 
                              if ($roles['nama_role'] == 'operator 1' || $roles['nama_role'] == 'operator 2' || $roles['nama_role'] == 'operator 3') {
                                  echo "<option value='" . $roles['nama_role'] . "'>" . $roles['nama_role'] . "</option>";
                              }
                          } elseif ($id_role == '2') {
                              if ($roles['nama_role'] == 'admin' || $roles['nama_role'] == 'operator 2' || $roles['nama_role'] == 'operator 3') {
                                  echo "<option value='" . $roles['nama_role'] . "'>" . $roles['nama_role'] . "</option>";
                              }
                          } elseif ($id_role == '3') {
                              if ($roles['nama_role'] == 'admin' || $roles['nama_role'] == 'operator 1' || $roles['nama_role'] == 'operator 3') {
                                  echo "<option value='" . $roles['nama_role'] . "'>" . $roles['nama_role'] . "</option>";
                              }
                          } elseif ($id_role == '4') {
                              if ($roles['nama_role'] == 'admin' || $roles['nama_role'] == 'operator 1' || $roles['nama_role'] == 'operator 2') {
                                echo "<option value='" . $roles['nama_role'] . "'>" . $roles['nama_role'] . "</option>";
                              }
                        }
                      }
                    ?>
                  </select>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="change-status" class="btn btn-primary">Simpan Disposisi</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Pop-up untuk memasukkan solusi -->
<div class="modal fade" id="solusiModal" tabindex="-1" aria-labelledby="solusiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" id="solusiForm">
          <div class="modal-header">
            <h5 class="modal-title" id="solusiModalLabel">Masukkan Solusi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <input type="hidden" name="id_detail" id="solusi_id_detail">
              <input type="hidden" name="disposisi" id="disposisi" value="admin">
              <input type="hidden" name="status" value="selesai">
              <div class="form-group">
                  <label for="solusi">Solusi:</label>
                  <textarea class="form-control" id="solusi" name="solusi" required rows="10"></textarea>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="save-solusi" class="btn btn-primary">Simpan Solusi</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Add the modal popup for displaying agenda and meeting details -->
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
  document.querySelectorAll(".btn-danger").forEach(function(button) {
    button.addEventListener("click", function(event) {
      event.preventDefault();
      var id = this.getAttribute("data-id");
      Swal.fire({
        title: "Anda yakin?",
        text: "Detail rapat ini akan dihapus!",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Ya!",
        cancelButtonText: "Tidak!",
      }).then((response) => {
        if (response.isConfirmed) {
          // Buat form untuk mengirimkan request hapus
          var form = document.createElement("form");
          form.action = "?page=agenda&action=detail&id=<?php echo $id_agenda; ?>";
          form.method = "post";
          var inputId = document.createElement("input");
          inputId.type = "hidden";
          inputId.name = "id_detail";
          inputId.value = id;
          form.appendChild(inputId);
          var inputDelete = document.createElement("input");
          inputDelete.type = "hidden";
          inputDelete.name = "delete_detail";
          inputDelete.value = "true";
          form.appendChild(inputDelete);
          document.body.appendChild(form);
          form.submit();
        }
      });
    });
  });
});

document.addEventListener("DOMContentLoaded", function() {
        // Event listener untuk tombol Disposisi
        document.querySelectorAll("button[name='status'][value='disposisi']").forEach(function(button) {
            button.addEventListener("click", function(event) {
                event.preventDefault();
                let id_detail = this.closest("form").querySelector("input[name='id_detail']").value;
                document.getElementById("disposisi_id_detail").value = id_detail;
                document.getElementById("disposisi").value = disposisi; // Set the original disposisi value
                new bootstrap.Modal(document.getElementById("disposisiModal")).show();
            });
        });

        // Event listener untuk tombol Solusi
        document.querySelectorAll("button[name='status'][value='selesai']").forEach(function(button) {
            button.addEventListener("click", function(event) {
                event.preventDefault();
                let id_detail = this.closest("form").querySelector("input[name='id_detail']").value;
                document.getElementById("solusi_id_detail").value = id_detail;
                new bootstrap.Modal(document.getElementById("solusiModal")).show();
            });
        });
    });

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".edit-btn").forEach(function(button) {
        button.addEventListener("click", function(event) {
            let idDetail = this.getAttribute("data-id");
            let modal = new bootstrap.Modal(document.getElementById("editModal" + idDetail));
            modal.show();
        });
    });
});
</script>
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

document.addEventListener("DOMContentLoaded", function() {
    const tarikDetailBtn = document.getElementById("tarikDetailBtn");
    
    if (tarikDetailBtn) {
        tarikDetailBtn.addEventListener("click", function() {
            console.log("Tombol Tarik Detail diklik"); // Log untuk memastikan event terdeteksi
            
            // Fetch the details with specific statuses
            fetch('fetch_details.php?id_agenda=<?php echo $id_agenda; ?>')
                .then(response => response.json())
                .then(data => {
                    console.log('Data fetched:', data); // Debugging: Print the fetched data
                    const detailList = document.getElementById('detailList');
                    if (!detailList) {
                        console.error("Element dengan id 'detailList' tidak ditemukan");
                        return;
                    }
                    detailList.innerHTML = ''; // Clear previous content

                    if (data.length > 0) {
                        data.forEach(detail => {
                            const row = document.createElement('tr');
                            
                            const checkboxCell = document.createElement('td');
                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.name = 'selected_details[]';
                            checkbox.value = detail.id;
                            checkbox.classList.add('detail-checkbox');
                            checkboxCell.appendChild(checkbox);

                            const judulRapatCell = document.createElement('td');
                            judulRapatCell.textContent = detail.judul_rapat;
 
                            const bahasanCell = document.createElement('td');
                            bahasanCell.textContent = detail.bahasan;
                            
                            row.appendChild(checkboxCell);
                            row.appendChild(judulRapatCell);
                            row.appendChild(bahasanCell);

                            detailList.appendChild(row);
                        });
                    } else {
                        const row = document.createElement('tr');
                        const cell = document.createElement('td');
                        cell.colSpan = 3;
                        cell.textContent = 'Tidak ada detail yang bisa dipilih.';
                        row.appendChild(cell);
                        detailList.appendChild(row);
                    }

                    // Buka modal setelah data dimuat
                    const modal = new bootstrap.Modal(document.getElementById('tarikDetailModal'));
                    modal.show();

                    // Add event listener for the "Pilih Semua" checkbox
                    const selectAllCheckbox = document.getElementById('selectAll');
                    if (selectAllCheckbox) {
                        selectAllCheckbox.addEventListener('click', function() {
                            const checkboxes = document.querySelectorAll('.detail-checkbox');
                            checkboxes.forEach(checkbox => {
                                checkbox.checked = this.checked;
                            });
                        });
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        });
    } else {
        console.error("Tombol dengan id 'tarikDetailBtn' tidak ditemukan");
    }
});

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".status-btn").forEach(function(button) {
        button.addEventListener("click", function(event) {
            var statusUpdatedAt = this.getAttribute("data-status-updated-at");
            Swal.fire({
                title: "Tanggal Perubahan Status",
                text: "Status diubah pada: " + statusUpdatedAt,
                icon: "info"
            });
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
  #tarikDetailModal .table {
    margin-bottom: 0;
  }
  #tarikDetailModal .table th,
  #tarikDetailModal .table td {
    vertical-align: middle;
    border: 1px solid #dee2e6;
  }
  #tarikDetailModal .table th:first-child,
  #tarikDetailModal .table td:first-child {
    width: 50px;
    text-align: center;
  }
  #tarikDetailModal .table th:nth-child(2),
  #tarikDetailModal .table td:nth-child(2) {
    width: 40%;
  }
  #tarikDetailModal .table th:last-child,
  #tarikDetailModal .table td:last-child {
    width: 60%;
  }
</style>
</body>
</html>
