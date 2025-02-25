<?php
session_start();
session_destroy(); // Hapus sesi
header("Location: ../register-login/login.php"); // Arahkan ke halaman login
exit();
?>