<?php
include_once('sql.conf');
include_once('sqlexception.php');

class SQL extends mysqli {

  public function __construct($host, $user, $pass, $dbname) {
    parent::__construct($host, $user, $pass, $dbname);
    if (mysqli_connect_error())
      throw new Exception("Failed to establish the database connection: " . $this->connect_error);
  }

  public function __call($name, $arguments) {
    $count = count($arguments);
    $stmtstr = "SELECT $name(";
    $i = 0;
    foreach($arguments as $value) {
      if ($i>0)
        $stmtstr.=',';
      ++$i;
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
      $result = $resultobj->fetch_row()[0];
      $resultobj->free();
      return $result;
    } else
      return null;
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
