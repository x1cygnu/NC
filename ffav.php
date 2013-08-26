<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/forumfunc.php");

session_start();


global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");

$H->sTitle="Northern Cross";

$sql=&OpenSQL($H);

ForcePlayer($sql, $H, "empty.php");

$H->AddStyle("forum.css");
$forummenuselected='fav';

include("part/mainmenu.php");
include("part/forummenu.php");

get("add","integer");
if (isset($GET['add']))
    forum_add_favourite($sql, $_SESSION['AID'], $GET['add']);
get("rem","integer");
if (isset($GET['rem']))
    forum_remove_favourite($sql, $_SESSION['AID'], $GET['rem']);

$T=new Table();
$T->sClass='forumtable block';
$T->SetCols(3);

include("part/forumtitle.php");

$T->Insert(1,2,"<p class='sectionname'>Favourite threads</p>");
$T->SetClass(1,2,'forumtitle');
$T->Join(1,1,3,1);
$T->Join(1,2,3,1);


$i=2;
$Ss=forum_favourite_threads($sql, $_SESSION['AID'], $_SESSION['PermTAG']);
foreach ($Ss as $S)
{
    ++$i;
    $T->Insert(1,$i,new Link('thread.php?th=' . $S['ThID'],'<p class="sectionname">'. $S['Name'] . '</p>'));
    $T->Insert(1,$i,'<p class="sectionsub">'. $S['Description'] . '</p>');
    $T->SetClass(1,$i,'forumdir');
    if ($S['Newest']>0)
	{
        $T->Insert(2,$i,"{$S['N']}");
	$T->Insert(2,$i,new Br());
        $TTString=DecodeTime($S['T']);
        $T->Insert(2,$i,"$TTString");
	}
    else
	$T->Insert(2,$i," ");
    if ($S['R']>=1)
	$T->SetClass(2,$i,'unrread');
    else
	$T->SetClass(2,$i,'rread');
    $T->Insert(3,$i,new Link("ffav.php?rem={$S['ThID']}","X"));
}

$H->Insert($T);
$H->Draw();
CloseSQL($sql);
?>
