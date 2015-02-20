<?php
include_once("node.php");

class Br extends Node {
  public function __construct() {
    parent::__construct('br');
  }
}

class Paragraph extends Node {
  public function __construct() {
    parent::__construct('p');
  }
}

class Div extends Node {
  public function __construct() {
    parent::__construct('div');
  }
}

class Span extends Node {
  public function __construct() {
    parent::__construct('span');
  }
}

?>
