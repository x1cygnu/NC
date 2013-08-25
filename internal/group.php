<?php

//group management semi-class

function get_members(&$sql,$gid)
{
    $gid=makeinteger($gid);
    return $sql->query("SELECT Act.AID, Act.Nick FROM NC_Members GrM JOIN NC_Account Act ON GrM.AID=Act.AID "
	    . "WHERE GrM.GID=$gid ORDER By Act.Nick");
}

function get_groups(&$sql)
{
    return $sql->query("SELECT Gr.*, (SELECT count(*) FROM NC_Members GrM WHERE GrM.GID=Gr.GID) AS Size FROM NC_Groups Gr");
}

function get_group_name(&$sql, $gid)
{
    $gid=makeinteger($gid);
    $A=$sql->query("SELECT Name FROM NC_Groups WHERE GID=$gid");
    return $A[0]['Name'];
}

function remove_member(&$sql, $gid, $aid)
{
    $gid=makeinteger($gid);
    $aid=makeinteger($aid);
    $sql->query("DELETE FROM NC_Members WHERE GID=$gid AND AID=$aid");
}

function add_member(&$sql, $gid, $nick)
{
    $nick=makequotedstring($nick);
    $gid=makeinteger($gid);
    $sql->query("INSERT INTO NC_Members SET GID=$gid, AID=(SELECT Acc.AID FROM NC_Account Acc WHERE Nick=$nick)");
}

function remove_group(&$sql, $gid)
{
    $gid=makeinteger($gid);
    $sql->query("DELETE FROM NC_Members WHERE GID=$gid");
    $sql->query("DELETE FROM NC_Groups WHERE GID=$gid");
}

function add_group(&$sql, $Name, $Desc)
{
    $Name=makequotedstring($Name);
    $Desc=makequotedstring($Desc);
    $sql->query("INSERT INTO NC_Groups VALUES(NULL, $Name, $Desc)");
}


?>