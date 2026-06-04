<?php
    session_start();
    include '../database/koneksi.php';

    // Pengecekan sesi login
    if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { 
        header("Location: ../auth/login.php"); 
        exit; 
    }

    // Pastikan ada ID kursus yang dikirim dari URL
    if (!isset($_GET['id'])) {
        echo "<script>alert('Pilih kursus terlebih dahulu!'); window.location='admin_kursus.php';</script>";
        exit;
    }

    $id_kursus = mysqli_real_escape_string($koneksi, $_GET['id']);

    // 1. Ambil Data Kursus
    $query_kursus = mysqli_query($koneksi, "SELECT * FROM kursus WHERE id_kursus = '$id_kursus'");
    if (mysqli_num_rows($query_kursus) == 0) {
        echo "Data kursus tidak ditemukan."; exit;
    }
    $k = mysqli_fetch_assoc($query_kursus);

    // 2. Logika Perhitungan Waktu Pelaksanaan (Sudah jalan berapa lama)
    $tgl_mulai = strtotime($k['tanggal_mulai']);
    $tgl_selesai = strtotime($k['tanggal_selesai']);
    $sekarang = time();

    // Hitung total hari keseluruhan
    $total_hari = max(1, ($tgl_selesai - $tgl_mulai) / (60 * 60 * 24)); 

    if ($sekarang < $tgl_mulai) {
        $status_jalan = "<span class='badge bg-warning text-dark'>Belum Dimulai</span>";
        $hari_jalan = 0;
        $persen = 0;
    } elseif ($sekarang > $tgl_selesai || $k['status'] == 'selesai') {
        $status_jalan = "<span class='badge bg-success'>Selesai</span>";
        $hari_jalan = $total_hari;
        $persen = 100;
    } else {
        $status_jalan = "<span class='badge bg-primary'>Sedang Berjalan</span>";
        $hari_jalan = floor(($sekarang - $tgl_mulai) / (60 * 60 * 24));
        $persen = min(100, round(($hari_jalan / $total_hari) * 100));
    }

    // 3. PERUBAHAN: Ambil Data Peserta yang "Diterima" DAN "Selesai"
    $query_peserta = mysqli_query($koneksi, "
        SELECT p.id_daftar, p.no_reg, p.tgl_daftar, p.status, p.jam_ditempuh, u.nama, u.no_hp 
        FROM pendaftaran p 
        JOIN users u ON p.user_id = u.id_user 
        WHERE p.kursus_id = '$id_kursus' AND p.status IN ('diterima', 'selesai')
        ORDER BY p.status ASC, u.nama ASC
    ");
    
    // Hitung sisa kuota (hanya menghitung yang masih 'diterima' agar sisa kuota akurat)
    $cek_aktif = mysqli_query($koneksi, "SELECT COUNT(*) as aktif FROM pendaftaran WHERE kursus_id = '$id_kursus' AND status = 'diterima'");
    $dt_aktif = mysqli_fetch_assoc($cek_aktif);
    $sisa_kuota = $k['kuota'] - $dt_aktif['aktif'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kursus - <?php echo $k['nama_kursus']; ?></title>
    <link rel="icon" href="../img/logo12.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .card-header { background-color: #fff; border-bottom: 2px solid #f0f0f0; }
        .progress { height: 20px; border-radius: 10px; }
    </style>
</head>
<body class="py-4">

<div class="container">
    
    <div class="mb-3">
        <a href="admin_kursus.php" class="btn btn-outline-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Kelola Kursus
        </a>
    </div>

    <div class="card shadow-sm mb-4 border-0" style="border-radius: 15px;">
        <div class="card-header py-3">
            <h4 class="mb-0 fw-bold text-primary"><i class="fas fa-book-open me-2"></i> <?php echo $k['nama_kursus']; ?></h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="text-muted border-bottom pb-2">Informasi Dasar</h6>
                    <table class="table table-sm table-borderless">
                        <tr><td width="130">Jadwal</td><td>: <strong><?php echo $k['hari']; ?>, <?php echo substr($k['jam'], 0, 5); ?> WIB</strong></td></tr>
                        <tr><td>Tanggal Mulai</td><td>: <?php echo date('d F Y', strtotime($k['tanggal_mulai'])); ?></td></tr>
                        <tr><td>Tanggal Selesai</td><td>: <?php echo date('d F Y', strtotime($k['tanggal_selesai'])); ?></td></tr>
                        <tr><td>Target Jam</td><td>: <span class="badge bg-dark"><?php echo $k['target_jam']; ?> Jam</span></td></tr>
                        <tr><td>Status Program</td><td>: <?php echo strtoupper($k['status']); ?></td></tr>
                    </table>
                </div>

                <div class="col-md-6 mb-3">
                    <h6 class="text-muted border-bottom pb-2">Statistik Kelas</h6>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Peserta Aktif: <strong><?php echo $dt_aktif['aktif']; ?> / <?php echo $k['kuota']; ?></strong> (Sisa: <?php echo $sisa_kuota; ?>)</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-3 mb-1">
                        <span>Waktu Berjalan: <?php echo $status_jalan; ?></span>
                        <small class="fw-bold"><?php echo $hari_jalan; ?> dari <?php echo $total_hari; ?> Hari</small>
                    </div>
                    <div class="progress shadow-sm">
                        <div class="progress-bar progress-bar-striped progress-bar-animated <?php echo ($persen == 100) ? 'bg-success' : 'bg-primary'; ?>" 
                             role="progressbar" 
                             style="width: <?php echo $persen; ?>%;" 
                             aria-valuenow="<?php echo $persen; ?>" aria-valuemin="0" aria-valuemax="100">
                            <?php echo $persen; ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-users text-success me-2"></i> Daftar Peserta & Histori Absen</h5>
            <button onclick="window.print()" class="btn btn-sm btn-outline-success"><i class="fas fa-print me-1"></i> Cetak Daftar</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="60">No</th>
                            <th>Nama Lengkap & Kontak</th>
                            <th>Status & Progress</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($query_peserta) > 0) {
                            while($p = mysqli_fetch_assoc($query_peserta)) {
                                $jam_tempuh = floor($p['jam_ditempuh'] / 60);
                                $menit_tempuh = $p['jam_ditempuh'] % 60;
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td>
                                    <strong><?php echo strtoupper($p['nama']); ?></strong><br>
                                    <small class="text-muted"><?php echo $p['no_reg']; ?></small> | 
                                    <a href="https://wa.me/<?php echo preg_replace('/^0/', '62', $p['no_hp']); ?>" target="_blank" class="text-decoration-none text-success small">
                                        <i class="fab fa-whatsapp"></i> <?php echo $p['no_hp']; ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if($p['status'] == 'selesai'): ?>
                                        <span class="badge bg-success mb-1">Lulus / Selesai</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary mb-1">Sedang Aktif</span>
                                    <?php endif; ?><br>
                                    <small class="fw-bold text-muted">Total Belajar: <?php echo $jam_tempuh; ?> Jam <?php echo $menit_tempuh; ?> Menit</small>
                                </td>
                                <td class="text-center">
                                    <a href="admin_riwayat_absen.php?id=<?php echo $p['id_daftar']; ?>" class="btn btn-sm btn-outline-info rounded-pill">
                                        <i class="fas fa-search me-1"></i> Cek Absen
                                    </a>
                                </td>
                            </tr>
                        <?php 
                            }
                        } else { 
                        ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada peserta yang tergabung di kelas ini.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>