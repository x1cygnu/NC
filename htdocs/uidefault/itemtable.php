<?php

class ItemTable extends Table {
  public $currow = 1;
  public $curcol = 1;
  public $maxcol;
  public function __construct($maxcol) {
    parent::__construct();
    $this->maxcol = $maxcol;
  }
  public function _($v) {
    $this($this->currow,$this->curcol)->_($v);
    if (++$this->curcol > $this->maxcol) {
      ++$this->currow;
      $this->curcol=1;
    }
  }
}
function ItemTable($maxcol) { return new ItemTable($maxcol); }

?>
