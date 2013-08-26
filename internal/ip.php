<?php

//IPBan semiclass

include_once("./internal/common.php");
include_once("./internal/security/validator.php");
include_once("./internal/log.php");
include_once("./internal/multi.php");

function ipban_list(&$sql) {
	$addresses=array();
	$list=$sql->query("SELECT * from NC_IPBan");
	foreach ($list as $entry) {
		$addresses[]=ip_merge($entry['IP1'],$entry['IP2'],$entry['IP3'],$entry['IP4']);
	}
	return $addresses;
}

function ipban_add(&$sql, $ip) {
	$ipx=ip_separate($ip);
	$sql->query("INSERT INTO NC_IPBan VALUES (".$ipx[0].','.$ipx[1].','.$ipx[2].','.$ipx[3].')');
}

function ipban_remove(&$sql, $ip) {
	$ipx=ip_separate($ip);
	$sql->query("DELETE FROM NC_IPBan WHERE IP1=".$ipx[0].
			' AND IP2='.$ipx[1].
			' AND IP3='.$ipx[2].
			' AND IP4='.$ipx[3]);
}

function ipban_block_check(&$sql, $ip) {
	$ipx=ip_separate($ip);
	$ret=$sql->query("SELECT count(*) AS C FROM NC_IPBan WHERE" .
		  '	(IP1=0 OR IP1='.$ipx[0].') AND ' .
		  '	(IP2=0 OR IP2='.$ipx[1].') AND ' .
		  '	(IP3=0 OR IP3='.$ipx[2].') AND ' .
		  '	(IP4=0 OR IP4='.$ipx[3].')');
	return $ret[0]['C']>0;
}

?>
