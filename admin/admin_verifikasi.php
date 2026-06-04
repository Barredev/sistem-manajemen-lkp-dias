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

    // --- LOGIKA PROSES VERIFIKASI (TANPA MENGURANGI KUOTA MASTER) ---
    if (isset($_POST['aksi'])) {
        $id_daftar = $_POST['id_daftar'];
        $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);
        $aksi = $_POST['aksi'];

        if ($aksi == 'terima') {
            // 1. Cari tahu anak ini daftar kursus apa
            $get_kursus = mysqli_query($koneksi, "SELECT kursus_id FROM pendaftaran WHERE id_daftar = '$id_daftar'");
            $data_kursus = mysqli_fetch_assoc($get_kursus);
            $kursus_id = $data_kursus['kursus_id'];

            // 2. Ambil Kuota Awal
            $cek_kuota = mysqli_query($koneksi, "SELECT kuota FROM kursus WHERE id_kursus = '$kursus_id'");
            $dt_kuota = mysqli_fetch_assoc($cek_kuota);
            $kuota_awal = $dt_kuota['kuota'];

            // 3. Hitung berapa orang yang SUDAH DITERIMA di kelas ini
            $cek_terisi = mysqli_query($koneksi, "SELECT COUNT(*) as terisi FROM pendaftaran WHERE kursus_id = '$kursus_id' AND status = 'diterima'");
            $dt_terisi = mysqli_fetch_assoc($cek_terisi);
            $jumlah_terisi = $dt_terisi['terisi'];

            // 4. Hitung Sisa Kuota
            $sisa_kuota = $kuota_awal - $jumlah_terisi;

            if ($sisa_kuota > 0) {
                // Jika masih ada sisa, terima peserta (TANPA MENGUBAH tabel kursus)
                $query = "UPDATE pendaftaran SET status = 'diterima', keterangan = '$catatan' WHERE id_daftar = '$id_daftar'";
                mysqli_query($koneksi, $query);
                
                echo "<script>alert('Pendaftaran berhasil diterima!'); window.location='admin_verifikasi.php';</script>";
            } else {
                // Jika sudah penuh
                echo "<script>alert('GAGAL! Kuota untuk kelas ini sudah penuh.'); window.location='admin_verifikasi.php';</script>";
            }
        } else {
            // Jika ditolak
            $query = "UPDATE pendaftaran SET status = 'ditolak', keterangan = '$catatan' WHERE id_daftar = '$id_daftar'";
            mysqli_query($koneksi, $query);
            echo "<script>alert('Pendaftaran telah ditolak.'); window.location='admin_verifikasi.php';</script>";
        }
        exit;
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pendaftar - LKP DIAS</title>
    <link rel="icon" href="../img/logo12.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { overflow-x: hidden; background-color: #f8f9fa; }
        #wrapper { display: flex; width: 100%; align-items: stretch; }
        #sidebar { min-width: 250px; max-width: 250px; min-height: 100vh; background: #343a40; color: #fff; transition: all 0.3s; }
        #sidebar .sidebar-header { padding: 20px; background: #212529; }
        #sidebar ul li a { padding: 12px 20px; display: block; color: #adb5bd; text-decoration: none; }
        #sidebar ul li a:hover { color: #fff; background: #495057; }
        #sidebar ul li.active > a { color: #fff; background: #0d6efd; }
        #sidebar.active { margin-left: -250px; }
        @media (max-width: 768px) { #sidebar { margin-left: -250px; } #sidebar.active { margin-left: 0; } }
        #content { width: 100%; }
        .navbar { padding: 15px 10px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .bukti-img { cursor: pointer; transition: 0.3s; border-radius: 5px; border: 1px solid #ddd; }
        .bukti-img:hover { transform: scale(1.05); }
        #sidebar, #content { transition: all 0.3s; }
    </style>
</head>
<body>

<div id="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header">
            <a href="../index.php" style="text-decoration: none; color: #fff;">
                <h3>LKP DIAS</h3>
            </a>
        </div>
        <ul class="list-unstyled components">
            <hr class="bg-light">
            <p class="ms-3 mb-0" style="font-size: 0.8rem; opacity: 0.7;">PELATIHAN</p>
            <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
            <li><a href="admin_kursus.php"><i class="fas fa-book me-2"></i> Kelola Kursus</a></li>
            <li class="active"><a href="admin_verifikasi.php"><i class="fas fa-check-circle me-2"></i> Verifikasi</a></li>
            <hr class="bg-light">
            <p class="ms-3 mb-0" style="font-size: 0.8rem; opacity: 0.7;">LAPORAN</p>
            <li><a href="admin_laporan_kursus.php"><i class="fas fa-file-alt me-2"></i> Laporan Kursus</a></li>
            <li><a href="admin_laporan_pendaftar.php"><i class="fas fa-users me-2"></i> Laporan Pendaftar</a></li>
            <hr class="bg-light">
            <p class="ms-3 mb-0" style="font-size: 0.8rem; opacity: 0.7;">PENGGUNA</p>
            <li><a href="profile.php"><i class="fas fa-user-circle me-2"></i> Profil Saya</a></li>
            <li>
                <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
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

        <div class="container-fluid">
            <h2 class="mb-4">Verifikasi Pendaftar</h2>

            <div class="card shadow-sm p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Data Peserta</th>
                                <th>Bukti Transfer</th>
                                <th>Proses Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // PERUBAHAN: Menambahkan Sub-Query untuk menghitung otomatis jumlah yang sudah diterima
                            $sql = mysqli_query($koneksi, "
                                SELECT p.*, u.nama, k.nama_kursus, k.kuota,
                                (SELECT COUNT(*) FROM pendaftaran WHERE kursus_id = k.id_kursus AND status = 'diterima') as total_diterima
                                FROM pendaftaran p
                                JOIN users u ON p.user_id = u.id_user 
                                JOIN kursus k ON p.kursus_id = k.id_kursus
                                WHERE p.status = 'pending'
                            ");
                            
                            if (mysqli_num_rows($sql) == 0) : ?>
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">
                                        <i class="fas fa-coffee fa-3x mb-3"></i><br>
                                        Belum ada pendaftaran baru yang perlu diverifikasi.
                                    </td>
                                </tr>
                            <?php endif;

                            while($d = mysqli_fetch_assoc($sql)) :
                                // HITUNG SISA KUOTA UNTUK TAMPILAN
                                $sisa_kuota = $d['kuota'] - $d['total_diterima'];
                            ?>
                            <tr>
                                <td>
                                    <h6 class="mb-0 fw-bold"><?php echo $d['nama']; ?></h6>
                                    <small class="text-muted">Reg No: <?php echo $d['no_reg']; ?></small><br>
                                    <small class="text-primary"><i class="fas fa-book me-1"></i> <?php echo $d['nama_kursus']; ?></small><br>
                                    
                                    <?php if($sisa_kuota > 0): ?>
                                        <span class="badge bg-info mt-1" style="font-size: 0.8rem;">Sisa Kuota: <?php echo $sisa_kuota; ?> / <?php echo $d['kuota']; ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger mt-1" style="font-size: 0.8rem;">Kuota Habis (0 / <?php echo $d['kuota']; ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <img src="../peserta/img/<?php echo $d['bukti_pembayaran']; ?>" 
                                         class="bukti-img" width="80" 
                                         data-bs-toggle="modal" 
                                         data-bs-target="#imgModal<?php echo $d['id_daftar']; ?>">
                                </td>
                                <td>
                                    <form action="" method="POST" class="row g-2">
                                        <input type="hidden" name="id_daftar" value="<?php echo $d['id_daftar']; ?>">
                                        <div class="col-12">
                                            <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Catatan opsional...">
                                        </div>
                                        <div class="col-12">
                                            <?php if($sisa_kuota > 0): ?>
                                                <button type="submit" name="aksi" value="terima" class="btn btn-sm btn-success w-45"><i class="fas fa-check me-1"></i> Terima</button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-secondary w-45" disabled title="Kuota sudah habis"><i class="fas fa-check me-1"></i> Penuh</button>
                                            <?php endif; ?>
                                            
                                            <button type="submit" name="aksi" value="tolak" class="btn btn-sm btn-outline-danger w-45"><i class="fas fa-times me-1"></i> Tolak</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>

                            <div class="modal fade" id="imgModal<?php echo $d['id_daftar']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Bukti Pembayaran - <?php echo $d['nama']; ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="../peserta/img/<?php echo $d['bukti_pembayaran']; ?>" class="img-fluid rounded">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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
                <p class="mb-0">Apakah Anda yakin ingin mengakhiri sesi ini dan keluar dari sistem?</p>
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