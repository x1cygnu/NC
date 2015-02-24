<?php

function account_create($sql, $loginname, $publicname, $password) {
  try {
    $AID = $sql->NC_AccountCreate($loginname, $publicname, $password);
  } catch (SQLDuplicateKeyException $e) {
    error("Name $loginname or $publicname is already taken");
    return null;
  }
  return $AID;
}

function account_login($sql, $loginname, $password) {
  $result = $sql->NC_AccountLogin($loginname, $password);
  if (!isset($result))
    throw new NCException('Login failed!');
  return $result;
}

?>
