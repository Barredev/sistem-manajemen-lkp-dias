<?php
session_start();
include '../database/koneksi.php';

// Logika Login
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Verifikasi password yang di-hash
        if (password_verify($password, $row['password'])) {
            $_SESSION['login']   = true;
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['nama']    = $row['nama'];
            $_SESSION['role']    = $row['role'];

            // PERUBAHAN: Semua role langsung diarahkan kembali ke home page
            header("Location: ../index.php");
            exit;
        }
    }
    $error = "Username atau Password salah!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Login - eDIAS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(to right, #f3e7e9, #e3eeff);
    }
    .card {
      min-width: 350px;
      border-radius: 1.5rem;
      border: none;
    }
    .btn-pastel {
      background-color: #91a3fa;
      color: white;
      border-radius: 10px;
      font-weight: 600;
    }
    .btn-pastel:hover {
      background-color: #6f86d6;
      color: white;
    }
    .form-check-label {
      user-select: none;
      cursor: pointer;
      font-size: 0.85rem;
    }
    .form-control {
      border-radius: 10px;
      padding: 10px 15px;
    }

    /* Tombol kembali di dalam kartu */
    .btn-kembali-inline {
      color: #d63384;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9rem;
      transition: all 0.2s;
      display: inline-block;
    }
    .btn-kembali-inline:hover {
      color: #b02a6b;
      transform: translateX(-3px);
    }
  </style>
</head>
<body class="d-flex flex-column justify-content-center align-items-center vh-100">

  <div class="card p-4 shadow-lg">

    <div class="text-center mb-4">
        <h4 class="mb-1">Login ke <span style="color: #d63384; font-weight: bold;">eDIAS</span></h4>
    </div>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger text-center py-2 mb-3" role="alert" style="font-size: 0.85rem; border-radius: 10px;">
        <?= $error ?>
      </div>
    <?php endif; ?>

    <form action="" method="POST" autocomplete="off">
      <div class="mb-3">
        <label for="username" class="form-label small fw-bold">Username</label>
        <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username" required autofocus />
      </div>
      <div class="mb-3">
        <label for="password" class="form-label small fw-bold">Password</label>
        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required />
      </div>
      
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="showPassword" />
          <label class="form-check-label" for="showPassword">
            Lihat Password
          </label>
        </div>
      </div>

      <div class="text-center">
        <button type="submit" name="login" class="btn btn-pastel w-100 py-2 shadow-sm">Login Sekarang</button>
      </div>
    </form>

    <div class="mt-4 text-center">
      <p class="small text-muted mb-0">Belum punya akun?
        <a href="daftar.php" class="text-decoration-none" style="color: #d63384; font-weight: 700;">Daftar Disini</a>
      </p>
    </div>
    <hr>

    <div class="mb-3 text-center">
      <a href="../index.php" class="btn-kembali-inline">
        ← Kembali
      </a>
    </div>
  </div>

  <script>
    // Script untuk toggle tampilkan password
    document.getElementById('showPassword').addEventListener('change', function () {
      const password = document.getElementById('password');
      password.type = this.checked ? 'text' : 'password';
    });
  </script>

</body>
</html>