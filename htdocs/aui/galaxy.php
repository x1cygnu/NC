<?php
if (!isset($_SESSION['AID']))
  throw new NCAIDMissException('You are not logged in');
$AID = $_SESSION['AID'];
if (!isset($_SESSION['PID']))
  throw new NCPIDMissException('You are an inactive player');
$PID = $_SESSION['PID'];

include_once('./internal/galaxy.php');

$mapx = get('map_x','integer');
$mapx = get_or_default($x, 0);
$mapy = get('map_y','integer');
$mapy = get_or_default($y, 0);
$range = get('map_range','integer');
$range = get_or_default($range, 10);
if ($range>50)
  $range=50;

$sql = openSQL();
$stars = galaxy_get($sql, $mapx, $mapy, $range);
$sql->close();

?>
