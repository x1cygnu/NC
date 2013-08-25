<?php

include_once("./constant.php");
include_once("./internal/common.php");
include_once("./internal/forumfunc.php");
//News semi-class

/*
0 - Announcements, news (yellow)
1 - PM	(blue)
2 - Incomming	(orange)
3 - Agreement	(green)
4 - Military victory (yellow-green)
5 - Military defeat (red)
*/

function news_set(&$sql, $pid, $text, $type, $PID=0, $Vpr=0, $Int=0, $Fr=0, $Bs=0, $Drn=0, $Tr=0, $Target=0)
{
    $text=makequotedstring($text);
    $pid=makeinteger($pid);
    $Now=EncodeNow();
    $PID=makeinteger($PID);
    $Vpr=makeinteger($Vpr);
    $Int=makeinteger($Int);
    $Fr=makeinteger($Fr);
    $Bs=makeinteger($Bs);
    $Drn=makeinteger($Drn);
    $Tr=makeinteger($Tr);
    $Target=makeinteger($Target);
    $sql->query("INSERT INTO NC_News VALUES(NULL, $pid, $Now, $text, $type, $PID, $Vpr, $Int, $Fr, $Bs, $Drn, $Tr, $Target)");
}

function news_clear_time(&$sql, $pid)
{
    $Now=EncodeNow();
    $pid=makeinteger($pid);
    $sql->query("DELETE FROM NC_News WHERE PID=$pid AND Time<$Now");
}

function news_broadcast(&$sql, $text)
{
    $text=makequotedstring(decode($text));
    $Now=EncodeNow();
    $sql->query("INSERT INTO NC_News SELECT NULL, A.PID, $Now, $text, 0, 0, 0, 0, 0, 0, 0, 0, 0 FROM NC_Account A WHERE A.PID>0");
}

function news_set_on_time(&$sql, $pid, $time, $text, $type, $IncPID=0, $IncVpr=0, $IncInt=0, $IncFr=0, $IncBs=0, $IncDrn=0, $IncTr=0, $IncTarget=0)
{
    $text=makequotedstring($text);
    $pid=makeinteger($pid);
    $time=makeinteger($time);
    $type=makeinteger($type);
    $IncPID=makeinteger($IncPID);
    $IncVpr=makeinteger($IncVpr);
    $IncInt=makeinteger($IncInt);
    $IncFr=makeinteger($IncFr);
    $IncBs=makeinteger($IncBs);
    $IncDrn=makeinteger($IncDrn);
    $IncTr=makeinteger($IncTr);
    $IncTarget=makeinteger($IncTarget);
    $sql->query("INSERT INTO NC_News VALUES(NULL, $pid, $time, $text, $type, $IncPID, $IncVpr, $IncInt, $IncFr, $IncBs, $IncDrn, $IncTr, $IncTarget)");    
}

function news_list(&$sql, $pid, $from)
{
    global $NewsPerPage;
    $from=makeinteger($from);
    $pid=makeinteger($pid);
    return $sql->query("SELECT * FROM NC_News WHERE PID=$pid ORDER BY Time DESC LIMIT $from, $NewsPerPage");
}

function news_list_incommings(&$sql, $pid)
{
    $from=makeinteger($from);
    $pid=makeinteger($pid);
    $Now=EncodeNow();
    return $sql->query("SELECT * FROM NC_News WHERE PID=$pid AND Type=2 AND Time>$Now ORDER BY Time ASC");

}

function news_count(&$sql, $pid)
{
    $J=$sql->query("SELECT count(*) AS J FROM NC_News WHERE PID=$pid");
    return $J[0]['J'];
}

function news_delete(&$sql, $pid, $nid)
{
    $sql->query("DELETE FROM NC_News WHERE PID=$pid AND NID=$nid");
}

function news_clear(&$sql, $pid)
{
    $sql->query("DELETE FROM NC_News WHERE PID=$pid");
}

?>