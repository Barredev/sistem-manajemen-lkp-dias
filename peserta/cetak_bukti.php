<?php
session_start();
include '../database/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_daftar = $_GET['id'];
$id_user_login = $_SESSION['id_user'];

/* Ambil data pendaftaran */
$query = "
    SELECT pendaftaran.*, users.nama, users.no_hp, users.alamat,
           kursus.nama_kursus, kursus.hari, kursus.jam
    FROM pendaftaran
    JOIN users ON pendaftaran.user_id = users.id_user
    JOIN kursus ON pendaftaran.kursus_id = kursus.id_kursus
    WHERE pendaftaran.id_daftar = '$id_daftar'
";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

/* Keamanan */
if ($_SESSION['role'] !== 'peserta' && $data['user_id'] != $id_user_login) {
    echo "Akses ditolak!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Bukti Pendaftaran - <?php echo $data['no_reg']; ?></title>
<link rel="icon" href="../img/logo12.png" type="image/png">
<style>
    body {
        font-family: "Times New Roman", serif;
        background: #f4f4f4;
    }

    /* PAKSA PORTRAIT */
    @page {
        size: A4 portrait;
        margin: 20mm;
    }

    .no-print {
        text-align: center;
        margin: 20px;
    }

    .btn {
        padding: 8px 16px;
        border: none;
        background: #0d6efd;
        color: #fff;
        cursor: pointer;
        text-decoration: none;
        border-radius: 4px;
        margin: 0 5px;
    }

    .btn-secondary {
        background: #6c757d;
    }

    .kartu {
        width: 100%;
        max-width: 190mm;      /* AMAN untuk A4 */
        margin: 0 auto;
        background: #fff;
        padding: 20px;
        border: 1px solid #000;
        box-sizing: border-box; /* PENTING */
    }


    .header {
        text-align: center;
        border-bottom: 3px double #000;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .header h2 {
        margin: 0;
        letter-spacing: 1px;
    }

    .header p {
        margin: 5px 0 0;
        font-size: 14px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    table td {
        padding: 6px 4px;
        vertical-align: top;
    }

    .label {
        width: 35%;
        font-weight: bold;
    }

    .status {
        display: inline-block;
        padding: 5px 14px;
        border: 2px solid green;
        color: green;
        font-weight: bold;
        border-radius: 4px;
        text-transform: uppercase;
        font-size: 14px;
    }

    .footer {
        margin-top: 40px;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }

    .ttd {
        text-align: right;
    }

    .cap {
        width: 110px;
        opacity: 0.9;
        margin-top: -40px;
    }

    @media print {
        body {
            background: #fff;
        }
        .no-print {
            display: none;
        }
    }
</style>
</head>

<body>

<div class="no-print">
    <button class="btn" onclick="window.print()">🖨 Cetak</button>
    <a href="pendaftaran_saya.php" class="btn btn-secondary">⬅ Kembali</a>
</div>

<div class="kartu">

    <!-- HEADER -->
    <div class="header">
        <h2>LKP DIAS</h2>
        <p><strong>BUKTI PENDAFTARAN KURSUS MENJAHIT</strong></p>
    </div>

    <!-- DATA -->
    <table>
        <tr>
            <td class="label">No. Registrasi</td>
            <td>: <strong><?php echo $data['no_reg']; ?></strong></td>
        </tr>
        <tr>
            <td class="label">Tanggal Daftar</td>
            <td>: <?php echo date('d-m-Y', strtotime($data['tgl_daftar'])); ?></td>
        </tr>
        <tr>
            <td class="label">Nama Lengkap</td>
            <td>: <?php echo $data['nama']; ?></td>
        </tr>
        <tr>
            <td class="label">No. HP</td>
            <td>: <?php echo $data['no_hp']; ?></td>
        </tr>
        <tr>
            <td class="label">Alamat</td>
            <td>: <?php echo $data['alamat']; ?></td>
        </tr>
        <tr>
            <td class="label">Nama Kursus</td>
            <td>: <?php echo $data['nama_kursus']; ?></td>
        </tr>
        <tr>
            <td class="label">Jadwal</td>
            <td>: <?php echo $data['hari'] . ', ' . $data['jam']; ?></td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td>: <span class="status"><?php echo $data['status']; ?></span></td>
        </tr>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        <div>
            <p>
                Dicetak pada:<br>
                <?php echo date('d-m-Y H:i'); ?>
            </p>
        </div>

        <div class="ttd">
            <p>
                Hormat Kami,<br>
                <strong>LKP DIAS</strong>
            </p>
            <br>
            <img src="../img/cap1.png" class="cap" alt="Cap LKP DIAS">
        </div>
    </div>

</div>

</body>
</html>
