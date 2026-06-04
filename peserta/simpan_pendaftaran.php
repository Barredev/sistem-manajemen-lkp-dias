<?php
    session_start();
    include '../database/koneksi.php';

    if (isset($_POST['bayar'])) {
        $id_user = $_SESSION['id_user'];
        $id_kursus = $_POST['id_kursus'];

        // 1. Cek apakah peserta sudah pernah daftar di kursus ini
        // Penyesuaian: Tambahkan status != 'ditolak' agar peserta bisa daftar ulang jika sebelumnya ditolak
        $cek = mysqli_query($koneksi, "SELECT * FROM pendaftaran 
                                       WHERE user_id = '$id_user' 
                                       AND kursus_id = '$id_kursus' 
                                       AND status != 'ditolak'");
        
        if (mysqli_num_rows($cek) > 0) {
            echo "<script>alert('Anda sudah memiliki pendaftaran yang sedang diproses atau sudah diterima untuk kursus ini.'); window.location='peserta_dashboard.php';</script>";
            exit;
        }

        // 2. Logika Nomor Registrasi Berurutan (Otomatis)
        $tgl = date('Ymd');
        // Cari nomor terakhir di hari ini
        $query_cek = mysqli_query($koneksi, "SELECT MAX(no_reg) as max_reg FROM pendaftaran WHERE no_reg LIKE 'REG-$tgl-%'");
        $data = mysqli_fetch_assoc($query_cek);
        $last_reg = $data['max_reg'];

        // Ambil 3 angka terakhir dan tambahkan 1
        $no_urut = (int) substr($last_reg, -3);
        $no_urut++;

        // Format hasil akhirnya: REG-YYYYMMDD-001
        $no_reg = "REG-" . $tgl . "-" . sprintf("%03s", $no_urut);

        // 3. Proses Upload Bukti Pembayaran
        $bukti = $_FILES['bukti_tf']['name'];
        $tmp = $_FILES['bukti_tf']['tmp_name'];
        
        // Memberi nama unik pada file agar tidak bentrok
        $buktibaru = date('dmYHis') . "-" . $bukti;
        $path = "img/" . $buktibaru;

        if (move_uploaded_file($tmp, $path)) {
            // 4. Simpan ke Database
            $query = "INSERT INTO pendaftaran (no_reg, user_id, kursus_id, status, bukti_pembayaran) 
                      VALUES ('$no_reg', '$id_user', '$id_kursus', 'pending', '$buktibaru')";
            
            if (mysqli_query($koneksi, $query)) {
                echo "<script>alert('Pendaftaran & Bukti TF dikirim! Silakan tunggu verifikasi admin.'); window.location='pendaftaran_saya.php';</script>";
            } else {
                echo "Error: " . mysqli_error($koneksi);
            }
        } else {
            echo "<script>alert('Gagal mengupload gambar. Pastikan folder img tersedia dan memiliki izin tulis.'); window.history.back();</script>";
        }
    }
?>