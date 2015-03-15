<?php

class Frame extends Div {
  public $width;
  public $height;
  public $offsetx = 0;
  public $offsety = 0;
  public function __construct($width, $height) {
    parent::__construct();
    $this->width = $width;
    $this->height = $height;
  }
  protected function prepare() {
    $this->style['width'] = $this->width . 'px';
    $this->style['height'] = $this->height . 'px';
    $this->style['position'] = 'relative';
    parent::prepare();
  }
  public function setOffset($x,$y) {
    $this->offsetx=$x;
    $this->offsety=$y;
    return $this;
  }
  public function _($x,$y,$obj) {
    if ($obj instanceof Node)
      $div = $obj;
    else
      $div = Div()->_($obj);
    $div->style['position']='absolute';
    $div->style['left']=($x+$this->offsetx).'px';
    $div->style['top']=($y+$this->offsety).'px';
    $this[] = $div;
    return $this;
  }
  public function div($x,$y) {
    $div = new Div();
    $div->style['position']='absolute';
    $div->style['left']=($x+$this->offsetx).'px';
    $div->style['top']=($y+$this->offsety).'px';
    $this[] = $div;
    return $div;
  }
}
function Frame($width, $height) { return new Frame($width, $height); }

?>
