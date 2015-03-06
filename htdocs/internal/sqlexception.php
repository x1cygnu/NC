<?php

class SQLException extends Exception {
  public $errno;
  public $sqlstate;
  public $error;

  public function __construct($sql,$query) {
    parent::__construct("SQL error $sql->errno\n$query\n$sql->error\n");
    $this->errno = $sql->errno;
    $this->sqlstate = $sql->sqlstate;
    $this->error = $sql->error;
  }
}

class SQLDuplicateKeyException extends SQLException {}


function SQLThrowException($sql, $query) {
  switch ($sql->errno) {
    case 1062:
    case 1586:
      throw new SQLDuplicateKeyException($sql, $query);
    default:
      throw new SQLException($sql, $query);
  }
}

?>
