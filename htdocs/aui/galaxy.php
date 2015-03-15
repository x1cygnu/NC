<?php
if (!isset($_SESSION['AID']))
  throw new NCAIDMissException('You are not logged in');
$AID = $_SESSION['AID'];
if (!isset($_SESSION['PID']))
  throw new NCPIDMissException('You are an inactive player');
$PID = $_SESSION['PID'];

include_once('./internal/galaxy.php');
include_once('./internal/player.php');
include_once('./internal/research.php');

$mapx = get('map_x','integer');
$mapy = get('map_y','integer');
$range = get('map_range','integer');
$range = get_or_default($range, 10);
if ($range>50)
  $range=50;

$sql = openSQL();
$home = player_get_home($sql, $PID);
$sensory = research_get($sql, $PID, NC_RESEARCH_SENSORY);
$home['Range'] = $sensory['Level']/2;

$mapx = get_or_default($mapx,$home['HomeX']);
$mapy = get_or_default($mapy,$home['HomeY']);

$stars = galaxy_get($sql, $mapx, $mapy, $range);

$viewranges = array();
$viewranges[] = $home;

$background = galaxy_get_background($sql, $mapx, $mapy, $range);

$sql->close();

?>
