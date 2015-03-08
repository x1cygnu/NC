<?php

function system_get($sql, $sid) {
  return $sql->get('NC_SystemGet',$sid);
}
?>
