<?php
include_once('./uidefault/menu.php');
include('./uidefault/messages.php');
include_once('./uidefault/statusbox.php');
include_once('./uidefault/buildbox.php');
include_once('./uidefault/itemtable.php');
include_once('./uidefault/itemnames.php');

function populateItemTable($TT, $items) {
foreach ($items as $type => $item) {
  $box = new BuildBox(item_name($type));
  $box->value = $item->amount;
  $box->cost = $item->currentCost;
  $TT->_($box);
}
}

$H->addStyleFile($UI.'/planet.css');

$T=new Table();
$T->setClass('planetmain');

$T(1,1)
  ->_($planet->name)
  ->setClass('title');

$TT = new ItemTable(5);

$pop = new StatusBox('Population');
$pop->mainvalue=$planet->pop['Level'];
$pop->subvalue=$planet->pop['Towards'] .'/'. $planet->pop['Next'];
$TT->_($pop);

$min = new StatusBox('Minerals');
$min->mainvalue=$planet->minerals['Level'];
$min->subvalue=$planet->minerals['Towards'] .'/'. $planet->minerals['Next'];
$TT->_($min);

$T(2,1)->_($TT);

$T(3,1)
  ->_('Base')
  ->setClass('title');

$TT = new ItemTable(5);
populateItemTable($TT, $planet->base);
$T(4,1)->_($TT);

$T(5,1)
  ->_('Low orbit')
  ->setClass('title');

$TT = new ItemTable(5);
populateItemTable($TT, $planet->low);
$T(6,1)->_($TT);

$T(7,1)
  ->_('High orbit')
  ->setClass('title');
$TT = new ItemTable(5);
populateItemTable($TT, $planet->high);
$T(8,1)->_($TT);

if (!empty($siege)) {
  $T(9,1)
    ->_('Foreign fleets')
    ->setClass('title');
}

$H[]=$T;
?>
