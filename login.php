<?php
require_once('common.php');
if ($_POST['username'] === 'test' && $_POST['password'] === 'test') {
  $_SESSION['logged_in'] = true;
  $_SESSION['username'] = $_POST['username'];
  resetAccount();
  //header('Set-Cookie: PHPSESSID=nope; path=/; SameSite=Lax;');
  header('Location: /account.php');
} else {
  header('Location: /login.html');
}
?>
