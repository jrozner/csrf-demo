<?php
require_once('common.php');
if (!isLoggedIn()) {
  header('Location: /login.html');
}

$amount = $_REQUEST['amount'];
$to = $_REQUEST['to'];

if ($amount > $_SESSION['balance']) {
  die("insufficient funds");
}

$_SESSION['balance'] -= $amount;
array_push($_SESSION['transfers'], array("to" => $to, "from" => $_SESSION['username'], "amount" => $amount));

header('Content-Type: application/json');
echo json_encode(array("balance" => $_SESSION['balance'], "transfers" => $_SESSION['transfers']));
?>
