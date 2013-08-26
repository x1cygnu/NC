<?php
ob_start("ob_gzhandler");

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

ForcePlayer($sql, $H, "thread.php");

get("type","string");
get("th","integer");
if (!exists($GET['th'])) {
  post("th","integer");
  $GET['th']=$POST['th'];
}

if (!exists($GET['type'])) {
  $GET['type']=forum_get_thread_type($sql,$GET['th']);
}

if ($GET['type']=='dpub' or $GET['type']=='dpriv')
  $menuselected="Dipl";
else
  $menuselected="Forum";

$forummenuselected=$GET['type'];
include("./part/mainmenu.php");
include("./part/forummenu.php");

$H->AddStyle("forum.css");


get("f",'integer');

get("e",'integer');
if (exists($GET['e']))
{
    forum_remove_post($sql, $GET['e']);
}



if (PostControl(true))
    post("post",'string');
if ($POST['post']=='Post new thread')
{
    post("tname","string");
    post("messy","string");
    if (!double_post($POST["tname"] . $POST["messy"]))
    {
    if (isset($POST['tname']))
    {
	post("sect","integer");
	post("tname","string");
	post("tdesc","string");
	$GET['th']=forum_new_thread($sql,$POST['sect'],$POST['tname'],$POST['tdesc']); 
    }
    if ($GET['th']!=-1)
    {
        forum_new_post($sql,$GET['th'],$POST['messy']);
    }
    $GET['f']=forum_count_posts($sql,$GET['th'])-25;
    }
    else
    $H->Insert(new Error("Double post"));
}

if ($POST['post']=='Post answer')
{
    post("th","integer");
    if (!exists($POST['th']))
	{
	    $H->Insert(new Error("Thread not specified"));
	    $H->Draw();
	    die;
	}
    post("messy","string");
    if (strlen($POST['messy'])<3)
	{
	    $H->Insert(new Error("Post to short"));
	    $H->Draw();
	    die;
	}
    $GET['th']=$POST['th'];
    if (!double_post($POST["messy"] . $POST['th']))
    {
        forum_new_post($sql,$GET['th'],$POST['messy']);
    }
    else
	$H->Insert(new Error("Double post"));
    $GET['f']=forum_count_posts($sql,$GET['th'])-25;
}

if ($POST['post']=='Post modification')
{
    post("p","integer");
    post("messy","string");
    if (!exists($POST['p']))
	{
	    $H->Insert(new Error("Post not specified"));
	    $H->Draw();
	    die;
	}
    $old=forum_get_post($sql, $POST['p']);
    if (!double_post($POST['messy'] . $POST['p']))
    {
        $U=forum_edit_post($sql, $POST['p'], $POST['messy']);
	if ($U!="")
	    $H->Insert(new Error($U));
    }
    else
	$H->Insert(new Error("Double post"));
    $GET['th']=$old['ThID'];
    $GET['f']=forum_count_posts($sql,$GET['th'])-25;
}


if ($POST['post']=='Censure')
{
    post("p","integer");
    post("messy","string");
    if (!exists($POST['p']))
	{
	    $H->Insert(new Error("Post not specified"));
	    $H->Draw();
	    die;
	}
    $old=forum_get_post($sql, $POST['p']);
    if (!double_post($POST['messy'] . $POST['p']))
        forum_censure_post($sql, $POST['p'], $POST['messy']);
    else
	$H->Insert(new Error("Double post"));
    $GET['th']=$old['ThID'];
    $GET['f']=forum_count_posts($sql,$GET['th'])-25;
}

if ($GET['f']<0)
    $GET['f']=0;

if (!exists($GET['th']) or $GET['th']==0 or !forum_check_thread_permission($sql, $GET['th'], "Read"))
{
    $H->Insert(new Error("Incorrect thread specified"));
    $H->Draw();
    CloseSQL($sql);
    die;
}

$T=new Table();
$T->sClass='forumtable';
include("part/forumtitle.php");

if (exists($GET['f']))
    $P=forum_list_thread($sql, $GET['th'], $GET['f']);
else
    $P=forum_open_thread($sql, $GET['th']); //more advanced opening
    
$Here=forum_get_html_thread($sql, $GET['th']);
$Up=forum_get_html_section($sql, $Here['SectID']);
$T->aRowClass[2]='forumtitle';
    $T->Insert(1,2,"<h5>{$Here['Name']}</h5>");
    $T->Insert(1,2,"<h6>{$Here['Description']}</h6>");


$T->aRowClass[3]='legend';
if ($GET['type']=='dpriv')
    $T->Insert(1,3,new Link("forum.php?type=dpriv","Private Diplomacy"));
elseif ($GET['type']=='dpub')
    $T->Insert(1,3,new Link("forum.php?type=dpub","Public Diplomacy"));
else
    $T->Insert(1,3,new Link("forum.php","NC Forum"));
$T->Insert(1,3," > ");
$T->Insert(1,3,new Link("forum.php?s={$Up['SectID']}",$Up['Name']));
$T->Insert(1,3," > ");
$T->Insert(1,3,new Link("thread.php?th={$GET['th']}",$Here['Name']));
$T->Insert(1,3," > ");
$T->Insert(1,3," posts " . ($P['from']+1) . '-' . $P['to']);
if ($P['special']===true)
    $T->Insert(1,3," (Unread part)");

$T->aRowClass[4]='legend';
$nopages=ceil($P['total']/25.0);

if ($P['from']>0)
    {
	$prev=max(0,$P['from']-25);
	$T->Insert(1,4,new Link("thread.php?th={$GET['th']}&f=$prev","<< Previous "));
    }

if ($nopages<15)
{
    for ($p=0; $p<$nopages; ++$p)
	{
	$pg=$p*25;
	$T->Insert(1,4,new Link("thread.php?th={$GET['th']}&f=$pg"," $pg "));
	}
}
else
{
    for ($p=0; $p<6; ++$p)
	{
	$pg=$p*25;
	$T->Insert(1,4,new Link("thread.php?th={$GET['th']}&f=$pg"," $pg "));
	}
    $T->Insert(1,3," ... ");
    for ($p=$nopages-6; $p<$nopages; ++$p)
	{
	$pg=$p*25;
	$T->Insert(1,4,new Link("thread.php?th={$GET['th']}&f=$pg"," $pg "));
	}
}

if ($P['to']<$P['total'])
{
	$next=$P['to'];
	$T->Insert(1,4,new Link("thread.php?th={$GET['th']}&f=$next"," Next >>"));
}

$i=5;

$Write=forum_check_thread_permission($sql, $GET['th'], "Write");
$Moderate=forum_check_thread_permission($sql, $GET['th'], "Moderate");

foreach ($P as $K => $Post)
{
  if ($K==="from" or $K==="to" or $K==="special" or $K==="total")
    continue;
  if ($Post['TAG']!="")
    $T->Insert(1,$i,new Link("alliance.php?tag={$Post['TAG']}",'['.$Post['TAG'].']'));	
  $T->Insert(1,$i,new Link("post.php?pm={$Post['AID']}",$Post['Nick']));
  //    $T->Insert(1,$i,$Post['Nick']);
  $T->Insert(1,$i,new Br());
  $T->Insert(1,$i,DecodeTime($Post['Time']));
  $T->Insert(1,$i,new Br());
  if ($Post['Avatar']!="")
  {	
    $D=new Div();
    $D->Insert(new Image(makestring($Post['Avatar']),"Avatar"));
    $D->sStyle='text-align : center';
    $T->Insert(1,$i,$D);
  }
  if ($Post['Censure']=="")
  {
    $T->Insert(2,$i,decode($Post['Text']));
    $T->SetClass(2,$i,'forumtext');
    if ($Post['BackgroundSig']!="" and ThemeUseBackgroundImage())
    {
      $C=$T->Get(2,$i);
      $C->sStyle="background-image : url(" . htmlentities($Post['BackgroundSig']) . ")";
    }
  }
  else
  {
    $T->Insert(2,$i,"Post censured because it is against forum rules");
    $T->Insert(2,$i,new Br());
    $T->Insert(2,$i,"(" . decode($Post['Censure']) . ")");
    $T->Insert(2,$i,"<h6>For uncensure contact moderators through PM</h6>");
    $T->SetClass(2,$i,"censure");
  }
  $T->SetClass(1,$i,'legend author');
  if ($Write or $Moderate)
  {
    if ($Write and $Post['Censure']=="")
    {
      $T->Insert(3,$i,new Link("post.php?th={$GET['th']}&q={$Post['PstID']}","Q"));
    }
    if ($Moderate)
    {
      $T->Insert(3,$i,new Br());
      $T->Insert(3,$i,new Link("post.php?p={$Post['PstID']}","E"));
      $T->Insert(3,$i,'<br/>');
      $T->Insert(3,$i,new Link("post.php?pc={$Post['PstID']}","C"));
      $T->Insert(3,$i,'<br/>');
      $T->Insert(3,$i,new Link("thread.php?th={$GET['th']}&f={$GET['f']}&e={$Post['PstID']}","X"));
    }
  }
  if ($Post['Author']==$_SESSION['AID'] and $Post['PstID']==$Here['Newest'])
  {
    $T->Insert(3,$i,new Br());
    $T->Insert(3,$i,new Link("post.php?p={$Post['PstID']}","E"));
    $T->Insert(3,$i,new Br());
    $T->Insert(3,$i,new Link("thread.php?th={$GET['th']}&f={$GET['f']}&e={$Post['PstID']}","X"));
  }
  ++$i;
}

$T->Join(1,1,3,1);
$T->Join(1,2,3,1);
$T->Join(1,3,3,1);
$T->Join(1,4,3,1);
$T->SetClass(2,5,'forumdir forumtext');


if ($Write)
{
    $T->Insert(1,$i+1,new Link("post.php?th={$GET['th']}","Answer"));
}
else
{
    $T->Insert(1,$i+1,"You may not answer");
}

    $T->SetClass(1,$i+1,'legend');
    $T->Join(1,$i+1,3,1);
    $T->Join(1,$i,3,1);

$U=$T->Get(1,4);
$T->Set(1,$i,$U);
$T->SetClass(1,$i,'legend');

$H->Insert($T);
include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
