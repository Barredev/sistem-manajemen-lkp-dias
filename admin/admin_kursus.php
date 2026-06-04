<?php
    session_start();
    include '../database/koneksi.php';

    if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
        header("Location:../auth/login.php");
        exit;
    }

    // LOGIKA FOTO PROFIL
    $path_folder = "img/"; 
    $foto_session = isset($_SESSION['foto']) ? $_SESSION['foto'] : '';
    $foto_tampil = (!empty($foto_session) && file_exists($path_folder . $foto_session)) 
                   ? $path_folder . $foto_session 
                   : "https://ui-avatars.com/api/?name=" . urlencode($_SESSION['nama']) . "&background=random";

    // LOGIKA TAMBAH DATA KURSUS
    if (isset($_POST['tambah_kursus'])) {
        $nama    = mysqli_real_escape_string($koneksi, $_POST['nama_kursus']);
        $hari    = mysqli_real_escape_string($koneksi, $_POST['hari']);
        $jam     = $_POST['jam'];
        $tgl_m   = $_POST['tgl_mulai'];
        $tgl_s   = $_POST['tgl_selesai'];
        $kuota   = $_POST['kuota'];
        $harga   = $_POST['harga'];
        // PERUBAHAN 1: Menangkap input target jam
        $target  = $_POST['target_jam']; 

        // PERUBAHAN 2: Memasukkan target_jam ke query INSERT
        $query = "INSERT INTO kursus (nama_kursus, hari, jam, tanggal_mulai, tanggal_selesai, kuota, harga, target_jam, status) 
                  VALUES ('$nama', '$hari', '$jam', '$tgl_m', '$tgl_s', '$kuota', '$harga', '$target', 'aktif')";
        
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Kursus berhasil ditambahkan!'); window.location='admin_kursus.php';</script>";
        } else {
            echo "Gagal: " . mysqli_error($koneksi);
        }
    }


    // =======================
    // LOGIKA EDIT DATA KURSUS
    // =======================
    if (isset($_POST['edit_kursus'])) {
        $id      = $_POST['id_kursus'];
        $nama    = mysqli_real_escape_string($koneksi, $_POST['nama_kursus']);
        $hari    = mysqli_real_escape_string($koneksi, $_POST['hari']);
        $jam     = $_POST['jam'];
        $tgl_m   = $_POST['tgl_mulai'];
        $tgl_s   = $_POST['tgl_selesai'];
        $kuota   = $_POST['kuota'];
        $harga   = $_POST['harga'];
        $status  = $_POST['status'];
        // PERUBAHAN 3: Menangkap input target jam untuk edit
        $target  = $_POST['target_jam']; 

        // PERUBAHAN 4: Menambahkan target_jam pada query UPDATE
        $query = "UPDATE kursus SET
                    nama_kursus='$nama',
                    hari='$hari',
                    jam='$jam',
                    tanggal_mulai='$tgl_m',
                    tanggal_selesai='$tgl_s',
                    kuota='$kuota',
                    harga='$harga',
                    target_jam='$target',
                    status='$status'
                WHERE id_kursus='$id'";

        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Data kursus berhasil diperbarui'); window.location='admin_kursus.php';</script>";
        } else {
            echo "Gagal update: " . mysqli_error($koneksi);
        }
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kursus - LKP DIAS</title>
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
        .card { border: none; border-radius: 10px; }
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
            <li class="active"><a href="admin_kursus.php"><i class="fas fa-book me-2"></i> Kelola Kursus</a></li>
            <li><a href="admin_verifikasi.php"><i class="fas fa-check-circle me-2"></i> Verifikasi</a></li>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Kelola Kursus</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="fas fa-plus me-2"></i> Tambah Kursus
                </button>
            </div>

            <div class="card shadow-sm p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama Kursus</th>
                                <th>Jadwal</th>
                                <th>Periode & Target</th>
                                <th>Kuota</th>
                                <th>Harga</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $res = mysqli_query($koneksi, "SELECT * FROM kursus ORDER BY id_kursus DESC");
                            while($row = mysqli_fetch_assoc($res)) :
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><strong><?php echo $row['nama_kursus']; ?></strong></td>
                                <td><small><?php echo $row['hari']; ?><br><?php echo $row['jam']; ?></small></td>
                                <td>
                                    <small><?php echo date('d/m/Y', strtotime($row['tanggal_mulai'])); ?> - <?php echo date('d/m/Y', strtotime($row['tanggal_selesai'])); ?></small><br>
                                    <span class="badge bg-secondary">Target: <?php echo $row['target_jam']; ?> Jam</span>
                                </td>
                                <td><?php echo $row['kuota']; ?></td>
                                <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                <td><span class="badge bg-success text-capitalize"><?php echo $row['status']; ?></span></td>
                                <td>

                                    <a href="admin_detail_kursus.php?id=<?php echo $row['id_kursus']; ?>" class="btn btn-sm btn-info text-white" title="Detail Kelas">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_kursus']; ?>" title="Edit Kelas">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <a href="admin_kursus_hapus.php?id=<?= $row['id_kursus']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus kursus ini?')" title="Hapus Kelas">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalEdit<?= $row['id_kursus']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Kursus</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form method="POST">
                                        <input type="hidden" name="id_kursus" value="<?= $row['id_kursus']; ?>">

                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Nama Kursus</label>
                                                <input type="text" name="nama_kursus" class="form-control" value="<?= $row['nama_kursus']; ?>" required>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col">
                                                    <label class="form-label">Hari</label>
                                                    <input type="text" name="hari" class="form-control" value="<?= $row['hari']; ?>" required>
                                                </div>
                                                <div class="col">
                                                    <label class="form-label">Jam</label>
                                                    <input type="time" name="jam" class="form-control" value="<?= substr($row['jam'],0,5); ?>" required>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col">
                                                    <label class="form-label">Tgl Mulai</label>
                                                    <input type="date" name="tgl_mulai" class="form-control" value="<?= $row['tanggal_mulai']; ?>" required>
                                                </div>
                                                <div class="col">
                                                    <label class="form-label">Tgl Selesai</label>
                                                    <input type="date" name="tgl_selesai" class="form-control" value="<?= $row['tanggal_selesai']; ?>" required>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col">
                                                    <label class="form-label">Kuota</label>
                                                    <input type="number" name="kuota" class="form-control" value="<?= $row['kuota']; ?>" required>
                                                </div>
                                                <div class="col">
                                                    <label class="form-label">Harga</label>
                                                    <input type="number" name="harga" class="form-control" value="<?= $row['harga']; ?>" required>
                                                </div>
                                                <div class="col">
                                                    <label class="form-label">Target Jam</label>
                                                    <input type="number" name="target_jam" class="form-control" value="<?= $row['target_jam']; ?>" required>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="aktif" <?= ($row['status']=='aktif')?'selected':''; ?>>Aktif</option>
                                                    <option value="nonaktif" <?= ($row['status']=='nonaktif')?'selected':''; ?>>Nonaktif</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="edit_kursus" class="btn btn-warning">Simpan Perubahan</button>
                                        </div>
                                    </form>

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

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kursus Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kursus</label>
                        <input type="text" name="nama_kursus" class="form-control" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Hari</label>
                            <input type="text" name="hari" class="form-control" placeholder="Senin & Rabu" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Jam</label>
                            <input type="time" name="jam" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Tgl Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Tgl Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Kuota</label>
                            <input type="number" name="kuota" class="form-control" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Harga (Rp)</label>
                            <input type="number" name="harga" class="form-control" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Target Jam</label>
                            <input type="number" name="target_jam" class="form-control" placeholder="Cth: 40" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_kursus" class="btn btn-primary">Simpan Kursus</button>
                </div>
            </form>
        </div>
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