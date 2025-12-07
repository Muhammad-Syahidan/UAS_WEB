<?php
session_start();

// Set sesi dummy untuk tamu
$_SESSION['iduser'] = 0; // ID 0 menandakan tamu
$_SESSION['user']   = 'Tamu';
$_SESSION['auth']   = 'Guest';
$_SESSION['avatar'] = 'default.png';

// Redirect ke halaman utama
header("Location: ../main.php");
?>