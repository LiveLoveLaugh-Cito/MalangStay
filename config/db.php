<?php
$host    = 'localhost';
$db      = 'db_malangstay';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset($charset);

if ($conn->connect_error) {
    die('<p style="color:red;font-family:sans-serif;padding:2rem;">
        Koneksi database gagal: ' . $conn->connect_error . '<br>
        Pastikan MySQL sudah berjalan dan database <strong>' . $db . '</strong> sudah dibuat.
    </p>');
}
?>
