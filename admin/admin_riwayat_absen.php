<?php
session_start();
include '../database/koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../auth/login.php"); 
    exit; 
}

if (!isset($_GET['id'])) {
    header("Location: admin_kursus.php");
    exit;
}

$id_daftar = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil info peserta dan kursusnya
$query_info = mysqli_query($koneksi, "
    SELECT p.*, u.nama, u.no_hp, k.nama_kursus, k.target_jam, k.id_kursus 
    FROM pendaftaran p 
    JOIN users u ON p.user_id = u.id_user
    JOIN kursus k ON p.kursus_id = k.id_kursus 
    WHERE p.id_daftar = '$id_daftar'
");

if (mysqli_num_rows($query_info) == 0) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='admin_kursus.php';</script>";
    exit;
}
$info = mysqli_fetch_assoc($query_info);

// Ambil histori absennya
$query_absen = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_daftar = '$id_daftar' ORDER BY waktu_in DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Histori Absen - <?php echo $info['nama']; ?></title>
    <link rel="icon" href="../img/logo12.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style> body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; } </style>
</head>
<body class="py-4">
<div class="container" style="max-width: 900px;">
    
    <a href="admin_detail_kursus.php?id=<?php echo $info['id_kursus']; ?>" class="btn btn-outline-secondary shadow-sm mb-3">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Detail Kelas
    </a>
    
    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-header bg-white py-4 border-0 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 fw-bold text-primary"><i class="fas fa-user-clock me-2"></i> Laporan Kehadiran Peserta</h4>
                <div class="mt-2 text-muted">
                    <p class="mb-0"><strong>Nama Peserta:</strong> <?php echo strtoupper($info['nama']); ?> (<?php echo $info['no_reg']; ?>)</p>
                    <p class="mb-0"><strong>Kelas / Target:</strong> <?php echo $info['nama_kursus']; ?> / <?php echo $info['target_jam']; ?> Jam</p>
                </div>
            </div>
            <div class="text-end">
                <span class="badge <?php echo ($info['status'] == 'selesai') ? 'bg-success' : 'bg-primary'; ?> fs-6 mb-2">
                    Status: <?php echo strtoupper($info['status']); ?>
                </span><br>
                <strong class="fs-5 text-dark">
                    Total: <?php echo floor($info['jam_ditempuh']/60); ?> Jam <?php echo ($info['jam_ditempuh']%60); ?> Menit
                </strong>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-dark">
                        <tr>
                            <th width="60">No</th>
                            <th>Tanggal Absen</th>
                            <th>Waktu Masuk</th>
                            <th>Waktu Keluar</th>
                            <th>Durasi Tercatat</th>
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
                                <td><strong><?php echo date('d M Y', strtotime($absen['waktu_in'])); ?></strong></td>
                                <td class="text-success fw-bold"><?php echo date('H:i', strtotime($absen['waktu_in'])); ?> WIB</td>
                                <td class="text-danger fw-bold">
                                    <?php 
                                        if($absen['waktu_out']) {
                                            echo date('H:i', strtotime($absen['waktu_out'])) . " WIB";
                                        } else {
                                            echo "<span class='badge bg-warning text-dark'><i class='fas fa-sync fa-spin me-1'></i> Sedang Dikelas</span>";
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
                            <tr><td colspan="5" class="py-5 text-muted">Belum ada aktivitas absensi untuk peserta ini.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>