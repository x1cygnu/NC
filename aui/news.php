<?php

if (!isset($_SESSION['AID']))
  throw new NCException('You are not logged in');
if (!isset($_SESSION['PID']))
  throw new NCException('You are an inactive player');

?>
