<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <button type="button" id="sidebarCollapse" class="btn">
      <i class="fas fa-align-left"></i>
    </button>
    
    <div class="" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-lg-0">
        <li class="nav-item dropdown">
          <a class="nav-link" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?= $_SESSION['username'] ?> <i class="fas fa-caret-down"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end position-absolute" aria-labelledby="navbarDropdown">
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> keluar</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>