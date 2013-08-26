<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");

include_once("internal/forumfunc.php");

session_start();


$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Diplomacy Forums";

$sql=&OpenSQL($H); 

ForcePlayer($sql, $H, "forum.php");


get("s","integer");
get("type","string");

if (!exists($GET['type']) and exists($GET['s']))
  $GET['type']=forum_get_section_type($sql,$GET['s']);

if ($GET['type']=='dpub' or $GET['type']=='dpriv')
  $menuselected="Dipl";
else
  $menuselected="Forum";

$forummenuselected=$GET['type'];
include("./part/mainmenu.php");
include("./part/forummenu.php");


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

    
$T=new Table();
$T->sClass='forumtable block';

include("part/forumtitle.php");

if ($GET['type']=='dpriv')
  $T->Insert(1,3,new Link("forum.php?type=dpriv","Private Diplomacy"));
elseif ($GET['type']=='dpub')
  $T->Insert(1,3,new Link("forum.php?type=dpub","Public Diplomacy"));
elseif ($GET['type']=='own')
  $T->Insert(1,3,new Link("forum.php?type=own","Owned"));
else 
  $T->Insert(1,3,new Link("forum.php","NC Forum"));

if (exists($GET['s']))
{
    $ModPerm=forum_check_section_permission($sql, $GET['s'], 'Moderate');
    get("the","integer");
    if ($GET['the']>0) {
      $H->Insert(new Info(new Link('forum.php?s='.$GET['s'].'&thee='.$GET['the'].'&type='.$GET['type'],
	'Confirm thread removal by clicking here')));
    }
    get("thee","integer");
    if ($GET['thee']>0)
    {
      $DelAns=forum_remove_thread($sql, $GET['thee']);
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
    get("see","integer");
    if (exists($GET['see']))
    {
	if ($_SESSION['IsAdmin'] or forum_get_section_owner($sql, $GET['see'])==$_SESSION['AID'])
	    forum_remove_section($sql, $GET['see']);
    }

    get("se","integer");
    if (exists($GET['se']))
    {
      if ($_SESSION['IsAdmin'] or forum_get_section_owner($sql, $GET['se'])==$_SESSION['AID']) {
	$H->Insert(new Info(new Link('forum.php?&see='.$GET['se'].'&type='.$GET['type'],
	  'Confirm section removal by clicking here')));
      }
    }

    $T->Insert(2,3,new Link("forum.php?umrk=1","Mark all read"));
    if ($GET['type']=='dpriv')
      $Ss=forum_private_user_sections($sql);
    elseif ($GET['type']=='dpub')
      $Ss=forum_public_user_sections($sql);
    elseif ($GET['type']=='own')
      $Ss=forum_owned_sections($sql, $_SESSION['AID']);
    else
      $Ss=forum_sections($sql);

}


$T->SetClass(1,2,'forumtitle');
$T->aRowClass[3]='legend';


$i=3;
foreach ($Ss as $S)
{
    ++$i;
    if (!exists($GET['s'])) {
      $T->Insert(1,$i,new Link('forum.php?type='.$GET['type'].'&s=' . $S['SectID'],'<p class="sectionname">'. $S['Name'] . '</p>'));
      if ($GET['se']==$S['SectID'])
	$T->SetClass(1,$i,'toberemoved');
    }
    else {
      $T->Insert(1,$i,new Link('thread.php?type='.$GET['type'].'&th=' . $S['ThID'],'<p class="sectionname">'. $S['Name'] . '</p>'));
      if ($GET['the']==$S['ThID'])
	$T->SetClass(1,$i,'toberemoved');
    }
    $T->Insert(1,$i,'<p class="sectionsub">'. $S['Description'] . '</p>');
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
    
    if (isset($GET['s']))
    {
	$T->Insert(3,$i,new Link("ffav.php?add={$S['ThID']}","F"));
	$T->Insert(3,$i,new Br());
    }
    if ($ModPerm)
    {
	$T->Insert(3,$i,new Link("forum.php?s={$GET['s']}&the={$S['ThID']}","X"));
	$T->Insert(3,$i,new Br());
	$T->Insert(3,$i,new Link("forum.php?s={$GET['s']}&th"
	    . ($S['Locked']?"u":"l") .
	    "={$S['ThID']}",($S['Locked']?"U":"L")));	
    }
    if (($_SESSION['IsAdmin'] or $_SESSION['AID']==$S['Owner']) and !exists($GET['s'])) {
      $T->Insert(3,$i,new Link("forum.php?type={$GET['type']}&se={$S['SectID']}","X"));
      $T->Insert(3,$i,new Br());
      $T->Insert(3,$i,new Link("sectionmanage.php?type={$GET['type']}&s={$S['SectID']}","M"));
    }
	
    
}

if (exists($GET['s']) and forum_check_section_permission($sql, $GET['s'], 'New'))
{
    $T->insert(2,3,new Link("post.php?s={$GET['s']}","New thread"));
}

$T->Join(1,1,3,1);
$T->Join(1,2,3,1);
$T->SetClass(1,3,"forumdir");
$H->Insert($T);

if (!isset($GET['s']) and $GET['type']=='own')
{
  $T=new Table();
  $T->sClass='block';
  $Cnt=forum_count_owned($sql, $_SESSION['AID']);
  $T->Insert(1,1,"new section (" . $Cnt . "/5)");
  $T->aRowClass[1]='title';
  $T->Insert(1,2,"Name:");
  $T->SetClass(1,2,'legend');
  $T->Insert(1,3,"Description:");
  $T->SetClass(1,3,'legend');
  $T->Insert(2,2,new Input("text","sectname","","text"));
  $T->Insert(2,3,new Input("text","sectdesc","","text"));
  $T->Insert(1,4,"By default only members of your alliance will be able to see this section");
  $T->Insert(1,5,new Input("submit","create","Create","smbutton"));
  $T->Join(1,4,4,1);
  $T->Join(1,1,4,1);
  $T->Join(1,5,4,1);

  $F=new Form("forum.php?type=own",true);
  $F->Insert($T);
  $H->Insert($F);
}
include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
