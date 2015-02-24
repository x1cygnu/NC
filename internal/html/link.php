<?php
include_once('node.php');

class Link extends Node {
  public $url;
  public $params;

  public function __construct($url) {
    parent::__construct('a');
    $this->url = $url;
    $params = array();
  }
  public function addParam($key, $value) {
    $params[$key]=$value;
    return $this;
  }
  protected function prepare() {
    $addr = rawurlencode($this->url);
    $first = true;
    foreach($this->params as $key => $value) {
      if ($first)
        $addr .= '?';
      else
        $addr .= '&';
      $first = false;
      $addr .= rawurlencode($key).'='.rawurlencode($value);
    }
    $this->setAttribute('href',$addr);
  }

};
function Link($url) { return new Link($url); }

?>
