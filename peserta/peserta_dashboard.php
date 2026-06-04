<?php
    session_start();
    // Atur zona waktu sesuai WIB agar absen real-time akurat
    date_default_timezone_set('Asia/Jakarta'); 
    include '../database/koneksi.php';

    if (!isset($_SESSION['login'])) {
        header("Location: ../auth/login.php");
        exit;
    }

    $id_user   = $_SESSION['id_user'];
    $nama_user = $_SESSION['nama'];

    /* ==========================================
       LOGIKA PROSES ABSENSI (MASUK & KELUAR)
    ========================================== */
    if (isset($_POST['aksi_absen'])) {
        $id_daftar = $_POST['id_daftar'];
        
        if ($_POST['aksi_absen'] == 'masuk') {
            // Catat waktu masuk
            mysqli_query($koneksi, "INSERT INTO absensi (id_daftar, waktu_in) VALUES ('$id_daftar', NOW())");
            echo "<script>alert('Berhasil Absen Masuk! Selamat belajar.'); window.location='peserta_dashboard.php';</script>";
            exit;
        } 
        
        elseif ($_POST['aksi_absen'] == 'keluar') {
            $id_absen = $_POST['id_absen'];
            $target_jam = $_POST['target_jam'];
            $target_menit = $target_jam * 60; // Konversi target ke menit
            
            // 1. Update waktu keluar dan hitung durasi_menit
            mysqli_query($koneksi, "UPDATE absensi SET waktu_out = NOW(), durasi_menit = TIMESTAMPDIFF(MINUTE, waktu_in, NOW()) WHERE id_absen = '$id_absen'");
            
            // 2. Ambil durasi yang baru saja dihitung
            $cek_durasi = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT durasi_menit FROM absensi WHERE id_absen = '$id_absen'"));
            $menit_belajar_hari_ini = $cek_durasi['durasi_menit'];

            // 3. Tambahkan ke total jam_ditempuh di tabel pendaftaran
            mysqli_query($koneksi, "UPDATE pendaftaran SET jam_ditempuh = jam_ditempuh + $menit_belajar_hari_ini WHERE id_daftar = '$id_daftar'");
            
            // 4. Cek apakah total menit sudah memenuhi target kursus
            $cek_total = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT jam_ditempuh FROM pendaftaran WHERE id_daftar = '$id_daftar'"));
            
            if ($cek_total['jam_ditempuh'] >= $target_menit) {
                // LULUS OTOMATIS!
                mysqli_query($koneksi, "UPDATE pendaftaran SET status = 'selesai' WHERE id_daftar = '$id_daftar'");
                echo "<script>alert('Absen Keluar Berhasil! SELAMAT, Anda telah menyelesaikan seluruh jam kursus ini!'); window.location='peserta_dashboard.php';</script>";
            } else {
                echo "<script>alert('Absen Keluar Berhasil! Waktu belajar Anda hari ini tercatat: $menit_belajar_hari_ini menit.'); window.location='peserta_dashboard.php';</script>";
            }
            exit;
        }
    }

    /* Ambil pendaftaran terakhir */
    $cek_reg = mysqli_query(
        $koneksi,
        "SELECT no_reg FROM pendaftaran 
        WHERE user_id = '$id_user' 
        ORDER BY id_daftar DESC LIMIT 1"
    );
    $data_reg = mysqli_fetch_assoc($cek_reg);

    /* Ambil data user untuk foto */
    $user = mysqli_fetch_assoc(
        mysqli_query($koneksi, "SELECT foto, nama FROM users WHERE id_user='$id_user'")
    );

    $path_folder = "img/";

    if (!empty($user['foto']) && file_exists($path_folder . $user['foto'])) {
        $foto_tampil = $path_folder . $user['foto'];
    } else {
        $foto_tampil = "https://ui-avatars.com/api/?name=" . urlencode($user['nama']) . "&background=random";
    }

    /* CEK KELENGKAPAN PROFIL */
    $profil_lengkap = true;
    $pesan_alert = "";
    if (empty($user['foto'])) {
        $profil_lengkap = false;
        $pesan_alert = "Silakan lengkapi data profil Anda, termasuk mengunggah foto. Data ini diperlukan untuk kebutuhan sertifikat.";
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Peserta - LKP DIAS</title>
    <link rel="icon" href="../img/logo12.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        #wrapper { display: flex; min-height: 100vh; }
        #sidebar { width: 250px; background: #343a40; color: #fff; transition: all 0.3s; }
        #sidebar.active { margin-left: -250px; }
        #sidebar .sidebar-header { padding: 20px; background: #212529; text-align: center; }
        #sidebar ul li a { color: #adb5bd; padding: 12px 20px; display: block; text-decoration: none; }
        #sidebar ul li a:hover, #sidebar ul li.active a { background: #0d6efd; color: #fff; }
        #content { width: 100%; }
        .navbar { background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,.1); }
        @media (max-width: 768px) { #sidebar { margin-left: -250px; } #sidebar.active { margin-left: 0; } }
        #sidebar, #content { transition: all 0.3s; }
        .card-absen { border-left: 5px solid #0d6efd; }
    </style>
</head>
<body>

<div id="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header">
            <a href="../index.php" style="text-decoration: none; color: #fff;"><h3>LKP DIAS</h3></a>
        </div>
        <ul class="list-unstyled components">
            <li class="active"><a href="peserta_dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a></li>
            <li><a href="profile.php"><i class="fas fa-user me-2"></i> My Profile</a></li>
            <li><a href="pendaftaran_saya.php"><i class="fas fa-list me-2"></i> Riwayat Pendaftaran</a></li>
            <li><a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg px-3">
            <button class="btn btn-outline-primary" id="sidebarCollapse"><i class="fas fa-bars"></i></button>
            <div class="ms-auto d-flex align-items-center">
                <span class="me-3 d-none d-md-inline">Halo, <strong><?php echo $nama_user; ?></strong></span>
                <img src="<?php echo $foto_tampil; ?>" class="rounded-circle border" width="40" height="40" style="object-fit: cover;">
            </div>
        </nav>

        <div class="container-fluid mt-4">
            <div class="card shadow-sm p-4 mb-4">
                <h3>Selamat Datang, <?php echo $nama_user; ?> 👋</h3>
                
                <?php if (!$profil_lengkap): ?>
                    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Perhatian!</strong><br><?php echo $pesan_alert; ?>
                        <br><a href="profile.php" class="alert-link">Lengkapi Profil Sekarang</a>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info mt-3 mb-0">
                    <strong>No. Registrasi Aktif:</strong> <?php echo $data_reg ? $data_reg['no_reg'] : 'Belum mendaftar kursus'; ?>
                </div>
            </div>

            <?php
                // Cek kelas yang sedang aktif (diterima)
                $cek_kelas = mysqli_query($koneksi, "
                    SELECT p.*, k.nama_kursus, k.target_jam 
                    FROM pendaftaran p
                    JOIN kursus k ON p.kursus_id = k.id_kursus
                    WHERE p.user_id = '$id_user' AND p.status = 'diterima'
                ");

                if (mysqli_num_rows($cek_kelas) > 0) :
            ?>
                <h4 class="mb-3">Kelasku Saat Ini</h4>
                <div class="row mb-4">
                    <?php while ($kls = mysqli_fetch_assoc($cek_kelas)) : 
                        $target_menit = $kls['target_jam'] * 60;
                        $jam_ditempuh = floor($kls['jam_ditempuh'] / 60);
                        $sisa_menit = $kls['jam_ditempuh'] % 60;
                        $persen = ($target_menit > 0) ? min(100, round(($kls['jam_ditempuh'] / $target_menit) * 100)) : 0;
                    ?>
                        <div class="col-md-6">
                            <div class="card shadow-sm card-absen h-100">
                                <div class="card-body">
                                    <h5 class="fw-bold text-primary"><i class="fas fa-book-reader me-2"></i> <?php echo $kls['nama_kursus']; ?></h5>
                                    
                                    <p class="small text-muted mb-2 mt-3">Progres Jam Belajar:</p>
                                    <div class="progress mb-2" style="height: 20px; border-radius: 10px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: <?php echo $persen; ?>%;"><?php echo $persen; ?>%</div>
                                    </div>
                                    <p class="small fw-bold">Tercapai: <?php echo $jam_ditempuh; ?> Jam <?php echo $sisa_menit; ?> Menit / Target: <?php echo $kls['target_jam']; ?> Jam</p>

                                    <hr>
                                    
                                    <?php
                                        // Cek absensi hari ini untuk id_daftar ini
                                        $id_daftar_aktif = $kls['id_daftar'];
                                        $cek_absen = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_daftar = '$id_daftar_aktif' AND DATE(waktu_in) = CURDATE()");
                                        $absen_hari_ini = mysqli_fetch_assoc($cek_absen);
                                    ?>

                                    <form action="" method="POST" class="mt-3">
                                        <input type="hidden" name="id_daftar" value="<?php echo $id_daftar_aktif; ?>">
                                        <input type="hidden" name="target_jam" value="<?php echo $kls['target_jam']; ?>">
                                        
                                        <?php if (!$absen_hari_ini) : // Belum absen sama sekali hari ini ?>
                                            <p class="small text-muted mb-2"><i class="fas fa-clock"></i> Anda belum absen hari ini.</p>
                                            <button type="submit" name="aksi_absen" value="masuk" class="btn btn-primary w-100">
                                                <i class="fas fa-sign-in-alt me-1"></i> Absen Masuk Sekarang
                                            </button>
                                            
                                        <?php elseif (is_null($absen_hari_ini['waktu_out'])) : // Sudah masuk, belum keluar ?>
                                            <input type="hidden" name="id_absen" value="<?php echo $absen_hari_ini['id_absen']; ?>">
                                            <p class="small text-warning fw-bold mb-2"><i class="fas fa-spinner fa-spin"></i> Kelas Sedang Berlangsung...</p>
                                            <p class="small text-muted">Masuk pukul: <?php echo date('H:i', strtotime($absen_hari_ini['waktu_in'])); ?> WIB</p>
                                            <button type="submit" name="aksi_absen" value="keluar" class="btn btn-danger w-100" onclick="return confirm('Yakin ingin absen keluar? Pastikan kelas sudah benar-benar selesai.')">
                                                <i class="fas fa-sign-out-alt me-1"></i> Absen Keluar (Selesai Kelas)
                                            </button>
                                            
                                        <?php else : // Sudah absen masuk dan keluar ?>
                                            <div class="alert alert-success p-2 text-center mb-0">
                                                <i class="fas fa-check-circle me-1"></i> Anda sudah menyelesaikan kelas hari ini.<br>
                                                <small>Durasi tercatat: <strong><?php echo $absen_hari_ini['durasi_menit']; ?> menit</strong></small>
                                            </div>
                                        <?php endif; ?>
                                    </form>

    <a href="riwayat_absen.php?id=<?php echo $id_daftar_aktif; ?>" class="btn btn-outline-secondary w-100 mt-2 shadow-sm">
        <i class="fas fa-history me-1"></i> Lihat Riwayat Absen
    </a>

                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>


            <div class="card shadow-sm p-4">
                <h4 class="mb-3">Daftar Kursus Menjahit yang Tersedia</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Nama Kursus</th>
                                <th>Jadwal / Target</th>
                                <th>Periode</th>
                                <th>Kuota</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no = 1;
                        $query_kursus = mysqli_query($koneksi, "SELECT * FROM kursus WHERE status='aktif'");
                        while ($data = mysqli_fetch_assoc($query_kursus)) :
                            $id_k = $data['id_kursus'];
                            $cek = mysqli_query($koneksi, "SELECT * FROM pendaftaran WHERE kursus_id='$id_k' AND status='diterima'");
                            $jumlah = mysqli_num_rows($cek);
                            $sisa = $data['kuota'] - $jumlah;
                        ?>
                            <tr class="text-center">
                                <td><?php echo $no++; ?></td>
                                <td class="text-start"><?php echo $data['nama_kursus']; ?></td>
                                <td>
                                    <?php echo $data['hari'] . ", " . substr($data['jam'], 0, 5) . " WIB"; ?><br>
                                    <small class="badge bg-secondary">Target: <?php echo $data['target_jam']; ?> Jam</small>
                                </td>
                                <td>
                                    <?php 
                                        echo date('d-m-Y', strtotime($data['tanggal_mulai'])) . " s/d " . date('d-m-Y', strtotime($data['tanggal_selesai'])); 
                                    ?>
                                </td>
                                <td><?php echo $sisa . " / " . $data['kuota']; ?></td>
                                <td>Rp <?php echo number_format($data['harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ($sisa > 0) : ?>
                                        <a href="form_daftar.php?id=<?php echo $id_k; ?>" class="btn btn-success btn-sm" onclick="return confirm('Yakin ingin mendaftar kursus ini?')">Daftar</a>
                                    <?php else : ?>
                                        <span class="badge bg-danger">Kuota Penuh</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <footer class="text-center text-muted mt-5 mb-3">
            <small>&copy; <?php echo date("Y"); ?> LKP DIAS</small>
        </footer>
    </div>
</div>

<div class="modal fade" id="logoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Logout</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-exclamation-circle text-warning fa-3x mb-3"></i>
                <p>Yakin ingin keluar dari sistem?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="../auth/logout.php" class="btn btn-danger">Ya, Logout</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const sidebar = document.getElementById('sidebar');
        const btn = document.getElementById('sidebarCollapse');
        btn.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });
    });
</script>

</body>
</html>