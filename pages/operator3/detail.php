<a class="btn btn-primary" href="?page=agenda"><i class="fa fa-arrow-left"></i> Kembali</a>
<div class="row mt-1">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">  

<?php
include_once('./config/db.php');

require_once './role_functions.php';

// Gunakan fungsi dan konstanta seperti biasa
$id_role = $_SESSION['id_role'];
$user_role = getRoleName($id_role);

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

function canChangeStatusAndDisposisi($id_role, $current_disposisi) {
    return $id_role == ROLE_ADMIN || 
           ($id_role == ROLE_OPERATOR_1 && $current_disposisi == 'operator 1') ||
           ($id_role == ROLE_OPERATOR_2 && $current_disposisi == 'operator 2') ||
           ($id_role == ROLE_OPERATOR_3 && $current_disposisi == 'operator 3');
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Rapat</title>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script></head>
<body>
<div class="container">
<h2>Detail Rapat: <?php echo $judul_rapat; ?></h2>

<div class="btn-group">
        <button type="button" class="btn btn-primary me-3" data-bs-toggle="modal" data-bs-target="#addDetailModal">Tambah Detail</button>
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

                $canChangeStatusAndDisposisi = canChangeStatusAndDisposisi($id_role, $disposisi);
                $canProvideSolution = false;

                if ($id_role == ROLE_ADMIN || 
                    ($id_role == ROLE_OPERATOR_1 && $disposisi == 'operator 1') ||
                    ($id_role == ROLE_OPERATOR_2 && $disposisi == 'operator 2') ||
                    ($id_role == ROLE_OPERATOR_3 && $disposisi == 'operator 3')) {
                    $canProvideSolution = true;
                    
                }

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
                            <div class='btn-group' role='group'>";

                    // Tampilkan tombol disposisi dan selesai jika $canChangeStatusAndDisposisi bernilai true
                    if ($canChangeStatusAndDisposisi) {
                        echo "<form method='post'>
                                    <input type='hidden' name='id_detail' value='{$id}'>
                                    <div class='btn-group'>
                                        <button type='submit' name='status' value='disposisi' class='btn btn-" . ($status == 'disposisi' ? 'primary' : 'secondary') . " btn-sm me-2' title='Disposisi'>
                                            <i class='fas fa-user'></i>
                                        </button>
                                        <button type='submit' name='status' value='selesai' class='btn btn-" . ($status == 'selesai' ? 'primary' : 'secondary') . " btn-sm me-2' title='Selesai'>
                                            <i class='fas fa-check-circle'></i>
                                        </button>
                                    </div>
                                </form>";
                    }

                    // Tampilkan tombol edit dan hapus jika $canProvideSolution bernilai true
                    if ($canProvideSolution) {
                        echo "<form method='post'>
                                    <input type='hidden' name='id_detail' value='{$id}'>
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
                                </form>";
                    }

                    echo "    </div>
                        </td>
                    </tr>";
            }
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
    const ROLE_ADMIN = <?php echo ROLE_ADMIN; ?>;
    const ROLE_OPERATOR_1 = <?php echo ROLE_OPERATOR_1; ?>;
    const ROLE_OPERATOR_2 = <?php echo ROLE_OPERATOR_2; ?>;
    const ROLE_OPERATOR_3 = <?php echo ROLE_OPERATOR_3; ?>;
    const userRole = <?php echo $id_role; ?>;

    // Event listener untuk tombol Disposisi
    document.querySelectorAll("button[name='status'][value='disposisi']").forEach(function(button) {
        button.addEventListener("click", function(event) {
            event.preventDefault();
            let id_detail = this.closest("form").querySelector("input[name='id_detail']").value;
            let disposisi = this.closest("tr").querySelector("td:nth-child(5)").textContent;
            
            if (userRole === ROLE_ADMIN || 
                (userRole === ROLE_OPERATOR_1 && disposisi === 'operator 1') ||
                (userRole === ROLE_OPERATOR_2 && disposisi === 'operator 2') ||
                (userRole === ROLE_OPERATOR_3 && disposisi === 'operator 3')) {
                document.getElementById("disposisi_id_detail").value = id_detail;
                document.getElementById("disposisi").value = disposisi;
                new bootstrap.Modal(document.getElementById("disposisiModal")).show();
            } else {
                alert("Anda tidak memiliki izin untuk mengubah disposisi item ini.");
            }
        });
    });

    // Event listener untuk tombol Solusi
    document.querySelectorAll("button[name='status'][value='selesai']").forEach(function(button) {
        button.addEventListener("click", function(event) {
            event.preventDefault();
            let id_detail = this.closest("form").querySelector("input[name='id_detail']").value;
            let disposisi = this.closest("tr").querySelector("td:nth-child(5)").textContent;
            
            if (userRole === ROLE_ADMIN || 
                (userRole === ROLE_OPERATOR_1 && disposisi === 'operator 1') ||
                (userRole === ROLE_OPERATOR_2 && disposisi === 'operator 2') ||
                (userRole === ROLE_OPERATOR_3 && disposisi === 'operator 3')) {
                document.getElementById("solusi_id_detail").value = id_detail;
                new bootstrap.Modal(document.getElementById("solusiModal")).show();
            } else {
                alert("Anda tidak memiliki izin untuk memberikan solusi untuk item ini.");
            }
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
</body>
</html>
