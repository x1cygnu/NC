<?php
include_once("internal/account.php");

function isCharOK($c) {
  return ($c>='A' and $c<='Z') || ($c>='a' and $c<='z') || ($c>='0' and $c<'9');
}

function isSpace($c) {
  return $c==' ';
}

function isNameOK($str) {
  $len = strlen($str);
  $prevspace = false;
  for ($i = 0; $i<$len; ++$i) {
    if (($i==0 or $i==$len-1) and isSpace($str[$i]))
      throw new NCException('Name cannot start or end with whitespace');
    if (isSpace($str[$i])) {
      if ($prevspace)
        throw new NCException('Two whitespace characters next to each other are dissalowed');
      $prevspace=true;
      continue;
    }
    if (!isCharOK($str[$i]))
      throw new NCException('Invalid character used!');
    $prevspace=false;
  }
}

if (postSubmitted('submit_register')) {
  $loginName = post('login','string');
  $showName = post('showname','string');
  $password = post('password','string');
  $password2 = post('password2','string');
  $email = post('email','string');

  if (empty($loginName)) throw new NCException('Missing login name');
  if (strlen($loginName)<2) throw new NCException('Login name is too short');
  if (strlen($loginName)>32) throw new NCException('Login name is too long');
  isNameOK($loginName);
  
  if (empty($showName))
    $showName = $loginName;
  else {
    if (strlen($showName)<2) throw new NCException('Shown name is too short');
    if (strlen($showName)>32) throw new NCException('Shown name is too long');
    isNameOK($showName);
  }

  if (empty($password) or empty($password2)) throw new NCException('Password is missing');
  if ($password != $password2) throw new NCException('Passwords do not match');

  if (empty($email)) throw new NCException('Email is missing');
  $demail = explode('@',$email);
  if (count($demail)!=2) throw new NCException('Invalid email format');
  if (strlen($demail[1])<5) throw new NCException('Email domain name surprisingly short');
  if (substr_count($demail[1],'.')<1) throw new NCException('Invalid email domain format');

  $sql = openSQL();
  $registerAID = account_create($sql, $loginName, $showName, $password);
  $sql->close();
}
?>
