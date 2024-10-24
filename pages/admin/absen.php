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

if (isset($_POST['tambah'])) {
    if (!empty($id_agenda)) {
        $nama = mysqli_real_escape_string($conn, $_POST['nama']);

        $stmt = $conn->prepare("INSERT INTO absen (id_agenda, nama) VALUES (?, ?)");
        $stmt->bind_param("is", $id_agenda, $nama);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            ?>
            <script src="vendors/sweetalert/sweetalert.min.js"></script>
            <script type="text/javascript">
                Swal.fire({
                    title: "Sukses!",
                    text: "Absensi berhasil ditambahkan.",
                    icon: 'success'
                }).then(() => {
                    window.location.href = "?page=agenda&action=absen&id=<?php echo $id_agenda; ?>";
                });
            </script>
            <?php
        } else {
            $errorText = "Gagal menambahkan absensi.";
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
}

if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama_edit = $_POST['nama_edit'];

    $stmt = $conn->prepare("UPDATE absen SET nama = ? WHERE id = ?");
    $stmt->bind_param("si", $nama_edit, $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        ?>
        <script src="vendors/sweetalert/sweetalert.min.js"></script>
        <script type="text/javascript">
            Swal.fire({
                title: "Sukses!",
                text: "Absensi berhasil diupdate.",
                icon: 'success'
            }).then(() => {
                window.location.href = "?page=agenda&action=absen&id=<?php echo $id_agenda; ?>";
            });
        </script>
        <?php
    } else {
        $errorText = "Gagal mengupdate absensi.";
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

if (isset($_POST['hapus'])) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM absen WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        ?>
        <script src="vendors/sweetalert/sweetalert.min.js"></script>
        <script type="text/javascript">
            Swal.fire({
                title: "Sukses!",
                text: "Absensi berhasil dihapus.",
                icon: 'success'
            }).then(() => {
                window.location.href = "?page=agenda&action=absen&id=<?php echo $id_agenda; ?>";
            });
        </script>
        <?php
    } else {
        $errorText = "Gagal menghapus absensi.";
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

// Fungsi tampilkan data absensi
$sql = "SELECT * FROM absen WHERE id_agenda = '$id_agenda'";
$result = mysqli_query($conn, $sql);
?>

<!-- HTML Code -->
<a class="btn btn-primary" href="?page=agenda"><i class="fa fa-arrow-left"></i> Kembali</a>
<div class="row mt-1">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">
                <h4>Tambah Absensi</h4>
                <form action="" method="post">
                    <div class="form-group">
                        <label for="nama">Nama</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary" name="tambah">Tambah</button>
                </form>
                <hr>
                <h4>Data Absensi</h4>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">Nama</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td class="text-center"> <?php echo $no++; ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>"><i class="fa fa-pencil-alt" title="Edit"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="hapusData(<?php echo $row['id']; ?>)"><i class="fa fa-trash" title="Hapus"></i></button>
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel">Edit Absensi</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="post" action="">
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="nama_edit">Nama</label>
                                                <input type="text" class="form-control" id="nama_edit" name="nama_edit" value="<?php echo htmlspecialchars($row['nama']); ?>" required>
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary" name="edit">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Script untuk Hapus Data -->
<script type="text/javascript">
function hapusData(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data yang dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'post';
            form.action = '';

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'id';
            input.value = id;

            var deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'hapus';
            deleteInput.value = true;

            form.appendChild(input);
            form.appendChild(deleteInput);
            document.body.appendChild(form);
            form.submit();
        }
    })
}
</script>
