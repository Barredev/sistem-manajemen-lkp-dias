<?php
session_start();
include '../database/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

/* ======================
   PROSES UPDATE PROFIL
====================== */
if (isset($_POST['update_profil'])) {
    $nama   = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $no_hp  = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    $foto = $_FILES['foto']['name'];

    if (!empty($foto)) {
        $ekstensi_boleh = ['png', 'jpg', 'jpeg'];
        $x = explode('.', $foto);
        $ekstensi = strtolower(end($x));
        $tmp = $_FILES['foto']['tmp_name'];
        $nama_foto_baru = date('YmdHis') . '_' . $foto;

        if (in_array($ekstensi, $ekstensi_boleh)) {
            move_uploaded_file($tmp, 'img/' . $nama_foto_baru);

            mysqli_query($koneksi,
                "UPDATE users SET 
                 nama='$nama', no_hp='$no_hp', alamat='$alamat', foto='$nama_foto_baru'
                 WHERE id_user='$id_user'"
            );
        }
    } else {
        mysqli_query($koneksi,
            "UPDATE users SET 
             nama='$nama', no_hp='$no_hp', alamat='$alamat'
             WHERE id_user='$id_user'"
        );
    }

    echo "<script>alert('Profil berhasil diperbarui'); window.location='profile.php';</script>";
}

/* ======================
   PROSES UBAH PASSWORD
====================== */
if (isset($_POST['ubah_password'])) {
    $pass_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi_password'];

    if ($pass_baru === $konfirmasi) {
        $password_hash = password_hash($pass_baru, PASSWORD_DEFAULT);
        mysqli_query($koneksi,
            "UPDATE users SET password='$password_hash' WHERE id_user='$id_user'"
        );
        echo "<script>alert('Password berhasil diganti'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('Konfirmasi password tidak cocok');</script>";
    }
}

/* ======================
   DATA USER
====================== */
$user = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'")
);

/* ======================
   FOTO PROFIL
====================== */
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
    <title>Profil Peserta - LKP DIAS</title>
    <link rel="icon" href="../img/logo12.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body { background: #f8f9fa; }
        #wrapper { display: flex; min-height: 100vh; }
        #sidebar { width: 250px; background: #343a40; color: #fff; }
        #sidebar.active { margin-left: -250px; }
        #sidebar a { color: #adb5bd; padding: 12px 20px; display: block; text-decoration: none; }
        #sidebar a:hover, #sidebar .active a { background: #0d6efd; color: #fff; }
        #content { width: 100%; }
        .navbar { background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,.1); }

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
            <li class="active"><a href="profile.php"><i class="fas fa-user me-2"></i> My Profile</a></li>
            <li><a href="pendaftaran_saya.php"><i class="fas fa-list me-2"></i> Riwayat Pendaftaran</a></li>
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
                <span class="me-3"><?php echo $user['nama']; ?></span>
                <img src="<?php echo $foto_tampil; ?>" class="rounded-circle border" width="40" height="40">
            </div>
        </nav>

        <!-- MAIN -->
        <div class="container-fluid mt-4">
            <div class="card shadow-sm p-4">
                <h4 class="mb-4">Profil Pengguna</h4>

                <div class="row">
                    <!-- DATA DIRI -->
                    <div class="col-md-6">
                        <h5>Data Diri</h5>
                        <form method="POST" enctype="multipart/form-data">
                            <label class="form-label">Pas Foto (Sertifikat)</label><br>
                            <img src="<?php echo $foto_tampil; ?>" width="120" class="mb-2 border"><br>
                            <input type="file" name="foto" class="form-control mb-3">

                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" value="<?php echo $user['nama']; ?>" class="form-control mb-3" required>

                            <label class="form-label">No HP</label>
                            <input type="text" name="no_hp" value="<?php echo $user['no_hp']; ?>" class="form-control mb-3">

                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control mb-3"><?php echo $user['alamat']; ?></textarea>

                            <button class="btn btn-primary" name="update_profil">
                                <i class="fas fa-save me-1"></i> Simpan Profil
                            </button>
                        </form>
                    </div>

                    <!-- PASSWORD -->
                    <div class="col-md-6">
                        <h5>Keamanan Akun</h5>
                        <form method="POST">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="password_baru" class="form-control mb-3" required>

                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="konfirmasi_password" class="form-control mb-3" required>

                            <button class="btn btn-warning" name="ubah_password">
                                <i class="fas fa-key me-1"></i> Ganti Password
                            </button>
                        </form>
                    </div>
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
                <p>Apakah Anda yakin ingin logout?</p>
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
document.getElementById('sidebarCollapse').onclick = function () {
    document.getElementById('sidebar').classList.toggle('active');
};
</script>

</body>
</html>
