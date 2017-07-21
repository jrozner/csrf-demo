<?php
session_start();
$password = 'password';

function generateToken($password) {
  $nonce = uniqid();
  $expires = time() + 3600;
  $data = bin2hex($nonce)."-".session_id()."-".$expires;
  $hash = hash_hmac('sha256', $data, $password);

  return $data."-".$hash;
}

function isLoggedIn() {
  if ($_SESSION['logged_in'] === true) {
    return true;
  }

  return false;
}

function resetAccount() {
  $_SESSION['balance'] = 500000;
  $_SESSION['transfers'] = [];
}
?>
