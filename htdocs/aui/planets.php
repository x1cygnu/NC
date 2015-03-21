<?php
if (!isset($_SESSION['AID']))
  throw new NCAIDMissException('You are not logged in');
$AID = $_SESSION['AID'];
if (!isset($_SESSION['PID']))
  throw new NCPIDMissException('You are an inactive player');
$PID = $_SESSION['PID'];

include_once('./internal/ownplanets.php');
include_once('./internal/level.php');

$sql = openSQL();
$planets = own_planets_get($sql, $PID);
foreach ($planets as &$planet) {
  $planet['Link'] = 'planet.php?' . field('owned_planet').'='.$planet['PLID'];
}
$sql->close();

?>
