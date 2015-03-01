<?php
include_once('sql.conf');
include_once('sqlexception.php');

class SQL extends mysqli {

  public function __construct($host, $user, $pass, $dbname) {
    parent::__construct($host, $user, $pass, $dbname);
    if (mysqli_connect_error())
      throw new Exception("Failed to establish the database connection: " . $this->connect_error);
  }

  private function purge() {
    while ($this->more_results()) {
      $this->next_result();
      $obj = $this->store_result();
      if ($obj)
        $obj->free();
    }
  }

  
  private function arrayget($args) {
    $name = $args[0];
    $argc = count($args);
    $stmtstr = "CALL $name(";
    for($i=1; $i<$argc; ++$i) {
      if ($i>1)
        $stmtstr.=',';
      $value = $args[$i];
      if (is_string($value))
        $value = '"'.$this->real_escape_string($value).'"';
      $stmtstr.=$value;
    }
    $stmtstr.=')';
    $this->real_query($stmtstr);
    $this->checkError($stmtstr);
    $resultobj = $this->store_result();
    $this->checkError($stmtstr);
    if ($resultobj) {
      $result = array();
      while(true) {
        $row = $resultobj->fetch_assoc();
        if (!isset($row))
          break;
        $result[] = $row;
      }
      $resultobj->free();
      $this->purge();
      return $result;
    }
    $this->purge();
    return null;
  }

  public function get() {
    return $this->arrayget(func_get_args());
  }

  public function __call($name, $arguments) {
    $result = $this->arrayget(array_merge( array($name), $arguments));
    if (isset($result)) {
      //only first row
      if (count($result)>0)
        $result = $result[0];
      if (isset($result['Result']))
        $result = $result['Result'];
      return $result;
    }
  }

  private function checkError($query) {
    if ($this->errno)
      SQLThrowException($this,$query);
  }
}

function openSQL() {
  $sql = new SQL(SQL_HOST, SQL_USER, SQL_PASS, SQL_DATABASE);
  return $sql;
}

?>
