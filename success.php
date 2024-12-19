<?php
include("../db.php");
session_start();

// Check if total_amount session variable is set
if (isset($_SESSION['total_amount'])) {
  // Retrieve data from session
  $name = $_SESSION['name'];
  $email = $_SESSION['email'];
  $mobile = $_SESSION['mobile'];
  $city = $_SESSION['city'];
  $amount = $_SESSION['amount'];

  // Execute and check insertion status
  if ($stmt->execute()) {
    $_SESSION['msg'] = "<h2 style='color:green'>Form successfully submitted!</h2>";
  } else {
    $_SESSION['msg'] = "<h2 style='color:red'>Error: " . $stmt->error . "</h2>";
  }

  // Close the prepared statement
  $stmt->close();
} else {
  echo "Session data missing. Unable to process payment data.";
}
?>

<html>

<head>
  <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&display=swap" rel="stylesheet">
</head>
<style>
  body {
    text-align: center;
    padding: 40px 0;
    background: #EBF0F5;
  }

  h1 {
    color: #88B04B;
    font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
    font-weight: 900;
    font-size: 40px;
    margin-bottom: 10px;
  }

  p {
    color: #404F5E;
    font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
    font-size: 20px;
    margin: 0;
  }

  i {
    color: #9ABC66;
    font-size: 100px;
    line-height: 200px;
    margin-left: -15px;
  }

  .card {
    background: white;
    padding: 60px;
    border-radius: 4px;
    box-shadow: 0 2px 3px #C8D0D8;
    display: inline-block;
    margin: 0 auto;
  }

  .button {
    margin-top: 20px;
    text-decoration: none;
    color: white;
    background-color: #88B04B;
    padding: 10px 20px;
    border-radius: 5px;
    font-size: 18px;
  }

  .button-clear {
    background-color: #FF6347;
  }

  .mt-3 {
    margin-top: 3px;
  }
</style>

<body>
  <div class="card">
    <div style="border-radius:200px; height:200px; width:200px; background: #F8FAF5; margin:0 auto;">
      <i class="checkmark">✓</i>
    </div>

    <h1>Success</h1>
    <p>Transaction ID : <?php echo $_GET['tid']; ?></p>
    <p>Amount : <?php echo $_GET['amount'] / 100; ?></p>
    <p>We received your purchase request;<br /> we'll be in touch shortly!</p>
    <br>
    <a href="clear_session.php" class="button mt-3">Back to Home</a>
  </div>
</body>

</html>