<?php
    session_start();
    include '../database/koneksi.php';

    if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../auth/login.php");
        exit;
    }

    // LOGIKA FOTO PROFIL
    $path_folder = "img/"; 
    $foto_session = isset($_SESSION['foto']) ? $_SESSION['foto'] : '';
    $foto_tampil = (!empty($foto_session) && file_exists($path_folder . $foto_session)) 
                   ? $path_folder . $foto_session 
                   : "https://ui-avatars.com/api/?name=" . urlencode($_SESSION['nama']) . "&background=random";

    /* ==========================================
       1. AMBIL DATA UNTUK KOTAK RINGKASAN ATAS
       ========================================== */
    $q_tot_kursus = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as tot FROM kursus"));
    $q_tot_siswa = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as tot FROM pendaftaran WHERE status IN ('diterima', 'selesai')"));
    $q_tot_lulus = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as tot FROM pendaftaran WHERE status = 'selesai'"));
    $q_tot_pending = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as tot FROM pendaftaran WHERE status = 'menunggu'"));

    /* ==========================================
       2. AMBIL DATA UNTUK GRAFIK BATANG (BAR CHART)
       Menghitung jumlah siswa per masing-masing kursus
       ========================================== */
    $label_kursus = [];
    $data_siswa_per_kursus = [];
    
    $query_bar = mysqli_query($koneksi, "
        SELECT k.nama_kursus, COUNT(p.id_daftar) as total_siswa 
        FROM kursus k 
        LEFT JOIN pendaftaran p ON k.id_kursus = p.kursus_id AND p.status IN ('diterima', 'selesai')
        GROUP BY k.id_kursus
    ");

    while($row = mysqli_fetch_assoc($query_bar)) {
        $label_kursus[] = $row['nama_kursus'];
        $data_siswa_per_kursus[] = $row['total_siswa'];
    }

    /* ==========================================
       3. AMBIL DATA UNTUK GRAFIK DONAT (DOUGHNUT CHART)
       Menghitung perbandingan siswa aktif vs lulus
       ========================================== */
    $label_status = ['Sedang Aktif', 'Sudah Lulus'];
    $data_status = [0, 0];

    $query_pie = mysqli_query($koneksi, "
        SELECT status, COUNT(id_daftar) as total 
        FROM pendaftaran 
        WHERE status IN ('diterima', 'selesai') 
        GROUP BY status
    ");

    while($row = mysqli_fetch_assoc($query_pie)) {
        if($row['status'] == 'diterima') {
            $data_status[0] = $row['total'];
        } elseif($row['status'] == 'selesai') {
            $data_status[1] = $row['total'];
        }
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - LKP DIAS</title>
    <link rel="icon" href="../img/logo12.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { overflow-x: hidden; background-color: #f4f7f6; }
        #wrapper { display: flex; width: 100%; align-items: stretch; }
        #sidebar { min-width: 250px; max-width: 250px; min-height: 100vh; background: #343a40; color: #fff; transition: all 0.3s; }
        #sidebar .sidebar-header { padding: 20px; background: #212529; }
        #sidebar ul li a { padding: 12px 20px; display: block; color: #adb5bd; text-decoration: none; }
        #sidebar ul li a:hover { color: #fff; background: #495057; }
        #sidebar ul li.active > a { color: #fff; background: #0d6efd; }
        #sidebar.active { margin-left: -250px; }
        @media (max-width: 768px) { #sidebar { margin-left: -250px; } #sidebar.active { margin-left: 0; } }
        #content { width: 100%; }
        .navbar { padding: 15px 10px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px; }
        
        /* Desain Kotak Ringkasan */
        .stat-card { border-radius: 15px; border: none; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-box { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    </style>
</head>
<body>

<div id="wrapper">

    <nav id="sidebar">
        <div class="sidebar-header">
            <a href="../index.php" style="text-decoration: none; color: #fff;"><h3>LKP DIAS</h3></a>
        </div>
        <ul class="list-unstyled components">
            <hr class="bg-light">
            <p class="ms-3 mb-0" style="font-size: 0.8rem; opacity: 0.7;">PELATIHAN</p>
            <li class="active"><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
            <li><a href="admin_kursus.php"><i class="fas fa-book me-2"></i> Kelola Kursus</a></li>
            <li><a href="admin_verifikasi.php"><i class="fas fa-check-circle me-2"></i> Verifikasi</a></li>
            <hr class="bg-light">
            <p class="ms-3 mb-0" style="font-size: 0.8rem; opacity: 0.7;">LAPORAN</p>
            <li><a href="admin_laporan_kursus.php"><i class="fas fa-file-alt me-2"></i> Laporan Kursus</a></li>
            <li><a href="admin_laporan_pendaftar.php"><i class="fas fa-users me-2"></i> Laporan Pendaftar</a></li>
            <hr class="bg-light">
            <p class="ms-3 mb-0" style="font-size: 0.8rem; opacity: 0.7;">PENGGUNA</p>
            <li><a href="profile.php"><i class="fas fa-user-circle me-2"></i> Profil Saya</a></li>
            <li><a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <button type="button" class="btn btn-outline-primary" id="sidebarCollapse"><i class="fas fa-align-left"></i></button>
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3 d-none d-md-inline">Halo, <strong><?php echo $_SESSION['nama']; ?></strong></span>
                    <img src="<?php echo $foto_tampil; ?>" class="rounded-circle border" width="35" height="35" style="object-fit: cover;">
                </div>
            </div>
        </nav>

        <div class="container-fluid px-4 mb-5">
            <h3 class="mb-4 fw-bold text-dark">Ikhtisar Sistem</h3>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm h-100 bg-white">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box bg-primary text-white me-3 shadow"><i class="fas fa-book-open"></i></div>
                            <div>
                                <h6 class="text-muted mb-1">Total Kursus</h6>
                                <h3 class="mb-0 fw-bold"><?php echo $q_tot_kursus['tot']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm h-100 bg-white">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box bg-info text-white me-3 shadow"><i class="fas fa-users"></i></div>
                            <div>
                                <h6 class="text-muted mb-1">Total Siswa</h6>
                                <h3 class="mb-0 fw-bold"><?php echo $q_tot_siswa['tot']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm h-100 bg-white">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box bg-success text-white me-3 shadow"><i class="fas fa-user-graduate"></i></div>
                            <div>
                                <h6 class="text-muted mb-1">Alumni / Lulus</h6>
                                <h3 class="mb-0 fw-bold"><?php echo $q_tot_lulus['tot']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm h-100 bg-white">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box bg-warning text-dark me-3 shadow"><i class="fas fa-clipboard-list"></i></div>
                            <div>
                                <h6 class="text-muted mb-1">Pendaftar Baru</h6>
                                <h3 class="mb-0 fw-bold"><?php echo $q_tot_pending['tot']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card shadow-sm border-0" style="border-radius: 15px;">
                        <div class="card-header bg-white py-3 border-0">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-chart-bar text-primary me-2"></i> Peminat per Kursus</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="barChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card shadow-sm border-0" style="border-radius: 15px;">
                        <div class="card-header bg-white py-3 border-0">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-chart-pie text-success me-2"></i> Status Siswa</h5>
                        </div>
                        <div class="card-body d-flex justify-content-center">
                            <div style="width: 80%;">
                                <canvas id="pieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="logoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-exclamation-circle text-warning fa-3x mb-3"></i>
                <p class="mb-0">Yakin ingin mengakhiri sesi ini dan keluar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="../auth/logout.php" class="btn btn-danger">Ya, Keluar</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Script Sidebar
    document.addEventListener("DOMContentLoaded", function () {
        const sidebar = document.getElementById('sidebar');
        const btn = document.getElementById('sidebarCollapse');
        btn.addEventListener('click', function () { sidebar.classList.toggle('active'); });
    });

    /* ==========================================
       SCRIPT UNTUK MENGGAMBAR GRAFIK (CHART.JS)
       ========================================== */
    
    // 1. Setup Data untuk Bar Chart (Dari PHP ke JS)
    const ctxBar = document.getElementById('barChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($label_kursus); ?>, // Label dari PHP
            datasets: [{
                label: 'Jumlah Siswa Aktif & Lulus',
                data: <?php echo json_encode($data_siswa_per_kursus); ?>, // Angka dari PHP
                backgroundColor: 'rgba(13, 110, 253, 0.7)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1,
                borderRadius: 5
            }]
        },
        options: {
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
            plugins: { legend: { display: false } }
        }
    });

    // 2. Setup Data untuk Doughnut Chart (Dari PHP ke JS)
    const ctxPie = document.getElementById('pieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($label_status); ?>, // Label Status
            datasets: [{
                data: <?php echo json_encode($data_status); ?>, // Angka Perbandingan
                backgroundColor: ['#0dcaf0', '#198754'], // Warna Biru Muda & Hijau
                hoverOffset: 4
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>

</body>
</html>