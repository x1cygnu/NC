<?php
include_once("node.php");

class Br extends Node {
  public function __construct() {
    parent::__construct('br');
  }
}
function Br() { return new Br(); }

class Paragraph extends Node {
  public function __construct() {
    parent::__construct('p');
  }
}
function Paragraph() { return new Paragraph(); }

class Div extends Node {
  public function __construct() {
    parent::__construct('div');
  }
}
function Div() { return new Div(); }

class Span extends Node {
  public function __construct() {
    parent::__construct('span');
  }
}
function Span() { return new Span(); }

?>
