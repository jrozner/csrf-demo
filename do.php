<?php
require_once('common.php');

$location = 'form';

$token = '';

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
  $location = 'xhr';
}

if ($location === 'xhr') {
  if (!isset($_SERVER['HTTP_X_CSRF_HEADER'])) {
    http_response_code(403);
    die('No csrf header');
  }
  $token = $_SERVER['HTTP_X_CSRF_HEADER'];
} else {
  if (!isset($_POST['csrf_token'])) {
    http_response_code(403);
    die('No csrf body');
  }
  $token = $_POST['csrf_token'];
}

$parts = explode('-', $token);

if (count($parts) !== 4) {
  http_response_code(403);
  die('Token is malformed');
}

$computed = hash_hmac('sha256', $parts[0].'-'.$parts[1].'-'.$parts[2], $password);

if (!hash_equals($computed, $parts[3])) {
  http_response_code(403);
  die('Token is invalid');
}

if (time() > $parts[2]) {
  http_response_code(403);
  die('Token is expired');
}

if (session_id() !== $parts[1]) {
  http_response_code(403);
  die('Incorrect user id');
}

setcookie('CSRF-TOKEN', generateToken($password));
?>

Request Succeeded
