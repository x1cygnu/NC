<?php
include_once("node.php");

class Cell extends Node {
  public $rowSpan = 1;
  public $colSpan = 1;

  public function __construct() {
    parent::__construct("td");
  }

  protected function prepare() {
    if ($this->rowSpan>1)
      $this->setAttribute("rowspan",$this->rowSpan);
    if ($this->colSpan>1)
      $this->setAttribute("colspan",$this->colSpan);
  }

  public function span($dy, $dx) {
    $this->rowSpan = $dy;
    $this->colSpan = $dx;
    return $this;
  }
}
function Cell() { return new Cell(); }

class Table extends Node {
  public $maxRows = 0; //x coordinate
  public $maxCols = 0; //y coordinate

  public function __construct() {
    parent::__construct('table',true);
    $args = func_get_args();
    switch (count($args)) {
      case 0: break;
      case 1:
              $this->maxRows = $args[0]->x;
              $this->maxCols = $args[0]->y;
              break;
      case 2:
              $this->maxRows = $args[0];
              $this->maxCols = $args[1];
              break;
      default:
              throw new Exception("Invalid argument(s) to Table::__construct: ",$args);
    }
  }

  private function get($row, $col) {
    if (!isset($this->content[$row]))
      $this->content[$row] = array();
    if (!isset($this->content[$row][$col]))
      $this->content[$row][$col] = new Cell();
    return $this->content[$row][$col];
  }

  public function __invoke($row, $col) {
    if ($this->maxRows < $row)
      $this->maxRows = $row;
    if ($this->maxCols < $col)
      $this->maxCols = $col;
    return $this->get($row,$col);
  }

  public function join($fromRow,$fromCol,$toRow,$toCol) {
    $this($fromRow,$fromCol)->span($toRow-$fromRow+1,$toCol-$fromCol+1);
    return $this;
  }

  protected function getBody() {
    $S = '';
    $exclusion = array();
    for ($row = 1; $row<=$this->maxRows; ++$row) {
      $S .= "<tr>";
      for ($col = 1; $col<=$this->maxCols; ++$col) {
        if (isset($exclution[$row][$col]))
          continue;
        if (!isset($this[$row][$col])) {
          $S .= '<td></td>';
          continue;
        }
        $cell = $this[$row][$col];
        if ($cell->rowSpan>1 or $cell->colSpan>1) {
          for ($spanrow = 0; $spanrow<$cell->rowSpan; ++$spanrow)
            for ($spancol = 0; $spancol<$cell->colSpan; ++$spancol)
              $exclusion[$spanrow+$row][$spancol+$col] = true;
        }
        $S .= $cell;
      }
      $S .= "</tr>\n";
    }
    return $S;
  }
  
}

?>
