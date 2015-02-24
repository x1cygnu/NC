<?php
if (!isset($go))
  throw new Exception('The $go is not set!\n');
try {
include("./aui/$go.php");
} catch (NCException $e) {
  error($e->getMessage());
  $auiError = $e;
}
include("./$UI/$go.php");
?>
