<?php
session_start(); // Tambahkan ini untuk mengecek sesi login
include 'database/koneksi.php';

// Cek status login
$is_logged_in = isset($_SESSION['login']) && $_SESSION['login'] === true;
$role = $is_logged_in ? $_SESSION['role'] : '';

/* PERUBAHAN 1: Ambil data kursus sekalian menghitung berapa peserta yang statusnya 'diterima' */
$query_kursus = mysqli_query(
    $koneksi,
    "SELECT k.*, 
     (SELECT COUNT(*) FROM pendaftaran WHERE kursus_id = k.id_kursus AND status = 'diterima') as total_terisi
     FROM kursus k
     WHERE k.status = 'aktif' 
     ORDER BY k.created_at DESC 
     LIMIT 5"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Selamat Datang di eDIAS</title>
  <link rel="icon" href="img/logo12.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

    body, html {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #c1d5fe, #f6d0e7);
      color: #4a4a4a;
      overflow-x: hidden;
    }

    .hero-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      padding: 80px 20px;
    }

    .welcome-left {
      text-align: left;
      padding-right: 50px;
    }

    .about-right {
      background: rgba(255, 255, 255, 0.4);
      backdrop-filter: blur(10px);
      padding: 40px;
      border-radius: 30px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }

    .welcome-left img {
      width: 100px;
      margin-bottom: 20px;
      filter: drop-shadow(0 0 6px rgba(255, 255, 255, 0.7));
      border-radius: 15px;
      background: white;
      padding: 8px;
    }

    h1 {
      font-weight: 600;
      font-size: 3rem;
      color: #3b3b3b;
      line-height: 1.2;
    }

    .btn-cover {
      background-color: #91a3fa;
      color: white;
      font-weight: 600;
      padding: 12px 45px;
      border-radius: 50px;
      border: none;
      box-shadow: 0 8px 15px rgba(145, 163, 250, 0.4);
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .btn-cover:hover {
      background-color: #6f86d6;
      transform: translateY(-3px);
      color: white;
    }

    .card-paket {
      border: none;
      border-radius: 15px;
      transition: transform 0.3s;
    }
    
    .card-paket:hover {
      transform: translateY(-5px);
    }

    @media (max-width: 991px) {
      .welcome-left {
        text-align: center;
        padding-right: 0;
        margin-bottom: 50px;
      }
      h1 { font-size: 2.5rem; }
    }
  </style>
</head>
<body>

  <div class="container hero-wrapper">
    <div class="row align-items-center">
      
      <div class="col-lg-6 welcome-left">
        <img src="img/logo12.png" alt="Logo LKP DIAS" />
        <h1 class="mb-3">Selamat Datang di <span style="color: #d63384;">eDIAS</span></h1>
        
        <?php if($is_logged_in): ?>
            <p class="lead mb-4">Halo, <strong><?= $_SESSION['nama']; ?></strong>! Selamat datang kembali.</p>
            
            <?php if($role == 'admin'): ?>
                <a href="admin/admin_dashboard.php" class="btn btn-cover">Masuk Dashboard Admin</a>
            <?php else: ?>
                <a href="peserta/peserta_dashboard.php" class="btn btn-cover">Masuk Dashboard Saya</a>
            <?php endif; ?>
            <a href="auth/logout.php" class="btn btn-danger ms-2" style="border-radius: 50px; padding: 12px 30px; font-weight: 600; text-decoration: none;">Logout</a>
            
        <?php else: ?>
            <p class="lead mb-4">Sistem Pendaftaran Kursus Tata Busana terpadu untuk masa depan kreatif Anda.</p>
            <a href="auth/login.php" class="btn btn-cover">Mulai</a>
        <?php endif; ?>
        
        <div class="mt-4 d-none d-lg-block">
            <p class="small mb-2">📍 Jl. Mangunkerta No. 123, Kota Cugenang</p>
            <p class="small mb-2">📱 WA: 082218488366</p>
            <p class="small mb-2">📧 Email: lkp.dias@gmail.com</p>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="about-right">
          <h3 class="fw-bold mb-3">Tentang LKP DIAS</h3>
          <p class="text-muted mb-4">
            Kami adalah pusat pelatihan Tata Busana yang berfokus mencetak desainer kreatif dan penjahit profesional. Dari dasar hingga mahir, kami siap membimbing Anda.
          </p>

          <div class="row g-3">
            <div class="col-sm-6">
              <div class="card card-paket h-100 shadow-sm" style="background: #f0f4ff;">
                <div class="card-body p-3">
                  <h6 class="fw-bold text-primary mb-1">Paket Reguler</h6>
                  <p class="small mb-2 text-muted">Durasi 6 Bulan</p>
                  <span class="fw-bold">Rp 3.000.000</span>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="card card-paket h-100 shadow-sm" style="background: #fff4f0;">
                <div class="card-body p-3">
                  <h6 class="fw-bold text-danger mb-1">Prog. Pemerintah</h6>
                  <p class="small mb-2 text-muted">Durasi 3 Bulan</p>
                  <span class="fw-bold text-success">Gratis (Subsidi)</span>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-4 p-3 rounded" style="background: rgba(255,255,255,0.5); border-left: 4px solid #91a3fa;">
            <h6 class="fw-bold mb-2">💳 Info Pembayaran</h6>
            <p class="small mb-1"><strong>BRI:</strong> 1234-01-234567-89 (LKP DIAS)</p>
            <p class="small mb-0"><strong>WA Konfirmasi:</strong> 0822-1848-8366</p>
          </div>
        </div>
      </div>

    </div>
  </div>

<div class="container mt-5">
  <div class="text-center mb-4">
    <h2 class="fw-bold">📚 Kursus Terbaru</h2>
    <p class="text-muted">Kursus yang sedang dibuka</p>
  </div>

  <div class="row g-4 justify-content-center">

    <?php if (mysqli_num_rows($query_kursus) > 0): ?>
      <?php while ($k = mysqli_fetch_assoc($query_kursus)): 
          
          /* PERUBAHAN 2: Hitung sisa kuota */
          $sisa_kuota = $k['kuota'] - $k['total_terisi'];
      ?>

        <div class="col-md-6 col-lg-4">
          <div class="card h-100 shadow-sm border-0 card-paket text-center">

            <div class="card-body">
              <h5 class="fw-bold text-primary">
                <?= $k['nama_kursus']; ?>
              </h5>

              <p class="small text-muted mb-1">
                <i class="bi bi-calendar-event"></i>
                <?= date('d-m-Y', strtotime($k['tanggal_mulai'])); ?> s/d
                <?= date('d-m-Y', strtotime($k['tanggal_selesai'])); ?>
              </p>

              <p class="small text-muted mb-2">
                <i class="bi bi-clock"></i>
                <?= $k['hari']; ?>, <?= substr($k['jam'], 0, 5); ?> WIB
              </p>

              <p class="small mb-2">
                <i class="bi bi-people"></i>
                <?php if($sisa_kuota > 0): ?>
                    Sisa Kuota: <span class="badge bg-info text-dark"><?= $sisa_kuota; ?> / <?= $k['kuota']; ?></span>
                <?php else: ?>
                    <span class="badge bg-danger">Kuota Habis</span>
                <?php endif; ?>
              </p>

              <span class="fw-bold text-success">
                <?= ($k['harga'] > 0) 
                    ? "Rp " . number_format($k['harga'], 0, ',', '.') 
                    : "Gratis"; ?>
              </span>
            </div>

            <div class="card-footer bg-transparent border-0 text-center pb-3">
                <?php if($sisa_kuota > 0): ?>
                    <?php if($is_logged_in): ?>
                        <?php if($role == 'admin'): ?>
                            <a href="admin/admin_kursus.php" class="btn btn-outline-secondary btn-sm rounded-pill">Lihat Daftar Kelas</a>
                        <?php else: ?>
<a href="peserta/form_daftar.php?id=<?= $k['id_kursus']; ?>" class="btn btn-outline-primary btn-sm rounded-pill">Lanjut Daftar</a>                        <?php endif; ?>
                    <?php else: ?>
                        <a href="auth/login.php" class="btn btn-outline-primary btn-sm rounded-pill">Login untuk Daftar</a>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="btn btn-secondary btn-sm rounded-pill" disabled>Pendaftaran Ditutup</button>
                <?php endif; ?>
            </div>

          </div>
        </div>

      <?php endwhile; ?>
    <?php else: ?>

      <div class="col-12 text-center">
        <div class="alert alert-info">
          Belum ada kursus yang tersedia
        </div>
      </div>

    <?php endif; ?>

  </div>
</div>

<br><br>

<div class="container">
  <div class="text-center mb-4">
    <h2 class="fw-bold">
      <i class="bi bi-images"></i> Galeri
    </h2>
    <p class="text-muted">Kegiatan Kursus</p>
  </div>

  <div class="row justify-content-center">
    <div class="col-md-4">
      <div class="card shadow-sm">
        <img src="img/kelas.jpg" class="card-img-top" alt="Galeri">
        <div class="card-body text-center">
          <p class="text-muted small">
            Kegiatan praktik kelas dasar menjahit
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<br><br>

</body>
</html>