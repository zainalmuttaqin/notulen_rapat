<?php
session_start();
include('./config/db.php');

// Ambil data role dari database
$roles_query = "SELECT id, role FROM roles";
$roles_result = mysqli_query($conn, $roles_query);

// Fungsi untuk hashing password
function hash_password($password) {
    return md5($password);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role_id = mysqli_real_escape_string($conn, $_POST['id_role']);

    // Validasi jika username sudah ada
    $check_query = "SELECT * FROM users WHERE username = '$username'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['error'] = "Username sudah digunakan.";
        header("Location: register.php");
        exit();
    } else {
        // Hash password sebelum menyimpan ke database
        $hashed_password = hash_password($password);

        // Insert ke tabel user
        $insert_query = "INSERT INTO users (username, pass, id_role) VALUES ('$username', '$hashed_password', '$role_id')";

        if (mysqli_query($conn, $insert_query)) {
            $_SESSION['register_success'] = true;
            $_SESSION['username'] = $username;  // Simpan nama pengguna untuk pesan notifikasi
            header("Location: register.php");
            exit();
        } else {
            $_SESSION['error'] = "Terjadi kesalahan saat registrasi.";
            header("Location: register.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="./vendors/bootstrap-5.0.0-beta3-dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Style CSS -->
    <link rel="stylesheet" href="./assets/css/style.css">

    <!-- jQuery 3.6.0 -->
    <script defer src="./vendors/jQuery-3.6.0/jQuery.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script defer src="./vendors/bootstrap-5.0.0-beta3-dist/js/bootstrap.bundle.min.js"></script>
    <!-- FontAwesome -->
    <script defer src="./vendors/fontawesome-free-5.15.3-web/js/all.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.all.min.js"></script>

    <style>
      html, body {
        height: 100%;
      }
      .container-fluid, .row, .full-height {
        height: 100%;
      }
    </style>

    <title>Registrasi</title>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-0 col-md-4 d-none d-md-block bg-primary full-height position-relative">
            <img class='w-75 exact-center' src="./assets/img/login.svg"/>
        </div>
        <div class="col-sm-12 col-md-8 full-height">
            <div class="p-5">
                <h3 class="fw-bold mb-4">Registrasi</h3>
                <?php
                if (isset($message)) {
                    echo "<div class='alert alert-info'>$message</div>";
                }
                ?>
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nama Pengguna</label>
                        <input type="text" name="username" class="form-control" placeholder="Nama Pengguna" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <input type="password" name="password" class="form-control" placeholder="Kata Sandi" required>
                    </div>
                    <div class="mb-3">
                        <label for="id_role" class="form-label">Nama Role</label>
                        <select class="form-control" id="id_role" name="id_role">
                            <option value="1">Admin</option>
                            <option value="2">Operator 1</option>
                            <option value="3">Operator 2</option>
                            <option value="4">Operator 3</option>
                            <option value="5">Public</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Daftar</button>
                    <div class="mb-3 text-center">
                    Sudah punya akun? <a href="login.php" class='fs-6 link-primary'>Masuk</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
// Menampilkan notifikasi jika registrasi berhasil
<?php if (isset($_SESSION['register_success']) && $_SESSION['register_success'] === true): ?>
    Swal.fire({
        title: 'Registrasi Berhasil!',
        text: 'Selamat datang, <?php echo $_SESSION['username']; ?>! Silakan login untuk melanjutkan.',
        icon: 'success',
        timer: 1000,
        showConfirmButton: false
    }).then(() => {
        window.location.href = 'login.php'; // Mengarahkan ke halaman login
    });
    <?php unset($_SESSION['register_success']); ?> // Menghapus variabel session
<?php endif; ?>
</script>
</body>
</html>
