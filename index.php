<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("location: login.php");
  exit();
}

include_once('./config/db.php');

require_once './role_functions.php';

// Gunakan fungsi dan konstanta seperti biasa
$id_role = $_SESSION['id_role'];
$user_role = getRoleName($id_role);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="./vendors/bootstrap-5.0.0-beta3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/style.css">

    <script defer src="./vendors/jQuery-3.6.0/jQuery.min.js"></script>
    <script defer src="./vendors/bootstrap-5.0.0-beta3-dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="./vendors/fontawesome-free-5.15.3-web/js/all.min.js"></script>
    <script defer src="./assets/js/script.js"></script>

    <?php
      $page = @$_GET['page'];
      $action = @$_GET['action'];
      $file = '';
      $title = '';
      $script = '';

      if (isset($page)) {
        switch ($id_role) {
            case ROLE_ADMIN:
                switch ($page) {
                    case 'agenda':
                        $script = '<script src="vendors/sweetalert/sweetalert.min.js"></script>';
                        switch ($action) {
                            case 'tambah':
                                $file = 'admin/add_agenda.php';
                                $title = 'Tambah Data Agenda - ';
                                break;
                            case 'edit':
                                $file = 'admin/edit.php';
                                $title = 'Ubah Data Agenda - ';
                                break;
                            case 'detail':
                                $file = 'admin/detail.php';
                                $title = 'Detail Data Agenda - ';
                                break;
                            case 'absen':
                                $file = 'admin/absen.php';
                                $title = 'Data Absen  - ';
                                break;
                            case 'cetak':
                                $file = 'admin/cetak_pdf.php';
                                $title = 'Cetak Data Agenda  - ';
                                break;
                            default:
                                $file = 'admin/agenda.php';
                                $title = 'Data Agenda - ';
                                break;
                        }
                        break;
    
                    case 'notulen':
                        $script = '<script src="vendors/sweetalert/sweetalert.min.js"></script>';
                        switch ($action) {
                            case 'export':
                                $file = 'admin/export_notulen.php';
                                $title = 'Export Notulen - ';
                                break;
                            case 'export_singkat':
                                $file = 'admin/export_singkat.php';
                                $title = 'Export Notulen - ';
                                break;
                            default:
                                $file = 'admin/notulen.php';
                                $title = 'Data Notulen - ';
                                break;
                        }
                        break;
                        
                    case 'akta':
                        $script = '<script src="vendors/sweetalert/sweetalert.min.js"></script>';
                        switch ($action) {
                            case 'buat':
                                $file = 'admin/buat_akta.php';
                                $title = 'Buat Akta Sidang  - ';
                                break;
                            case 'cetak':
                                $file = 'admin/cetak_akta_pdf.php';
                                $title = 'Cetak Akta Sidang  - ';
                                break;    
                            default:
                                $file = 'admin/akta_sidang.php';
                                $title = 'Data Akta Sidang - ';
                                break;
                        }
                        break;
                }
                break;
    
                case ROLE_OPERATOR_1:
                case ROLE_OPERATOR_2:
                case ROLE_OPERATOR_3:
                switch ($page) {
                    case 'agenda':
                        switch ($action) {
                            case 'detail':
                              $file = "operator" . ($id_role - 1) . "/detail.php";
                              $title = 'Detail Data Agenda - ';
                                break;
                            default:
                                $file = "operator". ($id_role  - 1). "/agenda.php";
                                $title = 'Data Agenda - ';
                                break;
                        }
                        break;
    
                        case 'notulen':
                            $script = '<script src="vendors/sweetalert/sweetalert.min.js"></script>';
                            switch ($action) {
                                case 'export':
                                    $file = "operator" . ($id_role - 1) . "/export_notulen.php";
                                    $title = 'Export Notulen - ';
                                    break;
                                case 'export_singkat':
                                    $file = "operator" . ($id_role - 1) . "/export_singkat.php";
                                    $title = 'Export Notulen - ';
                                    break;
                                default:
                                    $file = 'admin/notulen.php';
                                    $title = 'Data Notulen - ';
                                    break;
                            }
                            break;
                }
                break;
    
            case ROLE_PUBLIC:
                switch ($page) {
                    case 'agenda':
                        switch ($action) {
                            case 'detail':
                                $file = 'public/detail.php';
                                $title = 'Detail Data Agenda - ';
                                break;
                            default:
                                $file = 'public/agenda.php';
                                $title = 'Data Agenda - ';
                                break;
                        }
                        break;
    
                    case 'notulen':
                        $file = 'public/notulen.php';
                        $title = 'Data notulen - ';
                        break;
    
                    default:
                        $file = 'public/notulen.php';
                        $title = 'Data Notulen - ';
                        break;
                }
                break;
        }
    } else {
        if ($id_role == ROLE_ADMIN) {
            $file = 'admin/dashboard.php';
        } elseif ($id_role == ROLE_PUBLIC) {
            $file = 'public/dashboard.php'; // or any other file intended for the public role
        } else {
            $file = "operator" . ($id_role - 1) . "/dashboard" . ($id_role - 1) . ".php";
        }
    }
    ?>
    <title><?= $title ?>Sistem Informasi Notulen Rapat</title>
  </head>
  <body>
    <div class="wrapper">
      <?php include('./components/sidebar.php'); ?>
      <div id="main">
        <?php include('./components/navbar.php'); ?>
        <div class='py-3 px-4 bg-warning text-light fs-5'>
          Selamat datang <?= $_SESSION['username'] ?> (<?= getRoleName($_SESSION['id_role']) ?>)
        </div>
        <div id="content">
        <?php
        $path = __DIR__ . '/pages/' . $file;
        if (file_exists($path)) {
            include($path);
        } else {
            echo 'File tidak ditemukan: ' . $path;
        }
        ?>
        </div>
      </div>
    </div>
    <?= $script ?>
  </body>
</html>
