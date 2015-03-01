<?php

function account_create($sql, $loginname, $publicname, $password) {
  try {
    $AID = $sql->NC_AccountCreate($loginname, $publicname, $password);
  } catch (SQLDuplicateKeyException $e) {
    throw new NCException("Name $loginname or $publicname is already taken");
    return null;
  }
  return $AID;
}

function account_login($sql, $loginname, $password) {
  $result = $sql->NC_AccountLogin($loginname, $password);
  if (!isset($result))
    throw new NCLoginFailException('Login failed!');
  return $result;
}

?>
