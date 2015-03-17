<?php
include_once('./uidefault/menu.php');
include('./uidefault/messages.php');

$H->addStyleFile($UI.'/science.css');

$T=new Table();

$T(1,1)
  ->_("Sciences")
  ->span(1,3)
  ->setClass('title');

$SCIENCE_NAMES = array(
    NC_RESEARCH_SENSORY => 'Sensory',
    NC_RESEARCH_ENGINEERING => 'Engineering',
    NC_RESEARCH_MATHEMATICS => 'Mathematics',
    NC_RESEARCH_PHYSICS => 'Physics',
    NC_RESEARCH_WARP => 'Warp',
    NC_RESEARCH_URBAN => 'Urban'
    );

$row=1;
foreach (RESEARCH_SCIENCES() as $type) {
  ++$row;
  $T($row,1)->_($SCIENCE_NAMES[$type])->setClass('legend');
  $T($row,2)->_($science[$type]['Level'])->setClass('level');
  $T($row,3)->_(new Progress(400,16,'IMG/pr1.gif','IMG/pr2.gif',$science[$type]['Progress']/$science[$type]['Max']));
  $T->row($row)->addClass('science');
  if ($type == $selected)
    $T->row($row)->addClass('selected');
}

$H[] = $T;

?>
