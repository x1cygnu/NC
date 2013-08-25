<?php

include_once("./internal/security/validator.php");


function help_get($sql, $entry)
{
    $entry=makequotedstring($entry);
    $A=$sql->query("SELECT * FROM NC_Help WHERE Page=$entry");
    return $A[0];
}

function help_put($sql, $entry, $descr, $text)
{
    if ($_SESSION['IsAdmin'])
    {
	$entry=makequotedstring($entry);
        $descr=makequotedstring($descr);
	$text=makequotedstring($text);
        $sql->query("REPLACE INTO NC_Help VALUES(NULL, $entry, $descr, $text)");
    }
}

?>