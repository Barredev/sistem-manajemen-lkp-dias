<?php
session_start();
include '../database/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

/* Ambil data user */
$user = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'")
);

$path_foto = "img/";
if (!empty($user['foto']) && file_exists($path_foto . $user['foto'])) {
    $foto_tampil = $path_foto . $user['foto'];
} else {
    $foto_tampil = "https://ui-avatars.com/api/?name=" . urlencode($user['nama']) . "&background=random";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pendaftaran - LKP DIAS</title>
    <link rel="icon" href="../img/logo12.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body { background: #f8f9fa; }
        #wrapper { display: flex; min-height: 100vh; }
        #sidebar { width: 250px; background: #343a40; color: #fff; }
        #sidebar.active { margin-left: -250px; }
        #sidebar a {
            color: #adb5bd;
            padding: 12px 20px;
            display: block;
            text-decoration: none;
        }
        #sidebar a:hover,
        #sidebar .active a {
            background: #0d6efd;
            color: #fff;
        }
        #content { width: 100%; }
        .navbar {
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,.1);
        }

        @media (max-width: 768px) {
            #sidebar { margin-left: -250px; }
            #sidebar.active { margin-left: 0; }
        }
    </style>
</head>
<body>

<div id="wrapper">

<!-- SIDEBAR -->
<nav id="sidebar">
    <div class="p-3 text-center bg-dark">
                <a href="../index.php" style="text-decoration: none; color: #fff;">
            <h3>LKP DIAS</h3>
        </a>
    </div>
    <ul class="list-unstyled">
        <li><a href="peserta_dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a></li>
        <li><a href="profile.php"><i class="fas fa-user me-2"></i> My Profile</a></li>
        <li class="active"><a href="#"><i class="fas fa-list me-2"></i> Riwayat Pendaftaran</a></li>
        <li>
            <a href="#" class="text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </li>
    </ul>
</nav>

<!-- CONTENT -->
<div id="content">

<!-- NAVBAR -->
<nav class="navbar px-3">
    <button class="btn btn-outline-primary" id="sidebarCollapse">
        <i class="fas fa-bars"></i>
    </button>
    <div class="ms-auto d-flex align-items-center">
        <span class="me-3 d-none d-md-inline"><?php echo $user['nama']; ?></span>
        <img src="<?php echo $foto_tampil; ?>" class="rounded-circle border"
             width="40" height="40" style="object-fit: cover;">
    </div>
</nav>

<!-- MAIN -->
<div class="container-fluid mt-4">
    <div class="card shadow-sm p-4">
        <h4 class="mb-3">Riwayat Pendaftaran Saya</h4>
        <p class="text-muted">Daftar kursus yang pernah Anda daftarkan</p>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>No. Reg</th>
                        <th>Nama Kursus</th>
                        <th>Tgl Daftar</th>
                        <th>Bukti Transfer</th>
                        <th>Status & Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>

<?php
$sql = mysqli_query(
    $koneksi,
    "SELECT pendaftaran.*, kursus.nama_kursus 
     FROM pendaftaran 
     JOIN kursus ON pendaftaran.kursus_id = kursus.id_kursus 
     WHERE pendaftaran.user_id='$id_user'
     ORDER BY id_daftar DESC"
);

if (mysqli_num_rows($sql) == 0) :
?>
    <tr>
        <td colspan="6" class="text-center text-muted">
            Belum ada pendaftaran
        </td>
    </tr>
<?php
else :
while ($d = mysqli_fetch_assoc($sql)) :

    // ==== FIX UTAMA STATUS ====
    $status = strtolower(trim($d['status']));

    // Warna badge
    $warna = "warning";
    if ($status == 'diterima') $warna = "success";
    if ($status == 'ditolak')  $warna = "danger";
?>

<tr>
    <td class="text-center"><?php echo $d['no_reg']; ?></td>
    <td><?php echo $d['nama_kursus']; ?></td>
    <td class="text-center">
        <?php echo date('d-m-Y', strtotime($d['tgl_daftar'])); ?>
    </td>

    <td class="text-center">
        <?php if (!empty($d['bukti_pembayaran'])) : ?>
            <a href="img/<?php echo $d['bukti_pembayaran']; ?>" target="_blank">
                <img src="img/<?php echo $d['bukti_pembayaran']; ?>"
                     width="50" class="img-thumbnail">
            </a>
        <?php else : ?>
            <span class="badge bg-danger">Belum Upload</span>
        <?php endif; ?>
    </td>

    <td>
        <span class="badge bg-<?php echo $warna; ?>">
            <?php echo strtoupper($status); ?>
        </span>

        <?php if (!empty($d['keterangan'])) : ?>
            <div class="small text-muted fst-italic mt-1">
                <strong>Ket:</strong> <?php echo $d['keterangan']; ?>
            </div>
        <?php endif; ?>
    </td>

    <td class="text-center">
        <?php if ($status == 'diterima') : ?>
            <a href="cetak_bukti.php?id=<?php echo $d['id_daftar']; ?>"
               target="_blank"
               class="btn btn-success btn-sm">
                <i class="fas fa-print me-1"></i> Cetak
            </a>

        <?php elseif ($status == 'ditolak') : ?>
            <a href="form_daftar.php?id=<?php echo $d['kursus_id']; ?>"
               class="btn btn-primary btn-sm">
                Daftar Ulang
            </a>

        <?php else : ?>
            <span class="text-muted">Menunggu</span>
        <?php endif; ?>
    </td>
</tr>

<?php endwhile; endif; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<footer class="text-center text-muted mt-4 mb-3">
    <small>&copy; <?php echo date('Y'); ?> LKP DIAS</small>
</footer>

</div>
</div>

<!-- MODAL LOGOUT -->
<div class="modal fade" id="logoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Logout</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-exclamation-circle text-warning fa-3x mb-3"></i>
                <p>Yakin ingin keluar?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
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
