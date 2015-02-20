<?php

class Node implements ArrayAccess {
  private $type;
  protected $attribute;
  protected $content;
  public $big;

  static private $voidTags = array('area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr');

  public function __construct($type,$big=false) {
    $this->type = $type;
    $this->content = array();
    $this->attribute = array();
    $this->big = $big;
  }

  protected function getHeader() {
    $S = '<' . $this->type;
    foreach ($this->attribute as $key => $value)
      if ($value === true)
        $S .= " $key";
      else
        $S .= " $key=\"$value\"";
    if ($this->isVoidType())
      $S .= '/>';
    else
      $S .= '>';
    return $S;
  }

  protected function getBody() {
    $S = '';
    if (!$this->isVoidType())
      foreach ($this->content as $value)
        $S .= $value;
    return $S;
  }

  protected function getFooter() {
    if (!$this->isVoidType())
      return '</'.$this->type.'>'.($this->big?"\n":'');
    else 
      return '';
  }

  protected function prepare() {
  }

  public function __toString() {
    $this->prepare();
    return $this->getHeader() . $this->getBody() . $this->getFooter();
  }

  public function setAttribute($key, $value) {
    $encvalue = htmlentities($value, ENT_QUOTES | ENT_HTML5);
    $this->attribute[$key] = $encvalue;
  }

  public function addAttribute($key, $value) {
    $encvalue = htmlentities($value, ENT_QUOTES | ENT_HTML5);
    if (isset($this->attribute[$key]))
      $this->attribute[$key] .= ' '.$encvalue;
    else
      $this->attribute[$key] = $encvalue;
  }

  public function clearAttribute($key) {
    unset($this->attribute[$key]);
  }

  public function enableAttribute($key) {
    $this->attribute[$key] = true;
  }

  public function isVoidType() {
    return in_array($this->type, self::$voidTags);
  }

  function onClick($action) {$this->setAttribute('onclick',$action);}
  function onLoad($action) {$this->setAttribute('onload',$action);}
  function onMouseOver($action) {$this->setAttribute('onmouseover',$action);}
  function onMouseOut($action) {$this->setAttribute('onmouseout',$action);}
  function onMouseMove($action) {$this->setAttribute('onmousemove',$action);}
  function onChange($action) {$this->setAttribute('onchange',$action);}
  function onKeyPress($action) {$this->setAttribute('onkeypress',$action);}
  function onKeyUp($action) {$this->setAttribute('onkeyup',$action);}
  function onSubmit($action) {$this->setAttribute('onsubmit',$action);}
  function onFocus($action) {$this->setAttribute('onfocus',$action);}

  function setClass($class) {$this->setAttribute('class',$class);}
  function setId($id) {$this->setAttribute('id',$id);}
  function setStyle($style) {$this->setAttribute('style',$style);}

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
