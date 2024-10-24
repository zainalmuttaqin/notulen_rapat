<?php
session_start();
include('config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    // Fetch user data including role
    $sql = "SELECT * FROM users WHERE username='$username' AND pass='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['username'];
        $_SESSION['id_role'] = $user['id_role'];
        $_SESSION['login_success'] = true;
        // Redirect handled by JavaScript
    } else {
        $error = "Username atau password salah!";
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
    <!-- Script JS -->
    <script defer src="./assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.all.min.js"></script>
    <style>
      html, body {
        height: 100%;
      }
      .container-fluid, .row, .full-height {
        height: 100%;
      }
    </style>

    <title>Login</title>
  </head>
<body>
<div class="container-fluid">
      <div class="row">
        <div class="col-sm-0 col-md-4 d-none d-md-block bg-primary full-height position-relative">
        <img class='w-75 exact-center' src="./assets/img/login.svg"/>
        </div>
        <div class="col-sm-12 col-md-8 full-height">
          <div class="p-5">
            <h3 class="fw-bold mb-4">Login </h3>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <br>
            <button type="submit" class="btn btn-primary">Login</button>
            <div class="mb-3 text-center">
              Belum punya akun? <a href="register.php" class='fs-6 link-primary'>Daftar</a>
            </div>
        </form>
        </div>
    </div>
    <script>
    <?php if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true): ?>
        Swal.fire({
            title: 'Login Berhasil!',
            text: 'Selamat datang, <?php echo $_SESSION['username']; ?>!',
            icon: 'success',
            timer: 1000,
            showConfirmButton: false
        }).then(function() {
            window.location.href = "index.php"; // Redirect after SweetAlert
        });
        <?php unset($_SESSION['login_success']); ?> // Remove session variable after use
    <?php endif; ?>
    </script>
</body>
</html>
