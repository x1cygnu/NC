<?php
include_once('sql.conf');

class SQL extends mysqli {
  public function __construct($host, $user, $pass, $dbname) {
    parent::__construct($host, $user, $pass, $dbname);
    if (mysqli_connect_error())
      throw new Exception("Failed to establish the database connection: " . $this->connect_error);
  }
}

function openSQL() {
  return new SQL(SQL_HOST, SQL_USER, SQL_PASS, SQL_DATABASE);
}

?>
