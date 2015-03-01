<?php
if (isset($PID)) {
  info('Player created created');
  $go="news";
  include('./gopart.php');
} elseif (isset($auiError)) {
  switch ($auiError) {
    case NC_ERR_AIDMISS:
      $go='index';
      include('./gopart.php');
      break;
    case NC_ERR_PIDPRESENT:
      $go='news';
      include('./gopart.php');
      break;
    default:
      $continue = true;
  }
} else
  $continue = true;
if (!empty($continue)) {
include("./$UI/messages.php");

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

$T=new Table();
//$T->setClass("nullspace");
foreach($RACE as $name => $enum) {
  $T($enum,1)->_($name)->setClass('legend');
  for ($i=-4; $i<=4; ++$i) {
    $T($enum,6+$i)->_(sprintf("%+d",$i))->setClass('racevalue')->addClass($classes[$i]);
  }
}
$row = $T->maxRows;
$T($row+1, 1)->_(Submit(field('submit_player_create'),'Create race'))->span(1,10)->setClass("buttons");

$H[]=Form('start.php')->_($T);
$H->addStyle($UI.'/race.css');
}
?>
