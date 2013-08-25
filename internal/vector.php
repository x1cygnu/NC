<?php

class V2D {
  var $x;
  var $y;
  function __construct($x = 0, $y = 0) {
      $this->x = $x;
      $this->y = $y;
  }
}

class V3D {
  var $x;
  var $y;
  var $z;
  function __construct($x = 0, $y = 0, $z = 0) {
    $this->x = $x;
    $this->y = $y;
    $this->z = $z;
  }
}

?>
