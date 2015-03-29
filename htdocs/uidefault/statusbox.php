<?php

$H->addStyleFile($UI.'/statusbox.css');

class StatusBox extends Table {
  public $statname;
  public $mainvalue;
  public $subvalue;
  public function __construct($statname) {
    parent::__construct();
    $this->statname = $statname;
    $this->setClass('nullspace statbox');
  }
  protected function prepare() {
    $this(1,1)->setClass('statboxtop legend')->_($this->statname);
    $this(2,1)->setClass('statboxmain')->_($this->mainvalue);
    $this(3,1)->setClass('statboxsub')->_($this->subvalue);
  }
}
function StatusBox($statname) { return new StatusBox($statname); }

?>
