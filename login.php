<?php
include_once("internal/header.php");
$H = nc_header();
if (postSubmitted('login')) {
  $sql = openSQL();
//  $success = account_login
  include($UI.'/login.php');
  $sql->close();
} else {
  include($UI.'/index.php');
}
echo $H;
?>
