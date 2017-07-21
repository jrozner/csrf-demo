<?php
require_once('common.php');
if (!isLoggedIn()) {
  header('Location: /login.html');
}

resetAccount();

echo json_encode(array("balance" => $_SESSION['balance'], "transfers" => $_SESSION['transfers']));
?>
