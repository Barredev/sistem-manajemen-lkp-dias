<?php
    session_start();
    include '../database/koneksi.php';

    if (isset($_POST['daftar'])) {
        $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
        $username = mysqli_real_escape_string($koneksi, $_POST['username']);
        $password_raw = $_POST['password']; // Ambil password asli untuk divalidasi dulu
        $no_hp    = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
        $usia     = mysqli_real_escape_string($koneksi, $_POST['usia']);
        $jk       = $_POST['jenis_kelamin'];
        $alamat   = mysqli_real_escape_string($koneksi, $_POST['alamat']);
        $role     = 'peserta';

        // ==========================================
        // GERBANG KEAMANAN (BACKEND VALIDATION)
        // ==========================================
        if (strlen($password_raw) < 6) {
            // Validasi BVA: Password di bawah 6 karakter ditolak
            $error_msg = "Gagal: Password minimal harus 6 karakter!";
        } elseif (!preg_match('/^[0-9]{10,13}$/', $no_hp)) {
            // Validasi EP & BVA: No HP wajib angka dan panjangnya 10-13 digit
            $error_msg = "Gagal: Nomor HP tidak valid! Harus berupa angka (10-13 digit).";
        } else {
            // Jika semua validasi lolos, baru password dienkripsi dan data disimpan
            $password_hash = password_hash($password_raw, PASSWORD_DEFAULT); 
            
            $query = "INSERT INTO users (nama, username, password, no_hp, usia, jenis_kelamin, alamat, role) 
                      VALUES ('$nama', '$username', '$password_hash', '$no_hp', '$usia', '$jk', '$alamat', '$role')";

            if (mysqli_query($koneksi, $query)) {
                echo "<script>alert('Pendaftaran Berhasil! Silahkan Login'); window.location='login.php';</script>";
                exit;
            } else {
                $error_msg = "Terjadi kesalahan sistem: " . mysqli_error($koneksi);
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Daftar Peserta - eDIAS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(to right, #f3e7e9, #e3eeff);
      min-height: 100vh;
    }
    .card {
      width: 100%;
      max-width: 600px;
      border-radius: 1.5rem;
      border: none;
    }
    .btn-pastel {
      background-color: #91a3fa;
      color: white;
      border-radius: 10px;
      font-weight: 600;
      transition: all 0.3s;
    }
    .btn-pastel:hover {
      background-color: #6f86d6;
      color: white;
    }
    .form-control, .form-select {
      border-radius: 10px;
      padding: 8px 15px;
    }
  </style>
</head>
<body class="d-flex justify-content-center align-items-center p-3">

  <div class="card p-4 shadow-lg my-4">

    <div class="text-center mb-4">
        <h4 class="mb-1">Pendaftaran <span style="color: #d63384; font-weight: bold;">eDIAS</span></h4>
        <p class="text-muted small">Lengkapi data diri Anda untuk bergabung</p>
    </div>

    <?php if (isset($error_msg)): ?>
      <div class="alert alert-danger py-2 fw-bold text-center" role="alert" style="font-size: 0.85rem; border-radius: 10px;">
        <i class="fas fa-exclamation-triangle me-1"></i> <?= $error_msg ?>
      </div>
    <?php endif; ?>

    <form action="" method="POST" autocomplete="off">
      <div class="row">
        <div class="col-md-12 mb-3">
          <label class="form-label small fw-bold">Nama Lengkap</label>
          <input type="text" name="nama" class="form-control" placeholder="Nama sesuai identitas" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label small fw-bold">Username</label>
          <input type="text" name="username" class="form-control" placeholder="Tanpa spasi" pattern="^\S+$" title="Username tidak boleh menggunakan spasi" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label small fw-bold">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" minlength="6" required>
        </div>

        <div class="col-md-8 mb-3">
          <label class="form-label small fw-bold">No. WhatsApp</label>
          <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 08123456789" pattern="[0-9]{10,13}" minlength="10" maxlength="13" title="Masukkan nomor HP valid (10-13 digit angka)" required>
        </div>

        <div class="col-md-4 mb-3">
          <label class="form-label small fw-bold">Usia</label>
          <input type="number" name="usia" class="form-control" placeholder="Thn" min="5" max="100" required>
        </div>

        <div class="col-md-12 mb-3">
          <label class="form-label small fw-bold">Jenis Kelamin</label>
          <select name="jenis_kelamin" class="form-select" required>
            <option value="Laki-laki">Laki-laki</option>
            <option value="Perempuan">Perempuan</option>
          </select>
        </div>

        <div class="col-md-12 mb-4">
          <label class="form-label small fw-bold">Alamat Lengkap</label>
          <textarea name="alamat" class="form-control" rows="2" placeholder="Masukkan alamat lengkap..." required></textarea>
        </div>
      </div>

      <div class="text-center">
        <button type="submit" name="daftar" class="btn btn-pastel w-100 py-2 shadow-sm">Daftar Sekarang</button>
      </div>
    </form>

    <div class="mt-4 text-center">
      <p class="small text-muted mb-0">Sudah punya akun? 
        <a href="login.php" class="text-decoration-none" style="color: #d63384; font-weight: 700;">Login di sini</a>
      </p>
    </div>
  </div>

</body>
</html>