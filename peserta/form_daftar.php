<?php
session_start();
include '../database/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_user   = $_SESSION['id_user'];
$nama_user = $_SESSION['nama'];

/* Ambil ID kursus */
if (!isset($_GET['id'])) {
    header("Location: peserta_dashboard.php");
    exit;
}

$id_kursus = $_GET['id'];
$k = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT * FROM kursus WHERE id_kursus='$id_kursus'")
);

/* Ambil data user */
$user = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT foto, nama FROM users WHERE id_user='$id_user'")
);

$path_folder = "img/";
if (!empty($user['foto']) && file_exists($path_folder . $user['foto'])) {
    $foto_tampil = $path_folder . $user['foto'];
} else {
    $foto_tampil = "https://ui-avatars.com/api/?name=" . urlencode($user['nama']) . "&background=random";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pendaftaran - LKP DIAS</title>
    <link rel="icon" href="../img/logo12.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body { background: #f8f9fa; }
        #wrapper { display: flex; min-height: 100vh; }
        #sidebar { width: 250px; background: #343a40; color: #fff; }
        #sidebar .sidebar-header { padding: 20px; background: #212529; text-align: center; }
        #sidebar ul li a { color: #adb5bd; padding: 12px 20px; display: block; text-decoration: none; }
        #sidebar ul li a:hover { background: #0d6efd; color: #fff; }
        #content { width: 100%; }
        .navbar { background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,.1); }
        
        /* Desktop: Efek saat tombol diklik (Sidebar sembunyi) */
        #sidebar.active { margin-left: -250px; }

        /* Mobile: Sembunyikan sidebar secara default */
        @media (max-width: 768px) {
            #sidebar { margin-left: -250px; }
            /* Mobile: Efek saat tombol diklik (Sidebar muncul) */
            #sidebar.active { margin-left: 0; }
        }
        
        /* Animasi transisi agar halus */
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
            <li class="active">
                <a href="peserta_dashboard.php">
                    <i class="fas fa-home me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="profile.php">
                    <i class="fas fa-user me-2"></i> My Profile
                </a>
            </li>
            <li>
                <a href="pendaftaran_saya.php">
                    <i class="fas fa-list me-2"></i> Riwayat Pendaftaran
                </a>
            </li>
            <li>
                <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

<div id="content">

<nav class="navbar navbar-expand-lg px-3">
    <button class="btn btn-outline-primary" id="sidebarCollapse">
        <i class="fas fa-bars"></i>
    </button>
    <div class="ms-auto d-flex align-items-center">
        <span class="me-3"><?php echo $nama_user; ?></span>
        <img src="<?php echo $foto_tampil; ?>" class="rounded-circle" width="40" height="40">
    </div>
</nav>

<div class="container mt-4 mb-5">
    <div class="card shadow-sm p-4 border-0" style="border-radius: 15px;">
        <h3 class="mb-4 text-dark fw-bold">
            <i class="fas fa-check-circle text-success me-2"></i>
            Konfirmasi Pendaftaran
        </h3>

        <table class="table table-bordered mb-4">
            <tr>
                <th width="30%" class="bg-light">Nama Kursus</th>
                <td><?php echo $k['nama_kursus']; ?></td>
            </tr>
            <tr>
                <th class="bg-light">Jadwal</th>
                <td><?php echo $k['hari'] . ", " . $k['jam']; ?></td>
            </tr>
            <tr>
                <th class="bg-light">Periode</th>
                <td>
                    <?php
                        echo date('d-m-Y', strtotime($k['tanggal_mulai'])) .
                        " s/d " .
                        date('d-m-Y', strtotime($k['tanggal_selesai']));
                    ?>
                </td>
            </tr>
            <tr>
                <th class="bg-light">Biaya</th>
                <td>
                    <strong class="text-danger fs-5">
                        Rp <?php echo number_format($k['harga'], 0, ',', '.'); ?>
                    </strong>
                </td>
            </tr>
        </table>

        <form action="simpan_pendaftaran.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_kursus" value="<?php echo $id_kursus; ?>">

            <div class="alert alert-info border-0 shadow-sm mb-4" style="border-left: 5px solid #0dcaf0 !important;">
                <h6 class="alert-heading fw-bold mb-2">
                    <i class="fas fa-wallet me-2"></i>Instruksi Pembayaran
                </h6>
                <p class="small mb-2">Silakan lakukan transfer sesuai nominal biaya kursus ke salah satu rekening di bawah ini:</p>
                
                <div class="bg-white p-2 rounded border mb-2">
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-1"><i class="fas fa-university text-primary me-2"></i><strong>BCA:</strong> 1234 5678 90 a.n. LKP DIAS</li>
                        <li class="mb-1"><i class="fas fa-university text-primary me-2"></i><strong>BRI:</strong> 0987 6543 21 a.n. LKP DIAS</li>
                        <li class="mb-1"><i class="fas fa-university text-primary me-2"></i><strong>Mandiri:</strong> 1122 3344 55 a.n. LKP DIAS</li>
                        <li><i class="fas fa-mobile-alt text-success me-2"></i><strong>DANA / GoPay / OVO:</strong> 0812 3456 7890 a.n. LKP DIAS</li>
                    </ul>
                </div>
                
                <p class="small text-danger mb-0">
                    <i class="fas fa-exclamation-circle me-1"></i> Pastikan nominal sesuai. Simpan struk/<em>screenshot</em> bukti transfer untuk diunggah di bawah.
                </p>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">
                    <i class="fas fa-upload me-1"></i>
                    Upload Bukti Pembayaran (JPG / PNG)
                </label>
                <input type="file" name="bukti_tf" class="form-control" accept=".jpg,.jpeg,.png" required>
            </div>

            <hr>

            <div class="d-flex justify-content-between mt-3">
                <a href="peserta_dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
                <button type="submit" name="bayar" class="btn btn-success rounded-pill px-4 shadow-sm">
                    <i class="fas fa-paper-plane me-1"></i> Kirim Pendaftaran
                </button>
            </div>
        </form>
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