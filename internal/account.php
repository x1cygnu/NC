<?php

function account_create($sql, $loginname, $publicname, $password) {
  try {
    $AID = $sql->NC_AccountCreate($loginname, $publicname, $password);
  } catch (SQLDuplicateKeyException $e) {
    error("Name $loginname or $publicname is already taken");
    return 0;
  }
  return $AID;
}

function account_login($sql, $loginname, $password) {
  $AID = $sql->NC_AccountLogin($loginname, $password);

}

?>
