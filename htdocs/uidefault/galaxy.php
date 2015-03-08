<?php
include_once('./uidefault/menu.php');
include('./uidefault/messages.php');

$H->addStyleFile($UI.'/galaxy.css');

const ElemSize = 25;
$T=Frame((2*$range+3)*ElemSize,(2*$range+3)*ElemSize)
  ->setClass('map')
  ->setOffset(($range+1)*ElemSize, ($range+1)*ElemSize);
$TL = -($range+1)*ElemSize;
$BR = +($range+2)*ElemSize;
for ($i = -$range; $i<= +$range; ++$i) {
  $num = Div()->_(sprintf('%+d',$i+$mapx))->setClass('mapcoord');
  $T->_($i*ElemSize,$TL,$num);
  $T->_($i*ElemSize,$BR-ElemSize,$num);
  $num = Div()->_(sprintf('%+d',$i+$mapy))->setClass('mapcoord');
  $T->_($TL,$i*ElemSize,$num);
  $T->_($BR-ElemSize,$i*ElemSize,$num);
}

foreach ($stars as $star) {
  if ($star['StarType']==STAR_SPECIAL)
    $img='IMG/ssp.gif';
  else
    $img='IMG/s1.png';
  $img = Ref("system.php")
    ->_(new Image($img))
    ->addParam(field('sid'),$star['SID']);
  $T->_($star['X']*ElemSize,$star['Y']*ElemSize,$img);
}

$H[]=$T;
?>
