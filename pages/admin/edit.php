<a class="btn btn-primary" href="?page=agenda"><i class="fa fa-arrow-left"></i> Kembali</a>
<div class="row mt-1">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">
<?php
include_once('./config/db.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM agenda WHERE id = $id";
    $result = $conn->query($query);
    $agenda = $result->fetch_assoc();
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $judul_rapat = $_POST['judul_rapat'];
    $tgl_rapat = $_POST['tgl_rapat'];
    $uraian_rapat = $_POST['uraian_rapat'];

    $query = "UPDATE agenda SET judul_rapat = '$judul_rapat', tgl_rapat = '$tgl_rapat', uraian_rapat = '$uraian_rapat' WHERE id = $id";
    $result = $conn->query($query);
    if ($result) {
        ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: 'Agenda berhasil diupdate!',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.value) {
                    window.location.href = 'index.php?page=agenda';
                }
            });
        </script>
        <?php
    } else {
        echo "Gagal mengupdate agenda.";
    }
}
?>

<form method="post">
    <input type="hidden" name="id" value="<?= $agenda['id'] ?>">
    <div class="form-group  col-md-2">
        <label for="tgl_rapat">Tanggal Rapat</label>
        <input type="date" class="form-control" id="tgl_rapat" name="tgl_rapat" value="<?= $agenda['tgl_rapat'] ?>" required>
    </div>
    <div class="form-group  col-md-7">
        <label for="judul_rapat">Judul Rapat</label>
        <input type="text" class="form-control" id="judul_rapat" name="judul_rapat"  value="<?= $agenda['judul_rapat'] ?>" required>
    </div>
    <div class="form-group">
        <label for="uraian_rapat">Uraian Rapat</label>
        <textarea class="form-control" id="uraian_rapat" name="uraian_rapat" required rows="10"><?= $agenda['uraian_rapat'] ?></textarea>
    </div>
    <br>
    <button type="submit" class="btn btn-primary" name="update">Update</button>
</form>