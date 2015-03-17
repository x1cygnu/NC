<?php

class Progress extends Node {
  public $width;
  public $height;
  public $posimg;
  public $negimg;
  public $value;

  public function __construct($width,$height,$posimg,$negimg,$value) {
    parent::__construct('div',true);
    $this->width = $width;
    $this->height = $height;
    $this->posimg = $posimg;
    $this->negimg = $negimg;
    $this->value = $value;
  }
  protected function prepare() {
    $this->style['width'] = $this->width . 'px';
    $poswidth = intval(round($this->value * $this->width));

    $pos = new Image($this->posimg);
    $pos->style['height'] = $this->height . 'px';
    $pos->style['width'] = $poswidth . 'px';
    $this[] = $pos;

    $neg = new Image($this->negimg);
    $neg->style['height'] = $this->height . 'px';
    $neg->style['width'] = ($this->width - $poswidth) . 'px';
    $this[] = $neg;

    parent::prepare();
  }

}

?>
