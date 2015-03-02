<?php
include_once('node.php');

class Form extends Node {
  public $post;
  public $target;

  public function __construct($target, $post=true) {
    parent::__construct('form');
    $this->post = $post;
    $this->target = $target;
  }

  protected function prepare() {
    $this->setAttribute('action',$this->target);
    $this->setAttribute('method',$this->post?'post':'get');
  }
}
function Form($target, $post=true) { return new Form($target, $post); }

class Input extends Node {
  protected $type;
  public $name;
  public $value;
  public $tabindex = 0;

  public function __construct($type, $name, $value='') {
    parent::__construct('input');
    $this->type = $type;
    $this->name = $name;
    $this->value = $value;
  }

  public function prepare() {
    $this->setAttribute('type',$this->type);
    if ($this->name !== '')
      $this->setAttribute('name',$this->name);
    if ($this->value !== '')
      $this->setAttribute('value',$this->value);
  }
}
function Input($type, $name, $value='') { return new Input($type, $name, $value); }

class TextInput extends Input {
  public function __construct($name, $value='') {
    parent::__construct('text', $name, $value);
  }
}
function TextInput($name, $value='') { return new TextInput($name, $value); }

class HiddenInput extends Input {
  public function __construct($name, $value='') {
    parent::__construct('hidden', $name, $value);
  }
}
function HiddenInput($name, $value='') { return new HiddenInput($name, $value); }

class PasswordInput extends Input {
  public function __construct($name, $value='') {
    parent::__construct('password', $name, $value);
  }
}
function PasswordInput($name, $value='') { return new PasswordInput($name, $value); }

class Submit extends Input {
  public function __construct($name, $value='') {
    parent::__construct('submit', $name, $value);
  }
}
function Submit($name, $value='') { return new Submit($name, $value); }

class ButtonInput extends Input {
  public function __construct($value='') {
    parent::__construct('button', '', $value);
  }
}
function ButtonInput($value='') { return new ButtonInput($value); }

class RadioInput extends Input {
  public $checked = false;
  public function __construct($name, $value='') {
    parent::__construct('radio', $name, $value);
  }
  public function prepare() {
    parent::prepare();
    if ($this->checked)
      $this->enableAttribute('checked');
  }
}
function RadioInput($name, $value='') { return new RadioInput($name, $value); }

class CheckboxInput extends Input {
  public $checked = false;
  public function __construct($name, $value='') {
    parent::__construct('checkbox', $name, $value);
  }
  public function prepare() {
    parent::prepare();
    if ($this->checked)
      $this->enableAttribute('checked');
  }
}
function CheckboxInput($name, $value='') { return new CheckboxInput($name, $value); }

class Select extends Node {
  public $name;
  public $default;

  public function __construct($name) {
    parent::__construct('select');
    $this->name = $name;
  }

  public function addOption($value, $description) {
    $this->content[$value] = $description;
    return $this;
  }

  protected function getBody() {
    $S = '';
    foreach ($this->content as $value => $description) {
        $S .= '<option value="'.htmlentities($value, ENT_QUOTES | ENT_HTML5).'"';
        if ($this->default == $value)
          $S .= ' selected';
        $S.='>'.htmlentities($description, ENT_HTML5)."</option>\n";
    }
    return $S;
  }

}
function Select($name) { return new Select($name); }

?>
