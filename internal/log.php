<?php

$Log=array();

$Log_Results = array(
0 => "unknown", 
1 => "OK",
2 => "failed",
3 => "not found",
4 => "multiple",
5 => "Reg failure",
6 => "Must be admin",
7 => "already",
8 => "yourself",
9 => "no money",
10 => "no tag",
11 => "wrong amount",
12 => "no planets",
13 => "grab failed",
14 => "siege",
15 => "wrong energy",
16 => "slot req",
17 => "wrong owner",
18 => "inv object",
19 => "same owner",
20 => "no space",
21 => "inv pass",
22 => "not reg",
23 => "no change",
24 => "tech req",
25 => "active",
26 => "countdown",
27 => "not same tag",
28 => "permban",
29 => "ipban"
);

$Log_Commands= array(
0 => "unknown",
1 => "login",
2 => "su",
3 => "logout",
4 => "acc create",
5 => "ta invite",
6 => "ta accept",
7 => "ta decline",
8 => "alliance add",
9 => "alliance cr",
10 => "alliance upd",
11 => "alliance fnd",
12 => "alliance inv",
13 => "alliance accpt",
14 => "alliance deny",
15 => "round restart",
16 => "art buy",
17 => "art sell",
18 => "art use",
19 => "pp buy",
20 => "fight",
21 => "launch",
22 => "popkill",
23 => "conquer",
24 => "player cr",
25 => "spend all",
26 => "resign",
27 => "acc upd",
28 => "planet cr",
29 => "build",
30 => "build ship",
31 => "medal",
32 => "buy ship",
33 => "acc remove"
);

function log_get_result($i)
{
    global $Log_Results;
    return $Log_Results[$i];
}

function log_get_result_code($s)
{
    global $Log_Results;
    $v=array_search($s,$Log_Results);
    if ($v>0)
	return $v;
    else
	return 0;
}

function log_get_command($i)
{
    global $Log_Commands;
    return $Log_Commands[$i];
}

function log_get_command_code($s)
{
    global $Log_Commands;
    $v=array_search($s,$Log_Commands);
    if ($v>0)
	return $v;
    else
	return 0;
}

function log_try()
{
}


function log_finish()
{
}

function log_end()
{
}

function log_entry(&$sql, $command)
{
$I=func_num_args();
$Log[0]=EncodeNow();
if (isset($_SESSION['AID']))
    $Log[1]=$_SESSION['AID'];
else
    $Log[1]=0;
$Log[2]=log_get_command_code($command);
$S="SET Time={$Log[0]}, AID={$Log[1]}, Command={$Log[2]}, Result=0";
for ($i=1; $i<=$I-2; ++$i)
    $S.=", Arg{$i}=" . makequotedstring(func_get_arg($i+1));
$sql->query("INSERT INTO NC_Log " . $S);
$R=$sql->query("SELECT LAST_INSERT_ID() A");
return $R[0]['A'];
}

function log_update(&$sql, $LID, $argi, $argv)
{
    $argi=makeinteger($argi);
    $LID=makeinteger($LID);
    $sql->query("UPDATE NC_Log SET Arg{$argi}=" . makequotedstring($argv) .
		" WHERE LID=$LID");
}

function log_result(&$sql, $LID, $result)
{
    $I=makeinteger(log_get_result_code($result));
    $LID=makeinteger($LID);
    $sql->query("UPDATE NC_Log SET Result=$I WHERE LID=$LID");
}

function log_get_owner(&$sql, $LID)
{
    $LID=makeinteger($LID);
    $sql->query("SELECT AID FROM NC_Log WHERE LID=$LID");
}

function log_set_owner(&$sql, $LID, $AID)
{
    $LID=makeinteger($LID);
    $AID=makeinteger($AID);
    $sql->query("UPDATE NC_Log SET AID=$AID WHERE LID=$LID");
}


function log_retieve(&$sql, $from, $to, $players)
{
    return $sql->query("SELECT L.*, A.Nick FROM NC_Log L "
		    ."JOIN NC_Account A WHERE A.AID=L.AID");
}

?>
