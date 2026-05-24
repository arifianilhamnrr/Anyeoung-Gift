<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: users/index.php?page=login");
} else {
    header("Location: users/index.php?page=home");
}
exit();
?>
