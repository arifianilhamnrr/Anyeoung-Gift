<?php
// Halaman registrasi user disatukan dengan halaman login (lihat tab "view-register"
// pada users/pages/login.php). Jika user membuka ?page=register secara langsung,
// arahkan ke halaman login dengan tab register otomatis aktif.

$_SESSION['active_auth_view'] = 'register';

if (!headers_sent()) {
    header('Location: index.php?page=login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0; url=index.php?page=login">
    <title>Daftar - Anyeong Gift</title>
</head>

<body>
    <script>window.location.replace('index.php?page=login');</script>
    <p>Mengalihkan ke halaman pendaftaran...
        <a href="index.php?page=login">Klik di sini</a> jika tidak otomatis dialihkan.
    </p>
</body>

</html>
