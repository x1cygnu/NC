<?php

$H->addStyleFile($UI.'/buildbox.css');

class BuildBox extends Table {
  public $name;
  public $value;
  public $cost;
  public function __construct($name) {
    parent::__construct();
    $this->name = $name;
    $this->setClass('nullspace buildbox');
  }
  protected function prepare() {
    $this(1,1)->setClass('buildboxtop legend')->_($this->name);
    $this(2,1)->setClass('buildboxmain')->_($this->value);
    $this(3,1)->setClass('buildboxsub')->_($this->cost);
  }
}
function BuildBox($statname) { return new BuildBox($statname); }

?>
