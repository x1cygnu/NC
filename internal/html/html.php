<?php
include_once("node.php");

class HTML extends Node {
  protected $meta;
  protected $style;
  protected $scriptfile;
  protected $script;


  public function __construct($title) {
    parent::__construct('body');
    $this->title=htmlentities($title, ENT_HTML5);
    $this->meta = array();
    $this->style = array();
    $this->scriptfile = array();
    $this->script = '';
  }

  public function __toString() {
    $metas = '';
    foreach ($this->meta as $key=>$value)
      $metas .= "  <meta name=\"$key\" content=\"$value\"/>\n";

    $styles = '';
    foreach ($this->style as $filename)
      $styles .= "  <link rel=\"stylesheet\" href=\"$filename\"/>\n";

    $scripts = "";
    foreach ($this->scriptfile as $filename)
      $scripts .= "  <script src=\"$filename\"></script>\n";
    if ($this->script != '')
      $scripts .= "  <script>$this->script</script>\n";

    $S = <<<"END"
<!doctype html>
<html><head>
  <meta charset="utf-8">
  <title>$this->title</title>
$metas
$styles
$scripts
</head>
END;
    $S .= parent::__toString();
    $S .= "</html>";
    return $S;
  }


  public function setMeta($key, $value) {
    $enckey = htmlentities($key, ENT_QUOTES | ENT_HTML5);
    $encvalue = htmlentities($key, ENT_QUOTES | ENT_HTML5);
    $this->meta[$enckey] = $encvalue;
  }

  public function addStyle($filename) {
    $encname = htmlentities($filename, ENT_QUOTES | ENT_HTML5);
    $this->style[] = $encname;
  }

  public function addScriptFile($filename) {
    $encname = htmlentities($filename, ENT_QUOTES | ENT_HTML5);
    $this->scriptfile[] = $encname;
  }

  public function addScript($script) {
    $encscript = htmlentities($script, ENT_HTML5);
    $this->script .= $encscript . "\n";
  }


}

?>
