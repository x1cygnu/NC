<?php
include_once("./internal/account.php");

// awards semi-class

$round_code = 1;

function awards_give_medal(&$sql, $aid, $type, $rank)
{
global $round_code;
$aid=makeinteger($aid);
$type=makeinteger($type);
$rank=makeinteger($rank);
$Log=log_entry($sql, "medal", account_get_name($sql, $aid), $type, $rank);
$sql->query("INSERT INTO NC_Awards VALUES ($aid, $round_code, $rank, $type)");
log_result($sql, $Log, "OK");
}

function awards_list(&$sql, $aid)
{
    $aid=makeinteger($aid);
    return $sql->query("SELECT * FROM NC_Awards WHERE AID=$aid");
}

function awards_get_type($type)
{
    switch ($type)
    {
	case 1: return "CL";
	case 2: return "PL";
	case 3: return "RN";
	case 4: return "SC";
	default: return "";
    }
}

function awards_get_name($round, $rank, $type)
{
    $rank=makeinteger($rank);
    if ($rank>10)
	{
	$rank-=10;
	return "C" . $round . $rank . awards_get_type($type) . "A";
	}
    else
	return "C" . $round . $rank . awards_get_type($type);
}

function awards_get_medal($rank)
{
    if ($rank>10)
	return "MD" . ($rank-10) . "A";
    else
	return "MD" . $rank;
}


?>