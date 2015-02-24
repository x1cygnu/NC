<?php
include_once("./internal/account.php");

if (postSubmitted('submit_login')) {
  $sql = openSQL();
  $login = post('login','string');
  $password = post('password','string');
  $account = account_login($sql, $login, $password);
  $_SESSION['AID'] = intval($account['AID']);
  if (isset($account['PID']))
    $_SESSION['PID'] = intval($account['PID']);
  info('Login succesfull');
  $sql->close();
}
?>
