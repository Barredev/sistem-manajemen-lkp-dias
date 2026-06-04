<?php
session_start();
include '../database/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: peserta_dashboard.php");
    exit;
}

$id_daftar = mysqli_real_escape_string($koneksi, $_GET['id']);
$id_user = $_SESSION['id_user'];

// Cek apakah pendaftaran ini benar milik user yang login
$query_info = mysqli_query($koneksi, "
    SELECT p.*, k.nama_kursus, k.target_jam 
    FROM pendaftaran p 
    JOIN kursus k ON p.kursus_id = k.id_kursus 
    WHERE p.id_daftar = '$id_daftar' AND p.user_id = '$id_user'
");

if (mysqli_num_rows($query_info) == 0) {
    echo "<script>alert('Akses Ditolak!'); window.location='peserta_dashboard.php';</script>";
    exit;
}
$info = mysqli_fetch_assoc($query_info);

// Ambil data riwayat absen dari yang paling baru
$query_absen = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_daftar = '$id_daftar' ORDER BY waktu_in DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Absensi - LKP DIAS</title>
    <link rel="icon" href="../img/logo12.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style> body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; } </style>
</head>
<body class="py-4">
<div class="container" style="max-width: 800px;">
    
    <a href="peserta_dashboard.php" class="btn btn-outline-secondary shadow-sm mb-3">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
    </a>
    
    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-header bg-white py-3 border-0 border-bottom">
            <h5 class="mb-1 fw-bold text-primary"><i class="fas fa-history me-2"></i> Riwayat Kehadiran</h5>
            <small class="text-muted">
                <strong>Kelas:</strong> <?php echo $info['nama_kursus']; ?> | 
                <strong>Total Akumulasi:</strong> 
                <?php 
                    echo floor($info['jam_ditempuh']/60) . " Jam " . ($info['jam_ditempuh']%60) . " Menit"; 
                ?>
            </small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Durasi Belajar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if(mysqli_num_rows($query_absen) > 0) {
                            while($absen = mysqli_fetch_assoc($query_absen)) { 
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><strong><?php echo date('d-m-Y', strtotime($absen['waktu_in'])); ?></strong></td>
                                <td class="text-success fw-bold"><?php echo date('H:i', strtotime($absen['waktu_in'])); ?> WIB</td>
                                <td class="text-danger fw-bold">
                                    <?php 
                                        if($absen['waktu_out']) {
                                            echo date('H:i', strtotime($absen['waktu_out'])) . " WIB";
                                        } else {
                                            echo "<span class='badge bg-warning text-dark'><i class='fas fa-spinner fa-spin'></i> Belum Keluar</span>";
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        if($absen['durasi_menit'] > 0) {
                                            $jam = floor($absen['durasi_menit'] / 60);
                                            $menit = $absen['durasi_menit'] % 60;
                                            echo $jam > 0 ? "<span class='badge bg-info text-dark'>$jam Jam $menit Menit</span>" : "<span class='badge bg-info text-dark'>$menit Menit</span>";
                                        } else {
                                            echo "-";
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php 
                            }
                        } else { 
                        ?>
                            <tr><td colspan="5" class="py-5 text-muted">Belum ada riwayat kehadiran.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>