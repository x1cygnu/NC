<?php

define("MessageInfo",1);
define('MessageWarning',2);
define('MessageError',3);

class Message {
  public $type;
  public $msg;
  public function __construct($type, $msg) {
    $this->type = $type;
    $this->msg = $msg;
  }
}

$Messages = array();

function error($msg) {
  global $Messages;
  $Messages[] = new Message(MessageError,$msg);
}
function warning($msg) {
  global $Messages;
  $Messages[] = new Message(MessageWarning,$msg);
}
function info($msg) {
  global $Messages;
  $Messages[] = new Message(MessageInfo,$msg);
}

?>

