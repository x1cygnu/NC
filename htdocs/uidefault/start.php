<?php
if (isset($PID)) {
  success('Player race created');
  $go="news";
  include('./gopart.php');
} else {
include("./$UI/messages.php");

foreach ($RACE as $name => $enum) {
  $$name = get_or_default($$name, 0);
}

$classes = array(
    -4 => 'neg4',
    -3 => 'neg3',
    -2 => 'neg2',
    -1 => 'neg1',
    0 => 'zero',
    1 => 'pos1',
    2 => 'pos2',
    3 => 'pos3',
    4 => 'pos4'
    );

$F=new Form('start.php');
$T=new Table();
$F[]=$T;
$T->setClass("racetable");
foreach($RACE as $name => $enum) {
  $F->_(HiddenInput(field($name),$$name));
  $T($enum,1)->_($name)->setClass('legend');
  for ($i=-4; $i<=4; ++$i) {
    $B = Button()->_(sprintf("%+d",$i));
    $B->setClass('racevalue')->addClass($classes[$i]);
    asRadio($B, field($name), $i, $i == $$name);
    $T($enum,6+$i)[] = $B;
  }
}
$row = $T->maxRows;
$T($row+1, 1)->_(Submit(field('submit_player_create'),'Create race'))->span(1,10)->setClass("buttons");

$H[]=$F;
$H->addStyleFile($UI.'/race.css');
$H->addScriptFile($UI.'/js/radio.js');
}
?>
