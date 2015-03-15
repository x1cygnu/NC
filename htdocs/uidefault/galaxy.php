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
  $T->_($i*ElemSize,$TL,clone $num);
  $T->_($i*ElemSize,$BR-ElemSize,clone $num);
  $num = Div()->_(sprintf('%+d',$i+$mapy))->setClass('mapcoord');
  $T->_($TL,$i*ElemSize,clone $num);
  $T->_($BR-ElemSize,$i*ElemSize,clone $num);
}

$T->setOffset(($range+1-$mapx)*ElemSize,
              ($range+1-$mapy)*ElemSize);

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

foreach ($viewranges as $view) {
  $div = $T->div(($view['HomeX']-$view['Range'])*ElemSize,
                 ($view['HomeY']-$view['Range'])*ElemSize);
  $size = ($view['Range']*2+1)*ElemSize;
  $div->style['width'] = $size . 'px';
  $div->style['height'] = $size . 'px';
  $div->setClass('view');
}

foreach ($background as $bg) {
  $img = new Image('IMG/bg/'.$bg['FileName']);
  $T->_($bg['X1']*ElemSize, $bg['Y1']*ElemSize, $img);
  $width = ($bg['X2']-$bg['X1'])*ElemSize;
  $height = ($bg['Y2']-$bg['Y1'])*ElemSize;
  $img->style['width'] = $width . 'px';
  $img->style['height'] = $height . 'px';
  $img->style['z-index'] = $bg['Z']-100;
}

$H[]=$T;
?>
