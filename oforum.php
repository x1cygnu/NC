<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");

include_once("internal/forumfunc.php");

session_start();


$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Forum";

$sql=&OpenSQL($H); 

ForcePlayer($sql, $H, "forum.php");

$menuselected="Forum";
include("./part/mainmenu.php");

$H->AddStyle("forum.css");



post("create","string");
if ($POST['create']==="Create")
{
    post("sectname","string");
    post("sectdesc","string");
    if (!double_post($POST['sectname'] . $POST['sectdesc']))
        forum_new_section($sql, $POST['sectname'], $POST['sectdesc']);
    else
	$H->Insert(new Error("Double post"));
}

get("umrk","integer");
if ($GET['umrk']==1)
{
    forum_unmark_all($sql, $_SESSION['AID']);
}

get("s","integer");
    
$T=new Table();
$T->sClass='forumtable block';

include("part/forumtitle.php");


$T->Insert(1,3,new Link("forum.php","NC forum"));

if (exists($GET['s']))
{
    $DelPerm=forum_check_section_permission($sql, $GET['s'], 'Delete');
    $LckPerm=forum_check_section_permission($sql, $GET['s'], 'Lock');
    get("the","integer");
    if ($GET['the']>0)
    {
	$DelAns=forum_remove_thread($sql, $GET['the']);
	if (exists($DelAns))
	    $H->Insert(new Error("{$DelAns}"));
	else
	    $H->Insert(new Info("Thread has been removed"));
    }
    get("thl","integer");
    if ($GET['thl']>0)
    {
	$DelAns=forum_lock_thread($sql, $GET['thl']);
	if (exists($DelAns))
	    $H->Insert(new Error("{$DelAns}"));
	else
	    $H->Insert(new Info("Thread has been locked"));
    }
    get("thu","integer");
    if ($GET['thu']>0)
    {
	$DelAns=forum_unlock_thread($sql, $GET['thu']);
	if (exists($DelAns))
	    $H->Insert(new Error("{$DelAns}"));
	else
	    $H->Insert(new Info("Thread has been unlocked"));
    }
    $Ss=forum_threads($sql,$GET['s']);
    $Here=forum_get_html_section($sql,$GET['s']);
    $T->Insert(1,3," > ");
    $T->Insert(1,3,new Link("forum.php?s={$GET['s']}","{$Here['Name']}"));
    $T->Insert(1,2,"<p class='sectionname'>{$Here['Name']}</p>");
    $T->Insert(1,2,"<p class='sectionsub'>{$Here['Description']}</p>");
}
else
{
    get("se","integer");
    if ($GET['se']>0)
    {
	if ($_SESSION['IsAdmin'])
	{
	    $DelAns=forum_remove_section($sql, $GET['se']);
	    $H->Insert(new Info("Section removed"));
	}
	else
	    $H->Insert(new Error("You have no permission to remove this section"));
    }


    $T->Insert(2,3,new Link("forum.php?umrk=1","Mark all read"));
    $Ss=forum_sections($sql);
}


$T->SetClass(1,2,'forumtitle');
$T->aRowClass[3]='legend';


$i=3;
foreach ($Ss as $S)
{
    ++$i;
    if (!exists($GET['s']))
	$T->Insert(1,$i,new Link('forum.php?s=' . $S['SectID'],'<p class="sectionname">'. $S['Name'] . '</p>'));
    else
	$T->Insert(1,$i,new Link('thread.php?th=' . $S['ThID'],'<p class="sectionname">'. $S['Name'] . '</p>'));
    $T->Insert(1,$i,'<p class="sectionsub">'. $S['Description'] . '</p>');
//    echo "({$S['Newest']}) >>{$S['N']}<<";
    if ($S['Locked']==1)
	$T->Insert(2,$i,"Locked");
    elseif ($S['Newest']>0)
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
    if ($DelPerm)
    {
	$T->Insert(3,$i,new Link("forum.php?s={$GET['s']}&the={$S['ThID']}","X"));
	$T->Insert(3,$i,new Br());
	}
    if ($LckPerm)
    {
	$T->Insert(3,$i,new Link("forum.php?s={$GET['s']}&th"
	    . ($S['Locked']?"u":"l") .
	    "={$S['ThID']}",($S['Locked']?"U":"L")));	
    }
    if ($_SESSION['IsAdmin'] and !exists($GET['s']))
	$T->Insert(3,$i,new Link("forum.php?se={$S['SectID']}","X"));
	
    
}

if (exists($GET['s']) and forum_check_section_permission($sql, $GET['s'], 'New'))
{
    $T->insert(2,3,new Link("post.php?s={$GET['s']}","New thread"));
}

$T->Join(1,1,2,1);
$T->Join(1,2,2,1);
$T->SetClass(1,3,"forumdir");
$H->Insert($T);

if (!isset($GET['s']) and $_SESSION['IsAdmin']==1)
{
    $F=new Form("forum.php",true);
    $F->Insert(new Input("text","sectname","","text"));
    $F->Insert(new Input("text","sectdesc","","text"));
    $F->Insert(new Input("submit","create","Create","smbutton"));
    $H->Insert($F);
}
include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
