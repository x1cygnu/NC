<?php
if (!isset($_SESSION['AID']))
  throw new NCAIDMissException('You are not logged in');
$AID = $_SESSION['AID'];
if (!isset($_SESSION['PID']))
  throw new NCPIDMissException('You are an inactive player');
$PID = $_SESSION['PID'];

include_once('./internal/research.php');
include_once('./internal/player.php');

$sql = openSQL();
$science = research_get_all_science($sql, $PID);
$selected = player_get_selected_research($sql, $PID);
$sql->close();

?>
