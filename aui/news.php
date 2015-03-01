<?php

if (!isset($_SESSION['AID']))
  throw new NCAIDMissException('You are not logged in');
if (!isset($_SESSION['PID']))
  throw new NCPIDMissException('You are an inactive player');

?>
