<?php
if (!isset($_SESSION['AID']))
  throw new NCAIDMissException('You are not logged in');
$AID = $_SESSION['AID'];
if (!isset($_SESSION['PID']))
  throw new NCPIDMissException('You are an inactive player');
$PID = $_SESSION['PID'];

include_once('./internal/research.php');
include_once('./internal/player.php');

$selected = get('science_switch','integer');
if (isset($selected)) {
  if ($selected<1 or $selected>6)
    unset($selected);
}


$sql = openSQL();
if (isset($selected))
  player_set_selected_research($sql, $PID, $selected);
else
  $selected = player_get_selected_research($sql, $PID);
$science = research_get_all_science($sql, $PID);
$sql->close();

foreach (RESEARCH_SCIENCES() as $type) {
  $science[$type]['Link'] = 'science.php?'.field('science_switch').'='.$type;
}

?>
