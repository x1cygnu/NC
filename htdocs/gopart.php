<?php
if (!isset($go))
  throw new Exception('The $go is not set!\n');
try {
include("./aui/$go.php");
} catch (NCAIDMissException $e) {
  error($e->getMessage());
  $redirect = 'index';
} catch (NCAIDPresentException $e) {
  error($e->getMessage());
  $redirect = 'news';
} catch (NCPIDMissException $e) {
  error($e->getMessage());
  $redirect = 'start';
} catch (NCPIDPresentException $e) {
  error($e->getMessage());
  $redirect = 'news';
} catch (NCLoginFailException $e) {
  error($e->getMessage());
  $redirect = 'index';
} catch (NCException $e) {
  error($e->getMessage());
  $auiError = $e;
}
if (isset($redirect)) {
  $go = $redirect;
  unset($redirect);
  include('./gopart.php');
} else {
  include("./$UI/$go.php");
}
?>
