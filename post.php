<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");


include_once("internal/forumfunc.php");
include_once("internal/account.php");
include_once("internal/pm.php");
include_once("internal/help.php");


session_start();


$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Post message";

$sql=&OpenSQL($H);

ForcePlayer($sql, $H, "post.php");

$H->AddStyle("forum.css");

include("part/mainmenu.php");


get("s","integer");
get("th","integer");
get("p","integer");
get("pc","integer");
get("pm","integer");
get("help","string");
if (!exists($GET['th']) and !exists($GET['s']) and !exists($GET['p']) and !exists($GET['pc']) and !exists($GET['pm']) and !(exists($GET['help']) and $_SESSION['IsAdmin']))
{
    $H->Insert(new Error("Neither section, thread nor post specified"));
    $H->Draw();
    die;
}

$l=1;

$T=new Table();
$T->sClass='block';
include("part/forumtitle.php");
if (exists($GET['th']))
{
    get("q","integer");
    if ($GET['q']>0 and forum_check_post_permission($sql, $GET['q'], "Read") and forum_check_censure($sql, $GET['q']))
    {
	$Quotation=forum_get_post($sql, $GET['q']);
	$Text="[quote={$Quotation['Nick']}]{$Quotation['Text']}[/quote]";
    }
    else
	$Text="";
    $Here=forum_get_thread($sql, $GET['th']);
    $T->Insert(1,2,"<h5>{$Here['Name']}</h5>");
    $T->Insert(1,2,"<h6>{$Here['Description']}</h6>");
    $T->SetClass(1,2,'forumtitle');
    $Buton="Post answer";
    $l=3;
}
elseif (exists($GET['s']))
{
  //TODO: check permission for this section!
    $Here=forum_get_section($sql, $GET['s']);
    $T->Insert(1,2,"<h5>{$Here['Name']}</h5>");
    $T->Insert(1,2,"<h6>{$Here['Description']}</h6>");
    $T->SetClass(1,2,'forumtitle');
    $T->Insert(1,3,"Creating new thread");
    $T->aRowClass[3]='legend';
    $T->Insert(1,4,"Topic");
    $T->Insert(1,5,"Description");
    $T->SetClass(1,4,'legend');
    $T->SetClass(1,5,'legend');
    $T->Insert(2,4,new Input("text","tname","","text"));
    $T->Insert(2,5,new Input("text","tdesc","","text"));
    $T->Join(1,3,2,1);
    $Buton="Post new thread";
    $Text="";
    $l=6;
}
elseif (exists($GET['p']))
{
    $Pst=forum_get_post($sql, $GET['p']);
    $Text=$Pst['Text'];
    $T->Insert(1,2,"<h5>{$Pst['Author']}</h5>");
    $T->Insert(1,2,decode($Pst['Text']));
    $T->SetClass(1,2,'forumtable sublegend');
    $Buton="Post modification";
    $l=3;
}
elseif (exists($GET['pm']))
{
    if ($GET['pm']>0)
	$addr=account_get_name($sql,$GET['pm']);
    else
	$addr="";
    get("qpm","integer");
    $Text="";
    if (exists($GET['qpm']))
    {
	$Quotation=pm_user_get($sql, $GET['qpm'], $_SESSION['AID']);
	if (strlen($Quotation['Text'])>2)
	{
	    $Name=account_get_name($sql,$Quotation['From']);
	    $Text="[quote={$Name}]{$Quotation['Text']}[/quote]";
	}
    }
    $T->Insert(1,4,"To");
    $T->Insert(2,4,new Input("text","to",$addr,"text"));
    $T->SetClass(1,4,'legend');
    $T->Insert(1,5,"Topic");
    $T->Insert(2,5,new Input("text","topic","","text"));
    $T->SetClass(1,5,'legend');
    $Buton="Send";
    $l=6;
}
elseif (exists($GET['help']) and $_SESSION['IsAdmin'])
{
    if ($GET['help']!="_new")
	$V=help_get($sql,$GET['help']);
    $T->Insert(1,4,"Entry");
    $T->Insert(2,4,new Input("text","entry",$V['Page'],"text"));
    $T->SetClass(1,4,'legend');
    $T->Insert(1,5,"Description");
    $T->Insert(2,5,new Input("text","descr",$V['Description'],"text"));
    $T->SetClass(1,5,'legend');
    $Text=$V['Text'];
    $Buton="Help";
    $l=6;
}

if (exists($GET['pc']))
{
    $Pst=forum_get_post($sql, $GET['pc']);
    $Text=$Pst['censure'];
    $T->Insert(1,2,"<h5>{$Pst['Author']}</h5>");
    $T->Insert(1,2,decode($Pst['censure']));
    $T->SetClass(1,2,'forumtable sublegend');
    $Buton="Censure";
    $l=3;
    $T->insert(1,$l,"Comment");
}
else
    $T->insert(1,$l,"Message");
$T->SetClass(1,$l,'legend');
$T->Insert(2,$l,"<textarea class=\"text forumtext\" name=\"messy\" rows=\"25\" cols=\"80\">$Text</textarea>");
$T->Join(1,1,2,1);
$T->Join(1,2,2,1);
$T->Insert(1,$l+1,new Input("submit","post",$Buton,"smbutton"));
$T->Join(1,$l+1,2,1);
$T->SetClass(1,$l+1,'title');

$F=new Form('thread.php',true);
$F->Insert($T);
if (exists($GET['s']))
    $F->Insert(new Input("hidden","sect",$GET['s']));
elseif (exists($GET['th']))
    $F->Insert(new Input("hidden","th",$GET['th']));
elseif (exists($GET['p']))
    $F->Insert(new Input("hidden","p",$GET['p']));
elseif (exists($GET['pc']))
    $F->Insert(new Input("hidden","p",$GET['pc']));
elseif (exists($GET['pm']))
{
    $F->sTarget='pm.php';
    $F->sName=$F->sID="pm";
    $F->OnSubmit("if (document.pm.to.value=='') {alert('Missing addresat!'); return false;};");
}
elseif (exists($GET['help']) && $_SESSION['IsAdmin'])
{
    $F->sTarget='helplist.php';
}
$F->Insert(new Input("hidden","orderid",$_SESSION['PostCode']+1));
$H->Insert($F);

include("part/mainsubmenu.php");

$H->Draw();
CloseSQL($sql);
?>
