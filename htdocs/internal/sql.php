<?php
include_once('sqlconf.php');
include_once('sqlexception.php');

class SQL extends mysqli {
  public $debug = false;
  public $lastquery = '';

  public function __construct($host, $user, $pass, $dbname) {
    parent::__construct($host, $user, $pass, $dbname);
    if (mysqli_connect_error())
      throw new Exception("Failed to establish the database connection: " . $this->connect_error);
  }

  public function real_query($query) {
    $this->lastquery = $query;
    $result = parent::real_query($query);
    $this->checkError();
    return $result;
  }

  public function query($query) {
    $this->lastquery = $query;
    $result = parent::query($query);
    $this->checkError();
    return $result;
  }

  private function purge() {
    while ($this->more_results()) {
      $this->next_result();
      $obj = $this->store_result();
      if ($obj)
        $obj->free();
    }
  }

  private function getresult() {
    $resultobj = $this->store_result();
    $this->checkError();
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
      if ($this->debug) {
        if (count($result)==0)
          print "<p>empty table</p>";
        else {
          print "<table>";
          print "<tr><td>row</td>";
          foreach ($result[0] as $key => $value)
            print "<td>$key</td>";
          print "</tr>";
          for ($i=0; $i<count($result); ++$i) {
            print "<tr><td>$i</td>";
            foreach ($result[$i] as $value) {
              if (isset($value))
                print "<td>$value</td>";
              else
                print "<td>null</td>";
            }
            print "</tr>\n";
          }
          print "</table>\n";
        }
      }
      return $result;
    }
    $this->purge();
    if ($this->debug)
      print "<p>null</p>";
    return null;
  }

  private function single($result) {
    if (!empty($result)) {
      //only first row
      if (count($result)>0)
        $result = $result[0];
      if (isset($result['Result']))
        $result = $result['Result'];
      return $result;
    } else
      return null;
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
    if ($this->debug)
      print "<p>$stmtstr</p>";
    $this->real_query($stmtstr);
    return $this->getresult();
  }

  public function get() {
    return $this->arrayget(func_get_args());
  }

  public function __call($name, $arguments) {
    $result = $this->arrayget(array_merge( array($name), $arguments));
    return $this->single($result);
  }

  private function checkError() {
    if ($this->errno)
      SQLThrowException($this,$this->lastquery);
  }

  public function setGlobal($name, $value) {
    if (is_string($value))
      $value = '"'.$this->real_escape_string($value).'"';
    $this->real_query("UPDATE $this->globalTable SET $name=$value");
    $this->getresult(); //ignore result
  }

  public function getGlobal($name) {
    $this->real_query("SELECT $name AS Result FROM $this->globalTable");
    $result = $this->getresult();
    return $this->single($result);
  }
}

function openSQL() {
  $sql = new SQL(SQL_HOST, SQL_USER, SQL_PASS, SQL_DATABASE);
  $sql->globalTable = "NC_Config";
  return $sql;
}

?>
