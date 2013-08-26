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

$forummenuselected='s';
include("part/mainmenu.php");
include("part/forummenu.php");

$Results=array();

post("s","string");
if ($POST['s']=="Search")
{
    post("anyWords","string");
    post("allWords","string");
    post("sentence","string");
    post("woutWords","string");
    post("woutSentence","string");
    post("author","string");
    
    $Subject="Text";
    $WhereQuery="";
    $QueryElements=0;
    
    if ($POST['anyWords']!="")
    {
	$WhereQueryAnyWords="";
	$QueryAnyWordsElements=0;
	
	$anyWords=explode(" ",$POST['anyWords']);
	foreach ($anyWords as $word)
	{
	    if ($word!="")
	    {
	    if ($QueryAnyWordsElements>0)
		$WhereQueryAnyWords.=" OR ";
	    $WhereQueryAnyWords.=$Subject . " LIKE " . makequotedstring('%'.$word.'%');
	    ++$QueryAnyWordsElements;
	    }
	}
	if ($QueryAnyWordsElements>1)
	    $WhereQueryAnyWords='('.$WhereQueryAnyWords.')';
	if ($QueryAnyWordsElements>0)	
	    {
	    $WhereQuery=$WhereQueryAnyWords;
	    $QueryElements=1;
	    }
    }

    if ($POST['allWords']!="")
    {
	$WhereQueryAllWords="";
	$QueryAllWordsElements=0;
	
	$allWords=explode(" ",$POST['allWords']);
	foreach ($allWords as $word)
	{
	    if ($word!="")
	    {
	    if ($QueryAllWordsElements>0)
		$WhereQueryAllWords.=" AND ";
	    $WhereQueryAllWords.=$Subject . " LIKE " . makequotedstring('%'.$word.'%');
	    ++$QueryAllWordsElements;
	    }
	}
	
	if ($QueryAllWordsElements>0)	
	    {
	    if ($QueryElements>0)
		$WhereQuery.=" AND ";
	    $WhereQuery.=$WhereQueryAllWords;
	    ++$QueryElements;
	    }
    }

    if ($POST['sentence']!="")
    {
	if ($QueryElements>0)
	    $WhereQuery.=" AND ";
	$WhereQuery.=$Subject." LIKE ".makequotedstring('%'.$POST['sentence'].'%');
	++$QueryElements;
    }

    if ($POST['woutSentence']!="")
    {
	if ($QueryElements>0)
	    $WhereQuery.=" AND ";
	$WhereQuery.=$Subject." NOT LIKE ".makequotedstring('%'.$POST['woutSentence'].'%');
	++$QueryElements;
    }

    if ($POST['woutWords']!="")
    {
	$WhereQueryNoWords="";
	$QueryNoWordsElements=0;
	
	$noWords=explode(" ",$POST['woutWords']);
	foreach ($noWords as $word)
	{
	    if ($word!="")
	    {
	    if ($QueryNoWordsElements>0)
		$WhereQueryNoWords.=" AND ";
	    $WhereQueryNoWords.=$Subject . " NOT LIKE " . makequotedstring('%'.$word.'%');
	    ++$QueryNoWordsElements;
	    }
	}
	
	if ($QueryNoWordsElements>0)	
	    {
	    if ($QueryElements>0)
		$WhereQuery.=" AND ";
	    $WhereQuery.=$WhereQueryNoWords;
	    ++$QueryElements;
	    }
    }

    if ($WhereQuery!="")
	$_SESSION['ForumSearchQuery']=' AND ' . $WhereQuery;
    else
      $_SESSION['ForumSearchQuery']='';

    $_SESSION['AuthorSearchQuery']=$POST['author'];

    if ($_SESSION['ForumSearchQuery']!='' or $_SESSION['AuthorSearchQuery']!='')
    {
	$Results=forum_search($sql, $_SESSION['AID'], $_SESSION['PermTAG'], $_SESSION['ForumSearchQuery'], $POST['author']);
    }
}

get("from","integer");
if (isset($GET['from']))
{
    if ($GET['from']<0)
	$GET['from']=0;
    $Results=forum_search($sql, $_SESSION['AID'], $_SESSION['PermTAG'], $_SESSION['ForumSearchQuery'], $_SESSION['AuthorSearchQuery'], $GET['from']);    
}


if (count($Results)>0)
{
    $i=2;    
    $T=new Table();
    $T->SetCols(2);
    $T->sClass='block forumtable';
    $T->aRowClass[1]='title';
    $T->Insert(2,1,"Search results");
    if ($GET['from']>0)
    {
	$NewFrom=$GET['from']-25;
	TrimDown($NewFrom,0);
	$T->Insert(1,1,new Link("fsearch.php?from=$NewFrom","<< "));
    }
    if (count($Results)==25)
    {
	$NewFrom=$GET['from']+25;
	$T->Insert(1,1,new Link("fsearch.php?from=$NewFrom"," >>"));
    }

    foreach ($Results as $Post)
    {
    $T->Insert(1,$i,new Link("post.php?pm={$Post['AID']}",$Post['Nick']));
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
    $T->Insert(2,$i,new Link("thread.php?th=".$Post['ThID'],''.$Post['Name']));
    $T->SetClass(2,$i,'legend forumdir');
    if ($Post['Censure']=="")
    {
        $T->Insert(2,$i+1,decode($Post['Text']));
	$T->SetClass(2,$i+1,'forumtext');
	if ($Post['BackgroundSig']!="" and ThemeUseBackgroundImage())
	{
	    $C=$T->Get(2,$i+1);
	    $C->sStyle="background-image : url(" . htmlentities($Post['BackgroundSig']) . ")";
	}
    }
    $T->SetClass(1,$i,'legend author');
    $T->Join(1,$i,1,2);
    $i+=2;
    }
    $H->Insert($T);
}


$F=new Form("fsearch.php",true);

$T=new Table();
$T->Insert(1,1,"Search parameters");
$T->Insert(1,2,"Any words");
$T->Insert(1,3,"All words");
$T->Insert(1,4,"Sentence");
$T->Insert(1,5,"Without words");
$T->Insert(1,6,"Without sentence");
$T->Insert(1,7,"Author");

$T->Insert(2,2,new Input("text","anyWords",$POST['anyWords'],"text"));
$T->Insert(2,3,new Input("text","allWords",$POST['allWords'],"text"));
$T->Insert(2,4,new Input("text","sentence",$POST['sentence'],"text"));
$T->Insert(2,5,new Input("text","woutWords",$POST['woutWords'],"text"));
$T->Insert(2,6,new Input("text","woutSentence",$POST['woutSentence'],"text"));
$T->Insert(2,7,new Input("text","author",$POST['author'],"text"));
$T->Insert(1,8,new Input("submit","s","Search","smbutton"));

$T->sClass='block';
for ($i=2; $i<=8; ++$i)
    $T->SetClass(1,$i,"legend");
$T->SetClass(1,1,"title");
$T->Join(1,1,2,1);
$T->Join(1,8,2,1);

$F->Insert($T);
$H->Insert($F);
$H->Draw();
CloseSQL($sql);
?>
