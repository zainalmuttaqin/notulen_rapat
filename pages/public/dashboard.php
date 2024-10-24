<?php

$query = "SELECT id FROM detail_rapat";
$result = mysqli_query($conn, $query);
$detail_rapat = $result->num_rows;

$query = "SELECT id FROM agenda";
$result = mysqli_query($conn, $query);
$agenda = $result->num_rows;

?>
<div class="row mt-3">
  <div class="col">
    <div class="small-box bg-danger">
      <div class="inner">
          <h3> <?= $agenda ?> </h3>
          <p class='text-light'> Agenda Rapat</p>
      </div>
      <i class="icon fas fa-clock"></i>
    </div>
  </div>



  <div class="col">
    <div class="small-box bg-success">
      <div class="inner">
          <h3> <?= $detail_rapat ?> </h3>
          <p class='text-light'> Detail Rapat</p>
      </div>
      <i class="icon fas fa-list"></i>
    </div>
  </div>

  
  <div class="col-12">
    <div class="card h-100">
      <div class="card-header"><h3>Pengumuman Terbaru</h3></div>
      <div class="card-body">
      <?php 
      $query = "SELECT d.*, a.judul_rapat, a.tgl_rapat, a.uraian_rapat FROM detail_rapat AS d 
                JOIN agenda AS a ON d.id_agenda = a.id
                WHERE d.status IN ('disposisi', 'belum diproses') 
                ORDER BY a.tgl_rapat DESC";
      $result = mysqli_query($conn, $query);
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
      ?>                      
      <div class='border-bottom mb-4'>
        <h5><strong style="font-weight: bold;">Judul Rapat: </strong> <?= $row['judul_rapat'] ?></h5>
        <p><strong style="font-weight: bold;">Tanggal Rapat:</strong> <?=  date('d/m/Y', strtotime($row['tgl_rapat'])) ?></p>
        <p><strong style="font-weight: bold;">Uraian Rapat:</strong> <span style="text-align: justify;"><?= $row['uraian_rapat'] ?></span></p>
        <p><strong style="font-weight: bold;">Bahasan:</strong> <?= $row['bahasan'] ?></p>
        <p><strong style="font-weight: bold;">Isi Bahasan:</strong> <span style="text-align: justify;"><?= $row['usulan'] ?></span></p>
        <p><strong style="font-weight: bold;">Status:</strong> <?= $row['status'] ?> (<?= $row['disposisi'] ?>)</p>
      </div>
      <?php        
        }   
      } else {
      ?>
      <h5>Tidak ada pengumuman</h5>
      <?php
      }
      ?>
      </div>
    </div>
  </div>
</div>