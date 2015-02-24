<?php
if (isset($auiError)) {
  include('./aui/index.php');
  if (!empty($login))
    $savedPlayerName = $login;
  include('./uidefault/index.php');
} else {
  include('./uidefault/messages.php');
  $H[] = 'done';
}
?>
