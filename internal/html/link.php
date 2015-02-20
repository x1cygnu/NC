<?php
include_once('node.php');

class Link extends Node {
  public $url;
  public $params;

  public function __construct($url, $content=null) {
    parent::__construct('a');
    $this->url = $url;
    $params = array();
    if (!is_null($content))
      $this[] = $content;
  }
  public function addParam($key, $value) {
    $params[$key]=$value;
  }
  protected function prepare() {
    $addr = $this->url;
    $first = true;
    foreach($this->params as $key => $value) {
      if ($first)
        $addr .= '?';
      else
        $addr .= '&';
      $first = false;
      $addr .= $key.'='.$value;
    }
    $this->setAttribute('href',$addr);
  }

};

?>

