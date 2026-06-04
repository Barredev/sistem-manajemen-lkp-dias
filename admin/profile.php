<?php
    session_start();
    include '../database/koneksi.php';

    if (!isset($_SESSION['login'])) { header("Location: ../auth/login.php"); exit; }

    $id_user = $_SESSION['id_user'];

    // 1. PROSES UPDATE DATA & FOTO
    if (isset($_POST['update_profil'])) {
        $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
        $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
        $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
        
        $foto = $_FILES['foto']['name'];
        if ($foto != "") {
            $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg');
            $x = explode('.', $foto);
            $ekstensi = strtolower(end($x));
            $file_tmp = $_FILES['foto']['tmp_name'];
            $nama_foto_baru = date('dmYHis') . '-' . $foto;

            if (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
                move_uploaded_file($file_tmp, 'img/' . $nama_foto_baru);
                $query = "UPDATE users SET nama='$nama', no_hp='$no_hp', alamat='$alamat', foto='$nama_foto_baru' WHERE id_user='$id_user'";
                $_SESSION['foto'] = $nama_foto_baru; 
            }
        } else {
            $query = "UPDATE users SET nama='$nama', no_hp='$no_hp', alamat='$alamat' WHERE id_user='$id_user'";
        }

        if (mysqli_query($koneksi, $query)) {
            $_SESSION['nama'] = $nama; 
            echo "<script>alert('Profil berhasil diperbarui!'); window.location='profile.php';</script>";
        }
    }

    // 2. PROSES UBAH PASSWORD
    if (isset($_POST['ubah_password'])) {
        $pass_baru = $_POST['password_baru'];
        $konfirmasi = $_POST['konfirmasi_password'];

        if ($pass_baru === $konfirmasi) {
            $password_fix = password_hash($pass_baru, PASSWORD_DEFAULT);
            mysqli_query($koneksi, "UPDATE users SET password='$password_fix' WHERE id_user='$id_user'");
            echo "<script>alert('Password berhasil diganti!'); window.location='profile.php';</script>";
        } else {
            echo "<script>alert('Konfirmasi password tidak cocok!');</script>";
        }
    }

    // Ambil data user terbaru
    $user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'"));
    
    // Logika Foto untuk Topbar & Sidebar
    $foto_path = ($user['foto'] && file_exists('img/'.$user['foto'])) 
                   ? 'img/'.$user['foto'] 
                   : "https://ui-avatars.com/api/?name=" . urlencode($user['nama']) . "&background=random";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - LKP DIAS</title>
    <link rel="icon" href="../img/logo12.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { overflow-x: hidden; background-color: #f8f9fa; }
        #wrapper { display: flex; width: 100%; align-items: stretch; }
        
        /* Sidebar/Aside Style */
        #sidebar { min-width: 250px; max-width: 250px; min-height: 100vh; background: #343a40; color: #fff; transition: all 0.3s; }
        #sidebar .sidebar-header { padding: 20px; background: #212529; }
        #sidebar ul li a { padding: 12px 20px; display: block; color: #adb5bd; text-decoration: none; }
        #sidebar ul li a:hover { color: #fff; background: #495057; }
        #sidebar ul li.active > a { color: #fff; background: #0d6efd; }
        
        /* Sidebar Toggle Logic */
        #sidebar.active { margin-left: -250px; }
        @media (max-width: 768px) {
            #sidebar { margin-left: -250px; }
            #sidebar.active { margin-left: 0; }
        }

        #content { width: 100%; }
        .navbar { padding: 15px 10px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        
        /* Profile UI */
        .card { border: none; border-radius: 12px; }
        .profile-img-preview { width: 100px; height: 100px; object-fit: cover; border: 3px solid #0d6efd; padding: 3px; }
    </style>
</head>
<body>

<div id="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header"><h3>LKP DIAS</h3></div>
        <ul class="list-unstyled components">
            <hr class="bg-light">
            <p class="ms-3 mb-0" style="font-size: 0.8rem; opacity: 0.7;">PELATIHAN</p>
            <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
            <li><a href="admin_kursus.php"><i class="fas fa-book me-2"></i> Kelola Kursus</a></li>
            <li><a href="admin_verifikasi.php"><i class="fas fa-check-circle me-2"></i> Verifikasi</a></li>
            <hr class="bg-light">
            <p class="ms-3 mb-0" style="font-size: 0.8rem; opacity: 0.7;">LAPORAN</p>
            <li><a href="admin_laporan_kursus.php"><i class="fas fa-file-alt me-2"></i> Laporan Kursus</a></li>
            <li><a href="admin_laporan_pendaftar.php"><i class="fas fa-users me-2"></i> Laporan Pendaftar</a></li>
            <hr class="bg-light">
            <p class="ms-3 mb-0" style="font-size: 0.8rem; opacity: 0.7;">PENGGUNA</p>
            <li class="active"><a href="profile.php"><i class="fas fa-user-circle me-2"></i> Profil Saya</a></li>
            <li>
                <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light mb-4">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-outline-primary">
                    <i class="fas fa-align-left"></i>
                </button>
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3 d-none d-md-inline text-muted">Halo, <strong><?php echo $user['nama']; ?></strong></span>
                    <img src="<?php echo $foto_path; ?>" alt="User" class="rounded-circle" width="35" height="35" style="object-fit: cover;">
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <h2 class="mb-4">Pengaturan Profil</h2>
            
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card shadow-sm p-4">
                        <h5 class="mb-4 border-bottom pb-2">Informasi Pribadi</h5>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <img src="<?php echo $foto_path; ?>" class="rounded-circle profile-img-preview mb-3">
                                <div>
                                    <label class="btn btn-sm btn-outline-primary shadow-sm">
                                        <i class="fas fa-camera me-1"></i> Ganti Foto
                                        <input type="file" name="foto" hidden onchange="this.form.submit()">
                                    </label>
                                    <div class="small text-muted mt-2">* Gunakan format JPG/PNG</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control" value="<?php echo $user['nama']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">No. HP / WhatsApp</label>
                                    <input type="text" name="no_hp" class="form-control" value="<?php echo $user['no_hp']; ?>">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Alamat Domisili</label>
                                <textarea name="alamat" class="form-control" rows="3"><?php echo $user['alamat']; ?></textarea>
                            </div>
                            <button type="submit" name="update_profil" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card shadow-sm p-4 h-100">
                        <h5 class="mb-4 border-bottom pb-2 text-danger">Keamanan Akun</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="password_baru" class="form-control" placeholder="Masukkan password baru" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="konfirmasi_password" class="form-control" placeholder="Ulangi password" required>
                            </div>
                            <button type="submit" name="ubah_password" class="btn btn-outline-danger w-100">
                                <i class="fas fa-key me-2"></i> Ganti Password
                            </button>
                        </form>
                        <div class="mt-4 p-3 bg-light rounded small">
                            <strong>Tips Keamanan:</strong> Pastikan password Anda sulit ditebak dan jangan berikan akses login Anda kepada siapapun.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="text-center text-muted py-4 mt-5">
            <small>&copy; <?php echo date("Y"); ?> LKP DIAS - All Rights Reserved</small>
        </footer>
    </div>
</div>



<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Konfirmasi Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
    // JS untuk Toggle Sidebar (Aside)
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