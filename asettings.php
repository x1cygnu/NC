<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/account.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";

$H->sTitle="Northern Cross - Settings";

$sql=&OpenSQL($H);

ForcePlayer($sql, $H, "settings.php");

$menuselected="Settings";
include("part/mainmenu.php");

get("thm","string");
if ($GET['thm']!="")
{
    setcookie("NC_StyleTheme",$GET['thm'],time()+315360000);
    $_COOKIE['NC_StyleTheme']=$GET['thm'];
}

get("ajx","integer");
if (isset($GET['ajx']))
{
    setcookie("NC_Ajax",$GET['ajx'],time()+315360000);
    $_COOKIE['NC_Ajax']=$GET['ajx'];
    $_SESSION['Ajax']=$GET['ajx'];
}

$H->AddStyle("default.css");


get("cl","integer");
if ($GET['cl']==1)
{$_SESSION['ConfirmLaunch']=0; account_set_confirm_launch($sql, $_SESSION['AID'], 0);}
elseif ($GET['cl']==2)
{$_SESSION['ConfirmLaunch']=1; account_set_confirm_launch($sql, $_SESSION['AID'], 1);}

get("bg","integer");
if ($GET['bg']==1)
{$_SESSION['MapBackground']=0; account_set_map_background($sql, $_SESSION['AID'], 0);}
elseif ($GET['bg']==2)
{$_SESSION['MapBackground']=1; account_set_map_background($sql, $_SESSION['AID'], 1);}

get("hnt","integer");
if (isset($GET['hnt']))
{
    $_SESSION['Hint']=$GET['hnt'];
    account_set_hint($sql, $_SESSION['AID'], $_SESSION['Hint']);
}

get("mapX","integer");
get("mapY","integer");
get("mapR","integer");
if (isset($GET['mapX']) and isset($GET['mapY']) and isset($GET['mapR']))
{
    account_set_map_defaults($sql, $_SESSION['AID'], $GET['mapX'], $GET['mapY'], $GET['mapR']);
    $_SESSION['DefMapX']=$GET['mapX'];
    $_SESSION['DefMapY']=$GET['mapY'];
    $_SESSION['DefMapR']=$GET['mapR'];
}

post("update","string");
if ($POST['update']=="Update")
{
    post("tz","float");
    post("passwd","string");
    post("passwd2","string");
    post("email","string");
    post("avatar","string");
    post("bsig","string");
    $U=account_update($sql,$_SESSION['AID'],$_POST['tz'],$POST['passwd'],$POST['passwd2'],$POST['email'],$POST['avatar'],$POST['bsig']);
    if ($U=="")
	$H->Insert(new Info("Changes saved"));
    else
	$H->Insert(new Error($U));
}

post("r","string");
if ($POST['r']=="Resign")
{
    post("pass","string");
    $A=player_resign($sql,$_SESSION['PID'],$POST['pass']);
    if ($A!="")
	$H->Insert(new Error($A));
    else
    {
	$H->Insert(new Info("You have been resigned"));
	$H->Insert("Please relog to start new game or return to forums");
	account_logout($sql);
	$H->Draw();
	die;
    }
}

$T=new Table();
$T->sClass='block';
$T->Insert(1,1,"Settings");
$T->Insert(1,2,"Account ID");
$T->Insert(1,3,"Player ID");
$T->Insert(1,4,"Name");
$T->Insert(1,5,"Time zone");
$T->Insert(1,6,"Password");
$T->Insert(1,8,"E-mail");
$T->Insert(1,9,"Avatar");
$T->Insert(3,9,"be reasonable with size");
$T->Insert(3,9,new Br());
$T->Insert(1,10,"Background signature");
$T->Insert(3,10,"be subtle!");
$T->Insert(3,10,new Br());
$T->Insert(3,10,"May be shown under your text)");
$T->Insert(3,10,new Br());
$T->Insert(1,11,"Default map position");

for ($r=2; $r<=11; ++$r)
    $T->SetClass(1,$r,'legend');

$T->Insert(2,2,"{$_SESSION['AID']}");
$T->Insert(2,3,"{$_SESSION['PID']}");
$T->Insert(2,4,"{$_SESSION['nick']}");
if ($_SESSION['TimeZone']==round($_SESSION['TimeZone']))
    $Format="GMT%+.0f";
else
    $Format="GMT%+.1f";
$GGh=sprintf($Format,$_SESSION['TimeZone']);
$T->Insert(2,5,"{$GGh}");
$T->Insert(3,5,new Input("text","tz","{$_SESSION['TimeZone']}","text number"));
$T->Insert(2,6,"Repeat twice<br/>if changing");
$T->Insert(3,6,new Input("password","passwd","","text"));
$T->Insert(3,7,new Input("password","passwd2","","text"));
$U=account_get_email($sql,$_SESSION['AID']);
$T->Insert(2,8,"{$U}");
$T->Insert(3,8,new Input("text","email","{$U}","text"));
$U=account_get_avatar($sql,$_SESSION['AID']);
$T->Insert(2,9,new Image($U));
$T->Insert(3,9,new Input("text","avatar",htmlentities($U),"text"));
$U=account_get_background_sig($sql,$_SESSION['AID']);
$T->Insert(2,10,new Image($U));
$T->Insert(3,10,new Input("text","bsig",htmlentities($U),"text"));

$T->Insert(2,11,"X:{$_SESSION['DefMapX']} Y:{$_SESSION['DefMapY']} Range:{$_SESSION['DefMapR']}");
$T->Insert(3,11,"X:");
$T->Insert(3,11,new Input("text","mapX",''.$_SESSION['DefMapX'],"text number"));
$T->Insert(3,11," Y:");
$T->Insert(3,11,new Input("text","mapY",''.$_SESSION['DefMapY'],"text number"));
$T->Insert(3,11," R:");
$T->Insert(3,11,new Input("text","mapR",''.$_SESSION['DefMapR'],"text number"));

$T->Insert(1,12,new Input("submit","update","Update","smbutton"));
$T->Join(1,12,3,1);
$G=$T->Get(1,12);
$G->iHeight=40;

$T->Insert(1,13,"Launch<br/>confirmation");
$T->Insert(2,13,($_SESSION['ConfirmLaunch']?"Yes":"No"));
$T->Insert(3,13,($_SESSION['ConfirmLaunch']?
		new Link("settings.php?cl=1","Disable")
		    : new Link("settings.php?cl=2","Enable")
		    ));

$T->Insert(1,14,"Map background");
$T->Insert(2,14,($_SESSION['MapBackground']?"Yes":"No"));
$T->Insert(3,14,($_SESSION['MapBackground']?
		new Link("settings.php?bg=1","Disable")
		    : new Link("settings.php?bg=2","Enable")
		    ));

$T->Insert(1,15,"Hint position");
switch ($_SESSION['Hint'])
{
case 0: $T->Insert(2,15,"disabled"); break;
case 1: $T->Insert(2,15,"below"); break;
case 2: $T->Insert(2,15,"left"); break;
case 3: $T->Insert(2,15,"right"); break;
case 4: $T->Insert(2,15,"below-right"); break;
case 5: $T->Insert(2,15,"top of viewscreen"); break;
}
$T->Insert(3,15,new Link("settings.php?hnt=0","Disable")); $T->Insert(3,15," ");
$T->Insert(3,15,new Link("settings.php?hnt=1","Below")); $T->Insert(3,15," ");
$T->Insert(3,15,new Link("settings.php?hnt=2","Left")); $T->Insert(3,15," ");
$T->Insert(3,15,new Link("settings.php?hnt=3","Right")); $T->Insert(3,15,new Br());
$T->Insert(3,15,new Link("settings.php?hnt=4","Below-Right")); $T->Insert(3,15," ");
$T->Insert(3,15,new Link("settings.php?hnt=5","Top-of-viewscreen"));

for ($i=13; $i<=15; ++$i)
$T->SetClass(1,$i,'legend');
$T->Join(1,1,3,1);
$T->Join(1,6,1,2);
$T->Join(2,6,1,2);
$T->aRowClass[1]='title';

$u=14;

if (CheckActivePlayer($sql))
{
$T->Insert(1,$u+2,"Resign");
$T->Join(1,$u+2,3,1);
$T->aRowClass[$u+2]='title';

$T->Insert(1,$u+3,"Attention! You cannot undo the resign");
$T->Join(1,$u+3,3,1);

$T->Insert(1,$u+4,"Password");
$T->Insert(2,$u+4,new Input("password","pass","","text"));
$T->Insert(3,$u+4,new Input("submit","r","Resign","smbutton"));
$T->SetClass(1,$u+4,'legend');
}

$u=$u+5;

$T->Insert(1,$u,"Client (using cookies)");
$T->Join(1,$u,3,1);
$T->aRowClass[$u]='title';

++$u;

$T->Insert(1,$u,"Theme");
$sStylePrefix=htmlentities($_COOKIE['NC_StyleTheme']);
if ($sStylePrefix=="")
    $T->Insert(2,$u,"Dark");
else
    $T->Insert(2,$u,"" . $sStylePrefix);
$T->SetClass(1,$u,'legend');

$T->Insert(3,$u,new Link("settings.php?thm=dark","Dark (default)"));
$T->Insert(3,$u,new Br());
$T->Insert(3,$u,new Link("settings.php?thm=white","white"));

++$u;
$T->Insert(1,$u,"Ajax extention");
$T->Insert(2,$u,($_SESSION['Ajax']==1?"Use":"Do not use"));
$T->SetClass(1,$u,'legend');

$T->Insert(3,$u,new Link("settings.php?ajx=1","Use (default)"));
$T->Insert(3,$u,new Br());
$T->Insert(3,$u,new Link("settings.php?ajx=0","Do not use"));

++$u;

$T->Insert(1,$u,"Server");
$T->Join(1,$u,3,1);
$T->aRowClass[$u]='title';

$T->Insert(1,$u+1,"GMT");
$TimeStr=DecodeTime(EncodeNow(),0);
$T->Insert(2,$u+1,$TimeStr);
$T->SetClass(1,$u+1,'legend');

$T->Insert(1,$u+2,"$GGh");
$T->Insert(2,$u+2,DecodeTime(EncodeNow()));
$T->SetClass(1,$u+2,'legend');


$T->Insert(1,$u+3,"Trade update<br/>($GGh)");
$T->Insert(2,$u+3,strftime("%T",133200-2*date('Z')+$_SESSION['TimeZone']*3600));
$T->Insert(2,$u+3,new Br());
$T->Insert(2,$u+3,strftime("%T",176400-2*date('Z')+$_SESSION['TimeZone']*3600));
$T->SetClass(1,$u+3,'legend');

$F=new Form("settings.php",true);
$F->Insert($T);
//$F->Insert();


$H->Insert($F);

include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
