<?php
include_once("node.php");

class Br extends Node {
  public function __construct() {
    parent::__construct('br',true);
  }
}
function Br() { return new Br(); }

class Paragraph extends Node {
  public function __construct() {
    parent::__construct('p',true);
  }
}
function Paragraph() { return new Paragraph(); }

class Div extends Node {
  public function __construct() {
    parent::__construct('div',true);
  }
}
function Div() { return new Div(); }

class Span extends Node {
  public function __construct() {
    parent::__construct('span');
  }
}
function Span() { return new Span(); }

class Button extends Node {
  public function __construct() {
    parent::__construct('button');
    $this->setAttribute('type','button');
  }
}
function Button() { return new Button(); }

?>
