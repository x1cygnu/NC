<?php
if (!isset($_SESSION['AID']))
  throw new NCAIDMissException('You are not logged in');
$AID = $_SESSION['AID'];
if (!isset($_SESSION['PID']))
  throw new NCPIDMissException('You are an inactive player');
$PID = $_SESSION['PID'];

include_once('./internal/starsystem.php');
include_once('./internal/system.php');

$sid = get('sid','integer');
if (!isset($sid))
  throw new NCException("SID is missing");

$sql = openSQL();
$star = starsystem_get($sql, $sid);
warning("TODO: Check if in range");
$planets = system_get($sql, $sid);
$sql->close();

?>
