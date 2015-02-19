<?php

class Node implements ArrayAccess {
  private $type;
  protected $attribute;
  protected $content;

  static private $voidTags = array('area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr');

  public function __construct($type) {
    $this->type = $type;
    $this->content = array();
    $this->attribute = array();
  }

  public function __toString() {
    $S = '<' . $this->type;
    foreach ($this->attribute as $key => $value)
      $S .= " $key=\"$value\"";
    if ($this->isVoidType())
      $S .= '/>';
    else {
      $S .= '>';
      foreach ($this->content as $value)
        $S .= $value;
      $S .= '</'.$this->type.'>';
    }
    return $S;
  }

  public function __call($name, $args) {
    echo "Calling " . $name . " with " . implode(', ', $args);
  }

  public function setAttribute($key, $value) {
    $encvalue = htmlentities($value, ENT_QUOTES | ENT_HTML5);
    $this->attribute[$key] = $encvalue;
  }

  public function isVoidType() {
    return in_array($this->type, self::$voidTags);
  }

  //ArrayAccess
  public function offsetExists($offset) {
    return isset($this->content[$offset]);
  }

  public function offsetGet($offset) {
    return isset($this->content[$offset]) ? $this->content[$offset] : null;
  }

  public function offsetSet($offset, $value) {
    if (is_null($offset))
      $this->content[] = $value;
    else
      $this->content[$offset] = $value;
  }

  public function offsetUnset($offset) {
    unset($this->content[$offset]);
  }


}

?>
