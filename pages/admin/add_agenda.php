<a class="btn btn-primary" href="?page=agenda"><i class="fa fa-arrow-left"></i> Kembali</a>
<div class="row mt-1">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">        
    <?php
    include_once('./config/db.php');
    if (isset($_POST['add_agenda'])) {
    $tgl_rapat = $_POST['tgl_rapat'];
    $judul_rapat = $_POST['judul_rapat'];
    $uraian_rapat = $_POST['uraian_rapat'];

    // Create a prepared statement
    $stmt = $conn->prepare("INSERT INTO agenda (tgl_rapat, judul_rapat, uraian_rapat) VALUES (?, ?, ?)");

    // Bind the parameters
    $stmt->bind_param("sss", $tgl_rapat, $judul_rapat, $uraian_rapat);

    // Execute the query
    $stmt->execute();

    // Check if the query was successful
    if ($stmt->affected_rows > 0) {
        ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <script>
            Swal.fire({
                title: 'Data berhasil ditambahkan!',
                text: 'Agenda rapat berhasil ditambahkan.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.value) {
                    window.location.href = "?page=agenda";
                }
            });
        </script>
        <?php
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}
?>
                <div class="container">
                    <h2>Tambah Agenda Rapat</h2>
                    <form id="form-agenda" method="post">
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for="tgl_rapat">Tanggal Rapat</label>
                                <input type="date" class="form-control" id="tgl_rapat" name="tgl_rapat" required>
                            </div>
                            <div class="form-group col-md-8">
                                <label for="judul_rapat">Judul Rapat</label>
                                <input type="text" class="form-control" id="judul_rapat" name="judul_rapat" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="uraian_rapat">Uraian Rapat</label>
                            <textarea class="form-control" id="uraian_rapat" name="uraian_rapat" required rows="8"></textarea>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-primary btn-save" name="add_agenda">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>