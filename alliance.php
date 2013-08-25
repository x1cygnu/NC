<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/player.php");
include_once("internal/alliance.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");
$H->AddStyle("alliance.css");

$H->sTitle="Northern Cross - Alliance";

$sql=&OpenSQL($H);

$menuselected="Alliance";

get("tag","string");
if (exists($GET['tag']))
{
    include("part/mainmenu.php");
    
    //Alliance public info & member list
$Alliance=alliance_get_all($sql,$GET['tag']);
if (isset($Alliance))
{
$T=new Table();
$T->sClass='block';
$Name=htmlentities($Alliance['Name']);
$T->Insert(1,1,"[{$Alliance['TAG']}] $Name");
$T->aRowClass[1]='title';
$T->Insert(1,2,"Additional info"); $T->Insert(2,2,htmlentities($Alliance['Descrption']));
$T->SetClass(1,2,'legend');
$T->Insert(1,3,"URL"); $T->Insert(2,3,new Link($Alliance['URL'],htmlentities($Alliance['URL']),"_blank"));
$T->SetClass(1,3,'legend');
$T->SetStyle(2,3,'width : 200pt;');
$T->Insert(1,4,"Number of members"); $T->Insert(2,4,"{$Alliance['NoMembers']}");
$T->SetClass(1,4,'legend');
$T->Insert(1,5,"Points"); $T->Insert(2,5,"{$Alliance['Points']}");
$T->SetClass(1,5,'legend');
$T->Insert(1,6,"Founder"); $name=account_get_name($sql,$Alliance['Founder']);
$T->SetClass(1,6,'legend');
$T->Insert(2,6,"{$name}");

$T->Join(1,1,2,1);
    $H->Insert($T);

$Ms=alliance_get_members($sql, $GET['tag']);
$T=new Table();
$T->sClass='block';
$T->Insert(1,1,"Members");
$T->aRowClass[1]='title';
$T->aRowClass[2]='legend';
$T->Insert(1,2,"#");
$T->Insert(2,2,"Nick");
$T->Insert(3,2,"Rank");
$T->Insert(4,2,"Points");
$T->Insert(5,2,"PL");
$T->Insert(6,2,"Culture");
$i=2;
$row=0;
foreach ($Ms as $M)
{
    ++$i;
    ++$row;
    $T->Insert(1,$i,"$row");
    if (CheckPlayer())
        $T->Insert(2,$i,new Link("pinfo.php?id={$M['PID']}","{$M['Nick']}"));
    else
	$T->Insert(2,$i,"{$M['Nick']}");
    if ($M['Rank']<999999)
        $T->Insert(3,$i,$M['Rank']);
    $T->Insert(4,$i,$M['Points']);
    $prc=floor((1-$M['PLRemain']/pl_points_for_lvl($M['PL']+1))*100);
    $T->Insert(5,$i,"{$M['PL']}-{$prc}%");
    $T->Insert(6,$i,"{$M['CultureLvl']}");
}
$T->Join(1,1,6,1);
$H->Insert($T);
get("b","integer");
if (isset($GET['b']))
    $H->Insert(new Link("detail.php?id={$GET['b']}","back to system detail"));
}
else
    $H->Insert(new Error("Alliance not found"));
    include("part/mainsubmenu.php");
    $H->Draw();
    die;
}

ForceActivePlayer($sql, $H, "alliance.php");

ForceFrozen($sql, $H);

include("part/mainmenu.php");

$Cnt=player_count_buildings($sql, $_SESSION['PID'], "Embassy");
if ($Cnt==0)
{
    $H->Insert(new Error("Embassy required"));
    $H->Insert("Construct an Embassy on any of your planets");
    include("part/mainsubmenu.php");
    $H->Draw();
    CloseSQL($sql);
    die;
}

post("tag","string");
post("Create","string");
if ($POST['Create']=='Create')
{
    if ($tag!="")
	$H->Insert(new Error("You are already in an alliance"));
    else
    {
	$W=alliance_create($sql, $POST['tag'], $_SESSION['PID']);
	if ($W!="")
	    $H->Insert(new Error($W));
	else
	    $H->Insert(new Info("Alliance succesfully created.<br/>You might want to fill in additional data"));
    }
}

$tag=player_get_tag($sql, $_SESSION['PID']);
get("a","string");
if (exists($GET['a']))
{
    if ($tag=="")
    {
//        echo "Accepting...";
	alliance_accept($sql, $GET['a'], $_SESSION['PID']);
	$tag=player_get_tag($sql, $_SESSION['PID']);
    }
    else
	$H->Insert(new Error("You are already in an alliance"));
}

if ($tag=="") //no alliance
{
    get("c","string");
    if (exists($GET['c']))
	alliance_deny($sql, $GET['c'], $_SESSION['PID']);



    $H->Insert(new Info("You are in no alliance yet"));
    $F=new Form("alliance.php",true);
    $T=new Table();
    $T->Insert(1,1,"Form new alliance");
    $T->SetClass(1,1,'title');
    $T->Insert(2,1,"Tag");
    $T->SetClass(2,1,'legend');
    $T->sClass='block';
    $T->iWidth=500;
    $T->Insert(3,1,new Input("text","tag","","text number"));
    $T->Insert(4,1,new Input("submit","Create","Create","smbutton"));
    $F->Insert($T);
    $H->Insert($F);
    $T=new Table();
    $T->Insert(1,1,"Join an alliance");
    $T->aRowClass[1]='title';
    $T->sClass='block';

    $Invs=invitation_for($sql, $_SESSION['PID']);
    if (count($Invs)>0)
    {
	$T->Insert(1,3,"Tag");
	$T->Insert(2,3,"Status");
	$T->aRowClass[3]='legend';
	$i=3;
        foreach ($Invs as $Inv)
	{
	    ++$i;
	    $T->Insert(1,$i,$Inv['TAG']);
	    switch ($Inv['Status'])
	    {
		case 0: case 2: $T->Insert(2,$i,"Declined");
				$T->SetClass(2,$i,"dec");
				break;
		case 1: $T->Insert(2,$i,"Pending");
			$T->SetClass(2,$i,"pend");
			$T->Insert(3,$i,new Link("alliance.php?c={$Inv['TAG']}","Decline"));
			$T->Insert(4,$i,new Link("alliance.php?a={$Inv['TAG']}","Accept"));
			break;
		case 3: $T->Insert(2,$i,"Accepted");
			$T->SetClass(2,$i,"acc"); break;
		default:
			$T->Insert(2,$i,"Unknown");
	    }
        }
	$T->Join(3,3,2,1);
	$T->Join(1,1,4,1);

    }
    $H->Insert($T);
    $H->Draw();
    CloseSQL($sql);
    die;
}

get("c","integer");
if (alliance_get_founder($sql,$tag)==$_SESSION['AID'] and exists($GET['c']))
{
    alliance_deny($sql, $tag, $GET['c']);
}


post("invite","string");
if (alliance_get_founder($sql,$tag)==$_SESSION['AID'] and $POST['invite']=='Invite')
{
//    echo "inviting...";
    post("nick","string");
    $pid=account_get_pid_from_nick($sql, $POST['nick']);
    $A=alliance_invite($sql, $_SESSION['PID'], $tag, $pid);
    if ($A!="")
        $H->Insert(new Error($A));
    else
	$H->Insert(new Info("Player invited"));
}


post("Update","string");
if (alliance_get_founder($sql,$tag)==$_SESSION['AID'] and $POST['Update']=='Update')
{
	post("name","string");
	post("desc","string");
	post("URL","string");
	post("founder","string");
	$founderAid=account_get_id($sql,$POST['founder']);
	alliance_update($sql,$tag,$POST['name'],$POST['desc'],$POST['URL']);
	alliance_set_founder($sql,$tag,$founderAid);
}


$Alliance=alliance_get_all($sql,$tag);
$T=new Table();
$T->sClass='block';
$Name=htmlstring($Alliance['Name']);
$T->Insert(1,1,"[{$Alliance['TAG']}] $Name");
$T->aRowClass[1]='title';
$T->Insert(1,2,"Additional info"); $T->Insert(2,2,htmlstring($Alliance['Descrption']));
$T->SetClass(1,2,'legend');
$T->Insert(1,3,"URL"); $T->Insert(2,3,new Link($Alliance['URL'],htmlstring($Alliance['URL']),"_blank"));
$T->SetClass(1,3,'legend');
$T->SetStyle(2,3,'width : 200pt;');
$T->Insert(1,4,"Number of members"); $T->Insert(2,4,"{$Alliance['NoMembers']}");
$T->SetClass(1,4,'legend');
$T->Insert(1,5,"Points"); $T->Insert(2,5,"{$Alliance['Points']}");
$T->SetClass(1,5,'legend');
$T->Insert(1,6,"Founder"); $name=account_get_name($sql,$Alliance['Founder']);
$T->SetClass(1,6,'legend');
if ($name!="")
    $T->Insert(2,6,$name);
else
    $T->Insert(2,6,"[[ resigned ]]");

$T->Join(1,1,2,1);

if ($Alliance['Founder']==$_SESSION['AID'])
{
    $F=new Form("alliance.php",true);
    $T->Insert(3,1,new Input("text","name","","text"));
    $T->Insert(3,2,new Input("text","desc","","text"));
    $T->Insert(3,3,new Input("text","URL","","text"));
    $T->Insert(3,6,new Input("text","founder","","text"));
    $T->Insert(3,7,new Input("submit","Update","Update","smbutton"));
    $F->Insert($T);
    
    $T=new Table();
    $T->sClass='block';
    $T->Insert(1,1,"Invitations");
    $T->aRowClass[1]='title';
    $T->Insert(1,2,"Invite");
    $T->SetClass(1,2,"legend");
    $T->Insert(2,2,new Input("text","nick","","text"));
    $T->Insert(3,2,new Input("submit","invite","Invite","smbutton"));
    $T->Join(1,1,3,1);
    invitation_purge($sql, $tag);
    $Invs=invitation_get($sql, $tag);
    if (count($Invs)>0)
    {
	$T->Insert(1,3,"Player");
	$T->Insert(2,3,"Status");
	$T->aRowClass[3]='legend';
	$i=3;
        foreach ($Invs as $Inv)
	{
	    ++$i;
	    $T->Insert(1,$i,account_get_name_from_pid($sql,$Inv['PID']));
	    switch ($Inv['Status'])
	    {
		case 0: case 2: $T->Insert(2,$i,"Declined");
				$T->SetClass(2,$i,"dec");
				break;
		case 1: $T->Insert(2,$i,"Pending");
			$T->SetClass(2,$i,"pend");
			$T->Insert(3,$i,new Link("alliance.php?c={$Inv['PID']}","Cancel"));
			break;
		case 3: $T->Insert(2,$i,"Accepted");
			$T->SetClass(2,$i,"acc"); break;
		default:
			$T->Insert(2,$i,"Unknown");
	    }
        }
    }
    $F->Insert($T);
    $H->Insert($F);
}
else
    $H->Insert($T);

$H->Insert(new Link("falliance.php","Alliance Territory Control"));
$T=new Table();
$T->Insert(1,1,"Alliance Members");
$T->aRowClass[1]='title';
$T->aRowClass[2]='legend';
$T->aRowClass[3]='sublegend';

$T->SetRows(3);
$T->SetCols(27);
$T->Join(1,1,27,1);

$T->Insert(1,2,"Name");
$T->Join(1,2,2,2);
$T->Insert(3,2,"Public info");
$T->Insert(3,3,"Rank");
$T->Insert(4,3,"Points");
$T->Insert(5,3,"PL");
$T->Join(3,2,3,1);

$T->Insert(6,2,"Race");
$T->Insert(6,3,"Gr");
$T->Insert(7,3,"Sc");
$T->Insert(8,3,"Cul");
$T->Insert(9,3,"Prd");
$T->Insert(10,3,"Sp");
$T->Insert(11,3,"Att");
$T->Insert(12,3,"Def");
$T->Join(6,2,7,1);

$T->Insert(13,2,"Sciences");
$T->Insert(13,3,"Sen");
$T->Insert(14,3,"Eng");
$T->Insert(15,3,"Wrp");
$T->Insert(16,3,"Phy");
$T->Insert(17,3,"Mat");
$T->Insert(18,3,"Urb");
$T->Join(13,2,6,1);

$T->Insert(19,2,"Economy");
$T->Insert(19,3,"PP/h");
$T->Insert(20,3,"Cul/h");
$T->Insert(21,3,"Sci/h");
$T->Insert(22,3,"TR");
$T->Insert(23,3,"Art");
$T->Insert(24,3,"Pop");
$T->Insert(25,3,"Colonies");
$T->Insert(26,3,"Planets");
$T->Join(19,2,8,1);

$T->Insert(27,2,"Idle");
$T->Join(27,2,1,2);

$Ms=alliance_get_members($sql, $tag);
$i=3;
$memberIndex=0;
foreach ($Ms as $M)
{
//  print_r($M);
    ++$i;
    $T->Insert(1,$i,new Link("post.php?pm={$M['AID']}","PM"));
    $T->SetClass(1,$i,'legend');
    $T->Insert(2,$i,new Link("member.php?id={$M['PID']}&i=" . $memberIndex++,"{$M['Nick']}"));
    $T->SetClass(2,$i,'nick');
    if ($M['Rank']<999999)
        $T->Insert(3,$i,$M['Rank']);
    $T->SetClass(3,$i,'p');
    $T->Insert(4,$i,$M['Points']);
    $T->SetClass(4,$i,'p');
    $prc=floor((1-$M['PLRemain']/pl_points_for_lvl($M['PL']+1))*100);
    $T->Insert(5,$i,"{$M['PL']}-{$prc}%");
    $T->SetClass(5,$i,'pl');

    $T->Insert(6,$i,sprintf("%+d",$M['Growth']));
    $T->Insert(7,$i,sprintf("%+d",$M['Science']));
    $T->Insert(8,$i,sprintf("%+d",$M['Culture']));
    $T->Insert(9,$i,sprintf("%+d",$M['Production']));
    $T->Insert(10,$i,sprintf("%+d",$M['Speed']));
    $T->Insert(11,$i,sprintf("%+d",$M['Attack']));
    $T->Insert(12,$i,sprintf("%+d",$M['Defence']));

    $T->Insert(13,$i,"{$M['Sensory']}");
    $T->SetClass(13,$i,'s');
    $T->Insert(14,$i,"{$M['Engineering']}");
    $T->SetClass(14,$i,'s');
    $T->Insert(15,$i,"{$M['Warp']}");
    $T->SetClass(15,$i,'s');
    $T->Insert(16,$i,"{$M['Physics']}");
    $T->SetClass(16,$i,'s');
    $T->Insert(17,$i,"{$M['Mathematics']}");
    $T->SetClass(17,$i,'s');
    $T->Insert(18,$i,"{$M['Urban']}");
    $T->SetClass(18,$i,'s');
    $T->SetClass(13+$M['SelectedScience'],$i,'s dev');
    $T->Insert(19,$i,sprintf("%d",$M['P']*Production($M['Production'])/100*(100+$M['TA'])/100));
    $T->SetClass(19,$i,'e');
    $T->Insert(20,$i,sprintf("%d",$M['C']*Culture($M['Culture'])/100*(100+$M['TA'])/100));
    $T->SetClass(20,$i,'e');
    $T->Insert(21,$i,sprintf("%d",$M['L']*Science($M['Science'])/100*(100+$M['TA'])/100));
    $T->SetClass(21,$i,'e');
    $T->Insert(22,$i,"{$M['TA']}");    
    $T->SetClass(22,$i,'e');
    $T->Insert(23,$i,"{$M['Short']}");    
    $T->SetClass(22,$i,'e');
    $T->Insert(24,$i,"{$M['Pop']}");    
    $T->SetClass(24,$i,'l');
    $T->Insert(25,$i,"{$M['PCulCount']} of {$M['CultureLvl']}");
    $T->SetClass(25,$i,'z');
    $T->Insert(26,$i,"{$M['PCount']}");
    $T->SetClass(26,$i,'z');

    $IdleT=EncodeNow()-$M['LastUpdate'];
    if ($IdleT<60) $Idle="{$IdleT}s";
    elseif ($IdleT<3600) {$IdleT=floor($IdleT/60); $Idle="{$IdleT}min";}
    elseif ($IdleT<3600*24) {$IdleT=floor($IdleT/3600); $Idle="{$IdleT}h";}
    else {$IdleT=floor($IdleT/(3600*24)); $Idle="{$IdleT}d";}
    
    $T->Insert(27,$i,$Idle);
    $T->SetClass(27,$i,'legend');
}
$H->Insert($T);
get("b","integer");
if (isset($GET['b']))
    $H->Insert(new Link("detail.php?id={$GET['b']}","back to system detail"));
include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
