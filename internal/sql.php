<?php

class SQL extends mysqli {
  public function __construct($host, $user, $pass, $dbname) {
    parent::__construct($host, $user, $pass, $dbname);
    if (mysqli_connect_error())
      error("Failed to establish the database connection");
  }
}

?>

