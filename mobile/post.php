<?php
chdir('..');

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
$H->AddStyle("default.css");


$sql=&OpenSQL($H);

ForcePlayer($sql, $H, "post.php");


include("mobile/part/mainmenu.php");


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

$F=new Form('thread.php',true);

if (exists($GET['th']))
{
    get("q","integer");
    if ($GET['q']>0 and forum_check_post_permission($sql, $GET['q'], "Read"))
    {
	$Quotation=forum_get_post($sql, $GET['q']);
	$Text="[quote={$Quotation['Nick']}]{$Quotation['Text']}[/quote]";
    }
    else
	$Text="";
    $Here=forum_get_thread($sql, $GET['th']);
    $F->Insert("<b>{$Here['Name']}</b>");
    $F->Br();
    $F->Insert("<i>{$Here['Description']}</i>");
    $Buton="Post answer";
}
elseif (exists($GET['s']))
{
    $Here=forum_get_section($sql, $GET['s']);
    $F->Insert("<b>{$Here['Name']}</b>");
    $F->Br();
    $F->Insert("<i>{$Here['Description']}</i>");
    $F->Br();
    $F->Insert("Topic");
    $F->Insert(new Input("text","tname","","text"));
    $F->Br();
    $F->Insert("Description");
    $F->Insert(new Input("text","tdesc","","text"));
    $Buton="Post new thread";
    $Text="";
}
elseif (exists($GET['p']))
{
    $Pst=forum_get_post($sql, $GET['p']);
    $Text=$Pst['Text'];
    $F->Insert("<b>{$Pst['Author']}</b>");
    $F->Br();
    $F->Insert(decode($Pst['Text']));
    $F->Br();
    $Buton="Post modification";
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
    $F->Insert("To");
    $F->Insert(new Input("text","to",$addr,"text"));
    $F->Br();
    $F->Insert("Topic");
    $F->Insert(new Input("text","topic","","text"));
    $Buton="Send";
    $l=6;
}

if (exists($GET['pc']))
{
    $Pst=forum_get_post($sql, $GET['pc']);
    $Text=$Pst['censure'];
    $F->Insert("<b>{$Pst['Author']}</b>");
    $F->Br();
    $F->Insert(decode($Pst['censure']));
    $H->Br();
    $Buton="Censure";
    $l=3;
    $F->Br();
    $F->insert("Comment");
    $F->Br();
}
else
{
    $F->Br();
    $F->insert("Message");
    $F->Br();
}
$F->Insert("<textarea name=\"mess\" rows=\"5\" cols=\"30\">$Text</textarea>");
$F->Br();
$F->Insert(new Input("submit","post",$Buton,"smbutton"));

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
    $F->OnSubmit("if (document.pm.topic.value=='') {alert('Missing topic!'); return false;};
if (document.pm.to.value=='') {alert('Missing addresat!'); return false;};");
}
$H->Insert($F);
$H->Draw();
CloseSQL($sql);
?>
