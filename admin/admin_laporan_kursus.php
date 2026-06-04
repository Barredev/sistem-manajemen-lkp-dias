<?php
    session_start();
    include '../database/koneksi.php';

    if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { header("Location: ../auth/login.php"); exit; }

    $filter_status = isset($_GET['status']) ? $_GET['status'] : '';

    $query = "SELECT * FROM kursus";
    if ($filter_status != '') {
        $query .= " WHERE status = '$filter_status'";
    }
    $query .= " ORDER BY id_kursus DESC";
    $sql = mysqli_query($koneksi, $query);

    // LOGIKA EXPORT EXCEL
    if (isset($_GET['export']) && $_GET['export'] == 'excel') {
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Kursus_LKP_DIAS_" . date('Y-m-d') . ".xls");
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Data Kursus</title>
    <link rel="icon" href="../img/logo12.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: sans-serif; background-color: #f8f9fa; padding: 20px; }
        .report-container { background: white; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 8px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; }
        th { background-color: #e9ecef; }
        .no-print { background: #fff; padding: 15px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        
        @media print { 
            .no-print { display: none !important; } 
            body { background-color: white; padding: 0; }
            .report-container { box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>

<div class="report-container">
    <div class="no-print">
        <div>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        
        <form method="GET" class="d-flex align-items-center gap-2 m-0">
            <strong>Filter Status:</strong>
            <select name="status" class="form-select form-select-sm" style="width: auto;">
                <option value="">-- Semua Status --</option>
                <option value="aktif" <?php if($filter_status == 'aktif') echo 'selected'; ?>>Aktif</option>
                <option value="selesai" <?php if($filter_status == 'selesai') echo 'selected'; ?>>Selesai</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        </form>

        <div class="d-flex gap-2">
            <a href="?status=<?php echo $filter_status; ?>&export=excel" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <button onclick="window.print()" class="btn btn-danger btn-sm">
                <i class="fas fa-file-pdf"></i> Cetak / PDF
            </button>
        </div>
    </div>

    <h3 style="text-align: center; font-weight: bold; margin-bottom: 20px;">LAPORAN DATA KURSUS LKP DIAS</h3>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Kursus</th>
                <th>Jadwal</th>
                <th>Masa Pelaksanaan</th>
                <th>Kuota</th>
                <th>Harga</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            // Gunakan mysqli_data_seek agar query bisa dilooping lagi jika sebelumnya sudah dipakai
            mysqli_data_seek($sql, 0); 
            while($k = mysqli_fetch_assoc($sql)) :
            ?>
            <tr>
                <td style="text-align: center;"><?php echo $no++; ?></td>
                <td><?php echo $k['nama_kursus']; ?></td>
                <td><?php echo $k['hari']; ?>, <?php echo $k['jam']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($k['tanggal_mulai'])); ?> s/d <?php echo date('d/m/Y', strtotime($k['tanggal_selesai'])); ?></td>
                <td style="text-align: center;"><?php echo $k['kuota']; ?></td>
                <td>Rp <?php echo number_format($k['harga'], 0, ',', '.'); ?></td>
                <td style="text-align: center;"><?php echo strtoupper($k['status']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br>

    <div class="row mt-5" style="display: flex; justify-content: flex-end;">
        <div style="width: 300px; text-align: center;">
            <p>Cianjur, <?php echo date('d F Y'); ?></p>
            <br><br><br>
            <p style="margin-bottom: 0;"><strong>( Admin LKP DIAS )</strong></p>
            <small style="color: #6c757d;">Administrator Panel</small>
        </div>
    </div>
</div>

</body>
</html>