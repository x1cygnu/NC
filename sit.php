<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");

include_once("internal/account.php");


session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Account Sitting agreement";

$sql=&OpenSQL($H);

get("stop","integer");
if ($GET['stop']==1)
{
    account_sit_free($sql, $_SESSION['SitPID'], $_SESSION['AID']);
    $_SESSION['SitPID']=0;
}


include("part/mainmenu.php");

ForceActivePlayer($sql, $H, "empty.php");
ForceFrozen($sql, $H);

$AID=player_get_sitted($sql, $_SESSION['PID']);




if ($GET['stop']==1)
{
    $H->Insert(new Info("You are about to stop sitting " . account_get_name_from_pid($sql, $_SESSION['SitPID'])));
    $H->Insert(new Link("sit.php?stop=2","Please confirm"));
}
elseif ($GET['stop']==2)
    $H->Insert(new Info("You are no longer sitting anyone"));

$demandingending=false;
post("signend","string");
if ($POST['signend']=='end Account Sitting agreement')
{
    if ($_POST['signedendconf']=='do as I say') {
	account_sit_free($sql, $_SESSION['PID'], $AID);
	$H->Insert(new Info("Account Sitting agreement ended"));
	$AID=player_get_sitted($sql, $_SESSION['PID']);

    }
    else
    {
	$H->Insert(new Info("Send your requrest again for confirmation"));
	$demandingending=true;
    }
}

$mayBeSitted=player_may_be_sitted($sql, $_SESSION['PID']);
$maysit=false;
post("sign","string");
post("player","string");
if ($POST['sign']=='sign Account Sitting agreement')
{
if (!$mayBeSitted) {
    $H->Insert(new Error("You exceeded your limits and cannot be sitted any more"));
}
else
{
$sit=account_get_sit_from_nick($sql, $POST['player']);
if (!isset($sit))
    $H->Insert(new Error("Player not found"));
elseif ($_SESSION['AID']==$sit['AID'])
    $H->Insert(new Error("You cannot sit yourself"));
elseif ($sit['PID']==0)
    $H->Insert(new Error("Person is not currently playing Northern Cross under that nick"));
elseif ($sid['SitPID']!=0)
    $H->Insert(new Error("Player is currently sitting someone else"));
else {
    $maysit=true;
    post("playera","string");
    if ($POST['playera']==$POST['player']) {
	account_sit($sql, $_SESSION['PID'], $sit['AID']);
	$H->Insert(new Info("Account Sitting agreement signed"));
    }
    else
	$H->Insert(new Info("Chosen player may sit your account right away.<br>Repeat your request for confirmation"));
}
}
}

$F=new Form("sit.php",true);
$T=new Table();
$T->sClass='block';
$T->Insert(1,1,"Account Sitting agreement");
$T->Insert(1,2,"Basic rules");
$T->Insert(2,2,
"Once per round you are allowed<br>to choose other active player<br>to play your account while you are absent.<br>
The player will have<br>limited access to your account.<br>Do not share your password with him!<br>
<br>You may choose the fortunate player here<br><br>
Neither Northern Cross administration,<br>nor any third player is responsible<br>for what your sitter do with your account.
");
$T->Insert(1,3,"Player");
if ($AID==0)
{
    $T->Insert(2,3,new Input("text","player",$POST['player'],"text"));
    $T->Insert(1,4,new Input("submit","sign","sign Account Sitting agreement","smbutton"));
}
else
{
    $Name=account_get_name($sql, $AID);
    $T->Insert(2,3,"You are currently sitted by $Name");
    $T->Insert(1,4,new Input("submit","signend","end Account Sitting agreement","smbutton"));
}
$T->Join(1,4,2,1);
$H->AddStyle("forum.css");
$T->Join(1,1,2,1);
$T->aRowClass[1]='title';
$T->SetClass(1,2,"legend");
$T->SetClass(2,2,"forumtext");
$T->SetClass(1,3,"legend");
$T->SetClass(1,4,"legend");
$F->Insert($T);
if ($maysit)
    $F->Insert(new Input("hidden","playera",$POST['player']));
if ($demandingending)
    $F->Insert(new Input("hidden","signedendconf",'do as I say'));    
$H->Insert($F);


//////////////////////////////////////
// active sitting
//////////////////////////////////////

if ($_SESSION['SitPID']>0)
{
$H->Insert("You are currently sitting");
$H->Br();
$H->Insert(new Link("pinfo.php?id=" . $_SESSION['SitPID'],'<b>' . account_get_name_from_pid($sql, $_SESSION['SitPID']) . '</b>'));
$H->Br();
$H->Br();
$H->Insert(new Link("sit.php?stop=1","Click here to stop sitting"));
}

$H->Draw();
CloseSQL($sql);
?>
