<?php
include_once('node.php');

class Image extends Node {
  public $image;
  public $alt;
  public $width;
  public $height;

  public function __construct($image, $alt='', $title='') {
    parent::__construct('img');
    $this->image = $image;
    $this->alt = $alt;
    $this->width = 0;
    $this->height = 0;
  }

  protected function prepare() {
    $this->addAttribute('src',$this->image);
    if ($alt!='')
      $this->addAttribute('alt',$this->alt);
    if ($this->width > 0)
      $this->addAttribute('width',$this->width);
    if ($this->height > 0)
      $this->addAttribute('height',$this->height);
  }

}
?>
