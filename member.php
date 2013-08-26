<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/player.php");
include_once("internal/planet.php");
include_once("internal/alliance.php");
include_once("internal/fleet.php");
include_once("internal/hint.php");

session_start();

get("id","integer");
get("i","integer");

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Alliance member details";

$sql=&OpenSQL($H);
ForceActivePlayer($sql, $H, "member.php");
ForceFrozen($sql, $H);
ForceNoSitting($sql, $H, $_SESSION['PID']);


$H->AddStyle("alliance.css");
$H->AddStyle("detail.css");
$H->AddStyle("news.css");
$H->AddStyle("planets.css");
$H->AddStyle("hint.css");

$menuselected="Alliance";
include("part/mainmenu.php");

$htag=player_get_tag($sql,$_SESSION['PID']);
if (!isset($GET['id'])) {
    $GET['id']=alliance_get_member_pid_by_index($sql, $GET['i'], $htag);
    if (!$GET['id']>0)
	$H->Insert(new Error("No player under chosen index"));
}


$ptag=player_get_tag($sql,$GET['id']);
$admin=account_get_admin_from_pid($sql, $GET['id']);
if ($htag=="" or $ptag!=$htag or $admin==1)
{
    $H->Insert(new Info("Player information unavailable"));
    $H->Draw();
    die;
}

$Ps=planet_list($sql, $GET['id']);

$Sf=alliance_sieging_fleets($sql, $GET['id']);
$Tf=fleet_get_intransit($sql, $GET['id']);

$T=new Table();
$T->SetCols(18);
$PName=account_get_name_from_pid($sql,$GET['id']);
if (!isset($GET['i'])) {
    $T->Insert(1,1,"Planets of $PName");
    $T->Join(1,1,17,1);
} else {
    $T->Insert(3,1,"Planets of $PName");
    $T->Join(1,1,2,1);
    if ($GET['i']>0)
	$T->Insert(1,1,new Link("member.php?i=" . ($GET['i']-1),"<< Previous"));
    $T->Insert(14,1,new Link("member.php?i=" . ($GET['i']+1),"Next >>"));
    $T->Join(3,1,11,1);
    $T->Join(14,1,5,1);
}
$T->aRowClass[1]='title';
$T->aRowClass[2]='legend';
$T->sClass='block';
$T->Insert(1,2,"No");
$T->Insert(2,2,"Name");
$T->Insert(3,2,"Type");
$T->Insert(4,2,"Pop");
$T->Insert(5,2,"Tx");
$T->Insert(6,2,"Frm");
$T->Insert(7,2,"Fct");
$T->Insert(8,2,"Cyb");
$T->Insert(9,2,"Lab");
$T->Insert(10,2,"Ref");
$T->Insert(11,2,"SB");
$T->Insert(12,2,"Vpr");
$T->Insert(13,2,"Int");
$T->Insert(14,2,"Fr");
$T->Insert(15,2,"Bs");
$T->Insert(16,2,"Drn");
$T->Insert(17,2,"CS");
$T->Insert(18,2,"Tr");
for ($i=4; $i<=18; ++$i)
    $T->SetClass($i,2,"numb");
foreach($Ps as $n => $P)
{
    $i=$n+1;
    $T->Insert(1,$i+2,"{$i}");
    $i+=2;
    $T->Insert(2,$i,"{$P['Name']} {$P['Ring']}");
    $T->Insert(4,$i,"{$P['Population']}");

    $T->Insert(3,$i,MakeHint($P['TypeName'],$P['Description'] . "<br>" .
	    "<b>Growth: " . $P['Growth'] . "%, Science: " . $P['Science'] .
	"%<br>Culture: " . $P['Culture'] . "%, Production: " . $P['Production'] .
	"%<br>Toxic vulnerability: " . $P['ToxicStability'] . "%<br>" .
	" Starbase: OV: " . $P['Attack'] . "%, DV: " . $P['Defense'] . "%<br>Building base cost: " . $P['BaseCost'] .
	"<br>Culture requirement: " . ($P['CultureSlot']>0?"Yes":"No") . "<br>Technology requirement: " . ($planet['TechReq']>0?$P['TechName']:'none') . '</b>'));


//    $T->Insert(3,$i,"{$P['TypeName']}");
    $STx=floor($P['STx']/1000);
    $T->Insert(5,$i,"" . $STx);
    $T->Insert(6,$i,"{$P['Farm']}");
    $T->Insert(7,$i,"{$P['Factory']}");
    $T->Insert(8,$i,"{$P['Cybernet']}");
    $T->Insert(9,$i,"{$P['Lab']}");
    $T->Insert(10,$i,"{$P['Refinery']}");
    $T->Insert(11,$i,"{$P['Starbase']}");
    $T->Insert(12,$i,"{$P['Vpr']}");
    $T->Insert(13,$i,"{$P['Int']}");
    $T->Insert(14,$i,"{$P['Fr']}");
    $T->Insert(15,$i,"{$P['Bs']}");
    $T->Insert(16,$i,"{$P['Drn']}");
    $T->Insert(17,$i,"{$P['CS']}");
    $T->Insert(18,$i,"{$P['Tr']}");    

    if ($P['FleetOwner']==0 or $P['FleetOwner']==$P['Owner'])
    {
    $T->SetClass(1,$i,'legend');
    $T->SetClass(2,$i,'legend');
    $T->SetClass(3,$i,'legend');
    $T->SetClass(4,$i,'p');
    $T->SetClass(5,$i,'tx');
    $T->SetClass(6,$i,'l');
    $T->SetClass(7,$i,'l');
    $T->SetClass(8,$i,'l');
    $T->SetClass(9,$i,'l');
    $T->SetClass(10,$i,'l');
    $T->SetClass(11,$i,'pl');
    $T->SetClass(12,$i,'pl');
    $T->SetClass(13,$i,'pl');
    $T->SetClass(14,$i,'pl');
    $T->SetClass(15,$i,'pl');
    $T->SetClass(16,$i,'pl');
    $T->SetClass(17,$i,'e');
    $T->SetClass(18,$i,'e');
    }
    else
    $T->aRowClass[$i]='siegedplanet';
    
}
++$i;
$T->Insert(1,$i,"Sieging fleets");
$T->aRowClass[$i]='title';
$T->Join(1,$i,18,1);

foreach ($Sf as $P)
{
    ++$i;
    $T->aRowClass[$i]='siegedplanet';
    $T->Insert(2,$i,"{$P['Name']} {$P['Ring']}");
    $T->Insert(4,$i,"{$P['Population']}");
    $STx=floor($P['STx']/1000);
    $T->Insert(5,$i,"" . $STx);
    $T->Insert(11,$i,"{$P['Starbase']}");
    $T->Insert(12,$i,"{$P['Vpr']}");
    $T->Insert(13,$i,"{$P['Int']}");
    $T->Insert(14,$i,"{$P['Fr']}");
    $T->Insert(15,$i,"{$P['Bs']}");
    $T->Insert(16,$i,"{$P['Drn']}");
    $T->Insert(17,$i,"{$P['CS']}");
    $T->Insert(18,$i,"{$P['Tr']}");    
}

++$i;
$T->Insert(1,$i,"Fleets in transit");
$T->aRowClass[$i]='title';
$T->Join(1,$i,18,1);

foreach ($Tf as $P)
{
    ++$i;
    $T->Insert(2,$i,"{$P['Name']} {$P['Ring']}");
		$T->Insert(3,$i,missionName($P['Mission']));
    $T->Insert(4,$i,DecodeTime($P['ETA']));    
    $T->Insert(12,$i,"{$P['Vpr']}");
    $T->Insert(13,$i,"{$P['Int']}");
    $T->Insert(14,$i,"{$P['Fr']}");
    $T->Insert(15,$i,"{$P['Bs']}");
    $T->Insert(16,$i,"{$P['Drn']}");
    $T->Insert(17,$i,"{$P['CS']}");
    $T->Insert(18,$i,"{$P['Tr']}");    
    $T->Join(4,$i,8,1);
}

$H->Insert($T);

$Inc=news_list_incommings($sql,$GET['id']);

$T=new Table();
$T->Insert(1,1,"Incoming fleets");
$T->aRowClass[1]='title';
$s=sprintf("Time (GMT%+d)",$_SESSION['TimeZone']);
$T->Insert(1,2,$s);
$T->Insert(2,2,"Message");
$T->SetClass(2,2,"newstext");
$T->aRowClass[2]="legend";
$T->sClass='block';

$T->Join(1,1,2,1);
$i=2;
foreach ($Inc as $entry)
{
    ++$i;
    $T->Insert(1,$i,DecodeTime($entry['Time']));
    $T->SetClass(1,$i,"newstype" . $entry['Type']);
    $T->Insert(2,$i,$entry['Text']);
    $T->SetClass(2,$i,"normtext");
}
$H->Insert($T);

$H->Insert(new Link("pinfo.php?id={$GET['id']}","Public info"));

include("part/mainsubmenu.php");

$H->Draw();
CloseSQL($sql);
?>
