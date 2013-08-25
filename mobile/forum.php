<?php

chdir('..');

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");

include_once("internal/forumfunc.php");

session_start();


$H = new HTML();
$H->AddStyle("default.css");


$sql=&OpenSQL($H); 

ForcePlayer($sql, $H, "forum.php");

$menuselected="Forum";
include("mobile/part/mainmenu.php");

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


if (exists($GET['s']))
{
    $H->Insert(new Link("forum.php"," Up "));
    if (forum_check_section_permission($sql, $GET['s'], 'New'))
    {
	$H->Insert(new Link("post.php?s={$GET['s']}"," New "));
    }
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
    $H->Br();
    $H->Insert("<b>{$Here['Name']}</b>");
    $H->Br();
    $H->Insert("<i>{$Here['Description']}</i>");
}
else
    $Ss=forum_sections($sql);



$H->Insert("<hr>");
foreach ($Ss as $S)
{
    $H->Br();
    if ($S['Locked']==1)
	$H->Insert("[Lock]");
    if ($S['R']>=1)
	$H->Insert("[New]");
    if (!exists($GET['s']))
	$H->Insert(new Link('forum.php?s=' . $S['SectID'],'<b>'. $S['Name'] . '</b>'));
    else
	$H->Insert(new Link('thread.php?th=' . $S['ThID'],'<b>'. $S['Name'] . '</b>'));
    $H->Br();
    $H->Insert('<i>'. $S['Description'] . '</i>');
}



$H->Draw();
CloseSQL($sql);
?>
