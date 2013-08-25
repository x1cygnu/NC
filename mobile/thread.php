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

ForcePlayer($sql, $H, "thread.php");

$menuselected="Forum";
include("mobile/part/mainmenu.php");

$H->AddStyle("news.css");


get("th",'integer');
get("f",'integer');

get("e",'integer');
if (exists($GET['e']))
{
    forum_remove_post($sql, $GET['e']);
}



post("post",'string');
if ($POST['post']=='Post new thread')
{
    post("tname","string");
    post("mess","string");
    if (!double_post($POST["tname"] . $POST["mess"]))
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
        forum_new_post($sql,$GET['th'],$POST['mess']);
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
    post("mess","string");
    if (strlen($POST['mess'])<3)
	{
	    $H->Insert(new Error("Post to short"));
	    $H->Draw();
	    die;
	}
    $GET['th']=$POST['th'];
    if (!double_post($POST["mess"] . $POST['th']))
    {
        forum_new_post($sql,$GET['th'],$POST['mess']);
    }
    else
	$H->Insert(new Error("Double post"));
    $GET['f']=forum_count_posts($sql,$GET['th'])-25;
}

if ($POST['post']=='Post modification')
{
    post("p","integer");
    post("mess","string");
    if (!exists($POST['p']))
	{
	    $H->Insert(new Error("Post not specified"));
	    $H->Draw();
	    die;
	}
    $old=forum_get_post($sql, $POST['p']);
    if (!double_post($POST['mess'] . $POST['p']))
    {
        $U=forum_edit_post($sql, $POST['p'], $POST['mess']);
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
    post("mess","string");
    if (!exists($POST['p']))
	{
	    $H->Insert(new Error("Post not specified"));
	    $H->Draw();
	    die;
	}
    $old=forum_get_post($sql, $POST['p']);
    if (!double_post($POST['mess'] . $POST['p']))
        forum_censure_post($sql, $POST['p'], $POST['mess']);
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

if (exists($GET['f']))
    $P=forum_list_thread($sql, $GET['th'], $GET['f']);
else
    $P=forum_open_thread($sql, $GET['th']); //more advanced opening
    
$Here=forum_get_html_thread($sql, $GET['th']);
$Up=forum_get_html_section($sql, $Here['SectID']);
$H->Insert(new Link("forum.php"," Top "));
$H->Insert(new Link("forum.php?s={$Up['SectID']}"," Up "));
$H->Br();
$H->Insert(" posts " . ($P['from']+1) . '-' . $P['to']);
if ($P['special']===true)
    $H->Insert(" (Unread part)");
$H->Br();
$H->Insert("<b>{$Here['Name']}</b>");
$H->Br();
$H->Insert("<i>{$Here['Description']}</i>");




$T=new Table();

$nopages=ceil($P['total']/25.0);

if ($P['from']>0)
    {
	$prev=max(0,$P['from']-25);
	$T->Insert(1,1,new Link("thread.php?th={$GET['th']}&f=$prev","<< Previous "));
    }

if ($nopages<15)
{
    for ($p=0; $p<$nopages; ++$p)
	{
	$pg=$p*25;
	$T->Insert(1,1,new Link("thread.php?th={$GET['th']}&f=$pg"," $pg "));
	}
}
else
{
    for ($p=0; $p<6; ++$p)
	{
	$pg=$p*25;
	$T->Insert(1,1,new Link("thread.php?th={$GET['th']}&f=$pg"," $pg "));
	}
    $T->Insert(1,1," ... ");
    for ($p=$nopages-6; $p<$nopages; ++$p)
	{
	$pg=$p*25;
	$T->Insert(1,1,new Link("thread.php?th={$GET['th']}&f=$pg"," $pg "));
	}
}

if ($P['to']<$P['total'])
{
	$next=$P['to'];
	$T->Insert(1,1,new Link("thread.php?th={$GET['th']}&f=$next"," Next >>"));
}

$i=2;

$Write=forum_check_thread_permission($sql, $GET['th'], "Write");
$Edit=forum_check_thread_permission($sql, $GET['th'], "Modify");
$Delete=forum_check_thread_permission($sql, $GET['th'], "Delete");

foreach ($P as $K => $Post)
{
    if ($K==="from" or $K==="to" or $K==="special" or $K==="total")
	continue;
    $T->Insert(1,$i,DecodeTime($Post['Time']) . " ");
    $T->Insert(1,$i,new Link("post.php?pm={$Post['AID']}",$Post['Nick']));
//    $T->Insert(1,$i,$Post['Nick']);
    if ($Post['Author']==$_SESSION['AID'] and $Post['PstID']==$Here['Newest'])
    {
	$T->Insert(2,$i,new Link("post.php?p={$Post['PstID']}","E"));
	$T->Insert(2,$i,new Link("thread.php?th={$GET['th']}&f={$GET['f']}&e={$Post['PstID']}","X"));
    }
    if ($Post['Censure']=="")
    {
        $T->Insert(1,$i+1,decode($Post['Text']));
	$T->SetClass(1,$i,'n4');
	$T->SetClass(1,$i+1,'nrm');
	if ($Write or $Edit or $Delete)
        {
	    if ($Write)
	    {
	    $T->Insert(2,$i,new Link("post.php?th={$GET['th']}&q={$Post['PstID']}","Q"));
	    }
	    if ($Edit)
	    {
	    $T->Insert(2,$i,new Link("post.php?p={$Post['PstID']}","E"));
	    $T->Insert(2,$i,new Link("post.php?pc={$Post['PstID']}","C"));
	    }
	    if ($Delete)
		$T->Insert(2,$i,new Link("thread.php?th={$GET['th']}&f={$GET['f']}&e={$Post['PstID']}","X"));
	}
	$T->Join(1,$i+1,2,1);
	$i+=2;
    }
    else
	{
	    $T->SetClass(1,$i,'n2');
	    $T->Insert(2,$i,"Censured");
	    ++$i;
	}
}

$T->Join(1,1,2,1);


if ($Write)
{
    $H->Insert(new Link("post.php?th={$GET['th']}","<CENTER>Answer</CENTER>"));
}
$H->Insert($T);
if ($Write)
{
    $H->Insert(new Link("post.php?th={$GET['th']}","<CENTER>Answer</CENTER>"));
}

$H->Draw();
CloseSQL($sql);
?>
