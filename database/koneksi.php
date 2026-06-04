<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "lkp_dias";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

/* =======================================================
   FITUR AUTO-UPDATE STATUS KURSUS
   Otomatis mengubah status jadi 'selesai' jika tanggal 
   hari ini sudah melewati tanggal_selesai kursus.
======================================================= */
mysqli_query($koneksi, "UPDATE kursus SET status = 'selesai' WHERE tanggal_selesai < CURDATE() AND status = 'aktif'");

?>