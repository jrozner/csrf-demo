<?php
require_once('common.php');
if (!isLoggedIn()) {
  header('Location: /login.html');
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CSRF Demo</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/jquery-1.12.4.min.js"></script>
  </head>
  <body>
    <div class="container">
      <div class="header clearfix">
        <nav>
          <ul class="nav pull-right">
            <li><button id="reset" class="btn btn-primary">Reset</button></li>
          </ul>
          <h3 class="text-muted">Bank of Antarctica</h3>
        </nav>
      </div>
      <div class="jumbotron">
        <h1><strong>Balance:</strong> $<span id="balance"><?= $_SESSION['balance'] ?></span></h1>
      </div>
      <div class="row">
        <div class="col-md-8">
          <h3>Transfers</h3>
          <table class="table table-striped">
            <thead>
              <tr>
                <th>From</th>
                <th>To</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
            <?php
            foreach ($_SESSION['transfers'] as $transfer) {
            ?>
              <tr>
                <td><?= $transfer['from'] ?></td>
                <td><?= $transfer['to'] ?></td>
                <td><?= $transfer['amount'] ?></td>
              </tr>
            <?php
            }
            ?>
            </tbody>
          </table>
        </div>
        <div class="col-md-4">
          <h3>Make a Transfer</h3>
          <form method="GET" action="transfer.php">
            <div class="form-group">
              <label for="to">To</label>
              <input class="form-control" type="text" name="to" id="to" />
            </div>
            <div class="form-group">
              <label for="amount">Amount</label>
              <input class="form-control" type="text" name="amount" id="amount" />
            </div>
            <input type="submit" class="btn btn-default" value="Send" />
          </form>
        </div>
      </div>
    </div>
  </body>
  <script>
  $(document).ready(function() {
    function drawPage(data) {
      $('#balance').text(data.balance);
      $('tbody>tr').remove();
      for (var i = 0; i < data.transfers.length; i++) {
        var transfer = data.transfers[i];
        var html = '<tr><td>'+transfer.from+'</td><td>'+transfer.to+'</td><td>'+transfer.amount+'</td></tr>'
        var el = $(html);
        $('tbody').append(el);
      }
    }

    $('form').on('submit', function(evt) {
      evt.preventDefault();
      $.ajax({
        "url": "/transfer.php",
        "method": "POST",
        "dataType": "json",
        "data": {"to": $('#to')[0].value, "amount": $('#amount')[0].value},
        "success": function(data, status, xhr) {
          drawPage(data);
        }
      });

      return false;
    });
    $('#reset').on('click', function(evt) {
      evt.preventDefault();
      $.ajax({
        "url": "/reset.php",
        "method": "POST",
        "dataType": "json",
        "success": function(data, status, xhr) {
          drawPage(data);
        }
      })
      return false;
    });
  });
  </script>
  <script src="js/bootstrap.min.js"></script>
</html>