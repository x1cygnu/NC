<?php
if (!isset($_SESSION['AID']))
  throw new NCAIDMissException('You are not logged in');
$AID = $_SESSION['AID'];
if (!isset($_SESSION['PID']))
  throw new NCPIDMissException('You are an inactive player');
$PID = $_SESSION['PID'];

include_once('./internal/planet.php');

$PLID = get('owned_planet','integer');
if (!isset($PLID))
  throw new NCException("Please specify which planet");

$sql = openSQL();
$owner = planet_get_owner($sql, $PLID);
if ($owner != $PID)
  throw new NCException("You are not the owner of this planet");

$planet = new Planet($sql, $PLID);
$sql->close();

?>
