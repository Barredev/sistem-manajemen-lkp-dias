<?php
    session_start();
    include '../database/koneksi.php';

    if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
        exit;
    }

    $id = $_GET['id'];
    $query = "DELETE FROM kursus WHERE id_kursus = '$id'";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data berhasil dihapus'); window.location='admin_kursus.php';</script>";
    } else {
        echo "Gagal menghapus: " . mysqli_error($koneksi);
    }
?>