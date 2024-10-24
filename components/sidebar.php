<nav id="sidebar">
    <div class="sidebar-header">
        <h3><span class='text-white fw-bold'></span>Notulen Rapat</h3>
        <strong><span class='text-white'>N</span>R</strong>
    </div>
    <ul class="list-unstyled components">
        <li class="<?= (!isset($page) ? 'active' : $page) === '' ? 'active' : '' ?>">
            <a href="index.php">
            <div class="row">
                <div class='col-12 col-md-2 sidebar-item-icon' >
                    <i class="fas fa-home"></i>
                </div>
                <span class="col-10">Dashboard</span>
            </div>
            </a>
        </li>
        <?php
            $menu = [
                [
                    "label" => "Ruang Rapat",
                    "icon" => "fa-clock",
                    "href" => "agenda",
                ],
                [
                    "label" => "Notulen Rapat",
                    "icon" => "fa-book",
                    "href" => "notulen",
                ],
                [
                    "label" => "Akta Sidang",
                    "icon" => "fa-file",
                    "href" => "akta",
                ],
            ];
            if ($_SESSION['username']) {
                foreach ($menu as $menu) {
        ?>
        <li class="<?= $page === $menu['href'] ? 'active' : '' ?>">
            <a href="index.php?page=<?=$menu['href']?>">
                <div class="row">
                    <div class='col-12 col-md-2 sidebar-item-icon' >
                        <i class="fas <?=$menu['icon']?>"></i>
                    </div>
                    <span class="col-10"><?=$menu['label']?></span>
                </div>
            </a>
        </li>
        <?php
            }
        }
        ?>
    </ul>

</nav>