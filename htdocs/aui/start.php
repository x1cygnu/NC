<?php
include_once('./internal/race.php');
include_once('./internal/player.php');

if (empty($_SESSION['AID']))
  throw new NCAIDMissException('You must log in first');
if (postSubmitted('submit_player_create')) {
  if (!empty($_SESSION['PID']))
    throw new NCPIDPresentException('You are already present in the game');
  $sum = 0;
  foreach($RACE as $stat => $v) {
    $value = post($stat, 'integer');
    if ($value<-4 or $value>4)
      throw new NCException('Invalid race attribute');
    $sum += $value;
    $$stat = $value;
  }
  if ($sum != 0)
    throw new NCException('Race stats do not sum up to 0');

  $sql = openSQL();
  $PID = player_create($sql, $_SESSION['AID']);
  foreach ($RACE as $stat => $enum) {
    if ($$stat != 0)
      player_set_race($sql, $PID, $enum, $$stat); 
  }
  $_SESSION['PID'] = $PID;
  $sql->close();
}
?>
