<?php
    session_start();
    include '../database/koneksi.php';

    if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { 
        header("Location: ../auth/login.php"); 
        exit; 
    }

    // Ambil filter dari URL jika ada
    $filter_kursus = isset($_GET['kursus']) ? $_GET['kursus'] : '';

    // Query Dasar
    $query = "SELECT pendaftaran.*, users.nama, users.no_hp, kursus.nama_kursus 
            FROM pendaftaran 
            JOIN users ON pendaftaran.user_id = users.id_user 
            JOIN kursus ON pendaftaran.kursus_id = kursus.id_kursus 
            WHERE pendaftaran.status = 'diterima'";

    if ($filter_kursus != '') {
        $query .= " AND pendaftaran.kursus_id = '$filter_kursus'";
    }

    $query .= " ORDER BY pendaftaran.tgl_daftar DESC";
    $sql = mysqli_query($koneksi, $query);

    // LOGIKA EXPORT EXCEL
    if (isset($_GET['export']) && $_GET['export'] == 'excel') {
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_Pendaftar_Diterima_" . date('Y-m-d') . ".xls");
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendaftar - LKP DIAS</title>
    <link rel="icon" href="../img/logo12.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .report-paper { background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .table-report th { background-color: #e9ecef !important; border: 1px solid #000; font-weight: bold; }
        .table-report td { border: 1px solid #000; }
        
        @media print {
            .no-print { display: none !important; }
            body { background-color: #fff; }
            .report-paper { padding: 0; border: none; box-shadow: none; }
            .container { width: 100% !important; max-width: 100% !important; }
        }
    </style>
</head>
<body>

<div class="container mt-4 no-print">
    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between">
            <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
            
            <form method="GET" class="d-flex align-items-center gap-2 m-0">
                <label class="fw-bold mb-0">Kursus:</label>
                <select name="kursus" class="form-select form-select-sm" style="width: auto;">
                    <option value="">-- Semua Kursus --</option>
                    <?php
                    $krs = mysqli_query($koneksi, "SELECT * FROM kursus");
                    while($k = mysqli_fetch_assoc($krs)) {
                        $selected = ($filter_kursus == $k['id_kursus']) ? 'selected' : '';
                        echo "<option value='".$k['id_kursus']."' $selected>".$k['nama_kursus']."</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            </form>

            <div class="d-flex gap-2">
                <a href="?kursus=<?php echo $filter_kursus; ?>&export=excel" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </a>
                <button onclick="window.print()" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf me-1"></i> Cetak PDF
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="report-paper shadow-sm">
        <div class="text-center mb-4">
            <h2 class="mb-1 fw-bold">LKP DIAS</h2>
            <h4 class="text-uppercase border-bottom pb-3">Laporan Pendaftar Terverifikasi</h4>
            <p class="mt-2 text-muted" style="font-size: 0.9rem;">Dicetak pada: <?php echo date('d/m/Y H:i'); ?></p>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle table-report">
                <thead class="text-center">
                    <tr>
                        <th width="50">No</th>
                        <th>No. Reg</th>
                        <th>Nama Peserta</th>
                        <th>Kontak</th>
                        <th>Nama Kursus</th>
                        <th>Tanggal Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    mysqli_data_seek($sql, 0); // Reset pointer data
                    if(mysqli_num_rows($sql) > 0) :
                        while($d = mysqli_fetch_assoc($sql)) :
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $no++; ?></td>
                        <td class="text-center fw-bold text-primary"><?php echo $d['no_reg']; ?></td>
                        <td><?php echo strtoupper($d['nama']); ?></td>
                        <td class="text-center"><?php echo $d['no_hp']; ?></td>
                        <td><?php echo $d['nama_kursus']; ?></td>
                        <td class="text-center"><?php echo date('d/m/Y', strtotime($d['tgl_daftar'])); ?></td>
                    </tr>
                    <?php 
                        endwhile; 
                    else:
                    ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Data tidak ditemukan untuk filter tersebut.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="row mt-5">
            <div class="col-8"></div>
            <div class="col-4 text-center">
                <p>Cianjur, <?php echo date('d F Y'); ?></p>
                <br><br><br>
                <p class="mb-0"><strong>( Admin LKP DIAS )</strong></p>
                <small class="text-muted">Administrator Panel</small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>