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

if (!exists($GET['s'])) {
  $H->Insert(new Error("Section not specified"));
  include("part/mainsubmenu.php");
  $H->Draw();
  die;
}

include_once("internal/forumfunc.php");
if ($_SESSION['AID']!=forum_get_section_owner($sql, $GET['s']) and $_SESSION['IsAdmin']==0) {
  $H->Insert(new Error("You are not owning this section"));
  include("part/mainsubmenu.php");
  $H->Draw();
  die;
}


////////////////////////////////
// Applying main changes
////////////////////////////////


$S=forum_get_section($sql, $GET['s']);
post("perm","string");
if ($POST['perm']=='Apply changes') {
  post("name","string");
  post("descr","string");
  post("newown","string");
  post("sr","integer");
  post("sw","integer");
  post("sn","integer");
  post("sm","integer");
  $POST['name']=makequotedstring(htmlentities($POST['name']));
  $POST['descr']=makequotedstring(htmlentities($POST['descr']));

  $newOwner=makeinteger($S['Owner']);
  $newTag=makequotedstring($S['OwnerTag']);
  if ($POST['newown']!="") {
    $tmpNewOwn=account_get_id($sql, $POST['newown']);
    if ($tmpNewOwn>0) {
      $c=forum_count_owned($sql, $tmpNewOwn);
      if ($c<5) {
	$newOwner = $tmpNewOwn;
	$newTag=makequotedstring(account_get_perm_tag($sql, $newOwner));
      } else
	$H->Insert(new Error("Player " . htmlentities($POST['newown']) . " cannot control more sections"));
    }
    else
      $H->Insert(new Error("Player " . htmlentities($POST['newown']) . " not found"));
  }
  $sql->query("UPDATE NC_Sections SET Name={$POST['name']}, Description={$POST['descr']},"
    . " Owner=$newOwner, OwnerTag=$newTag, "
    . " `Read`={$POST['sr']}, `Write`={$POST['sw']}, `New`={$POST['sn']}, Moderate={$POST['sm']}"
    . " WHERE SectID=" . $GET['s']);

  //there were changes - reload
  $H->Insert(new Info("Changes applied"));
  $S=forum_get_section($sql, $GET['s']);

}

get("pe","string");
if (exists($GET['pe']))
  forum_permission_remove_player($sql, $GET['s'], $GET['pe']);
get("ge","string");
if (exists($GET['ge']))
  forum_permission_remove_group($sql, $GET['s'], $GET['ge']);

post("pname","string");
if (exists($POST['pname'])) {
  $i=account_get_id($sql,$POST['pname']);
  if ($i>0)
    forum_permission_add_player($sql, $GET['s'], $i);
  else
    $H->Insert(new Error("Player " . htmlentities($POST['pname']) . " not found"));
}

post("ptag","string");
if (exists($POST['ptag'])) {
  forum_permission_add_group($sql, $GET['s'], $POST['ptag']);
}

/////////////////////////////////
// User interface form
/////////////////////////////////

$F=new Form("sectionmanage.php?type={$GET['type']}&s={$GET['s']}",true);
$T=new Table;

$Sel=new Select;
$Sel->AddOption('0','section owner');
$Sel->AddOption('1','section owner group');
$Sel->AddOption('2','by permission list');
$Sel->AddOption('3','everyone');

$T->sClass='block';
$T->iCols=3;
$T->Insert(1,1,"Section management");
$T->Join(1,1,3,1);
$T->aRowClass[1]='title';

$T->Insert(1,2,"Name"); $T->SetClass(1,2,'legend');
$T->Insert(2,2,new Input('text','name',$S['Name'],'text'));
$T->Join(2,2,2,1);

$T->Insert(1,3,"Desctiption"); $T->SetClass(1,3,'legend');
$T->Insert(2,3,new Input('text','descr',$S['Description'],'text'));
$T->Join(2,3,2,1);

$T->Insert(1,4,"Owner"); $T->SetClass(1,4,'legend');
$OwnerName=account_get_name($sql, $S['Owner']);
$T->Insert(2,4,'' . $OwnerName);
$T->Insert(3,4,new Input('text','newown','','text'));


$T->Insert(1,5,"Group");
if ($S['OwnerTag']!='')
  $T->Insert(2,5,'('.$S['OwnerTag'].')');
else
  $T->Insert(2,5,'none');
$T->SetClass(1,5,'legend');
$T->Join(2,5,3,1);
$F->Insert($T);

$T=new Table;
$T->sClass='block';
$T->iCols=3;
$T->Insert(1,1,"Permissions");
$T->Join(1,1,3,1);
$T->SetClass(1,1,'title');
$T->Insert(2,6,new Input("submit","perm","Apply changes","smbutton"));
$UsePermissionList=false;

$T->Insert(1,2,"Read"); $T->SetClass(1,2,'legend');
$Sel->sDefault=$S['Read']; $Sel->sName='sr';
$T->Insert(2,2,$Sel);
if ($S['Read']==0 || ($S['Read']==1 && $S['OwnerTag']==""))
  $T->Insert(3,2,'' . $OwnerName);
elseif ($S['Read']==1)
  $T->Insert(3,2,'(' . $S['OwnerTag'] . ')');
elseif ($S['Read']==3)
  $T->Insert(3,2,'everyone');
else {
  $T->Insert(3,2,'see below');
  $UsePermissionList=true;
}


$T->Insert(1,3,"Write"); $T->SetClass(1,3,'legend');
$Sel->sDefault=$S['Write']; $Sel->sName='sw';
$T->Insert(2,3,$Sel);
if ($S['Write']==0 || ($S['Write']==1 && $S['OwnerTag']==""))
  $T->Insert(3,3,'' . $OwnerName);
elseif ($S['Write']==1)
  $T->Insert(3,3,'(' . $S['OwnerTag'] . ')');
elseif ($S['Write']==3)
  $T->Insert(3,3,'everyone');
else {
  $T->Insert(3,3,'see below');
  $UsePermissionList=true;
}

$T->Insert(1,4,"New threads"); $T->SetClass(1,4,'legend'); 
$Sel->sDefault=$S['New']; $Sel->sName='sn';
$T->Insert(2,4,$Sel);
if ($S['New']==0 || ($S['New']==1 && $S['OwnerTag']==""))
  $T->Insert(3,4,'' . $OwnerName);
elseif ($S['New']==1)
  $T->Insert(3,4,'(' . $S['OwnerTag'] . ')');
elseif ($S['New']==3)
  $T->Insert(3,4,'everyone');
else {
  $T->Insert(3,4,'see below');
  $UsePermissionList=true;
}

$T->Insert(1,5,"Moderate"); $T->SetClass(1,5,'legend'); 
$Sel->sDefault=$S['Moderate']; $Sel->sName='sm';
$T->Insert(2,5,$Sel);
if ($S['Moderate']==0 || ($S['Moderate']==1 && $S['OwnerTag']==""))
  $T->Insert(3,5,'' . $OwnerName);
elseif ($S['Moderate']==1)
  $T->Insert(3,5,'(' . $S['OwnerTag'] . ')');
elseif ($S['Moderate']==3)
  $T->Insert(3,5,'everyone');
else {
  $T->Insert(3,5,'see below');
  $UsePermissionList=true;
}

$F->Insert($T);
$H->Insert($F);

if ($UsePermissionList) {
  $F=new Form("sectionmanage.php?s={$GET['s']}",true);
  $T=new Table();
  $T->sClass='block';
  $T->Insert(1,1,'Permission list');
  $T->aRowClass[1]='title';
  $r=2;
  $Ps=forum_get_section_permission_list($sql, $GET['s']);
  $T->Insert(1,$r,'' . $OwnerName);
  $T->Insert(2,$r,'--');
  $T->Join(1,1,2,1);
  ++$r;
  foreach ($Ps as $P) {
    if ($P['User']>0) {
      $T->Insert(1,$r,'' . account_get_name($sql, $P['User']));
      $T->Insert(2,$r,new Link("sectionmanage.php?type={$GET['type']}&s={$GET['s']}&pe={$P['User']}","X"));
    } else {
      $T->Insert(1,$r,'(' . $P['TAG'] . ')');
      $T->Insert(2,$r,new Link("sectionmanage.php?type={$GET['type']}&s={$GET['s']}&ge={$P['TAG']}","X"));
    }
    ++$r;
  }

  $T->Insert(1,$r,'Player: ');
  $T->Insert(1,$r,new Input("text","pname","","text"));
  $T->Insert(1,$r+1,'Group: ');
  $T->Insert(1,$r+1,new Input("text","ptag","","text"));
  $T->Insert(2,$r,new Input("submit","act","add","smbutton"));
  $T->Join(2,$r,1,2);
  $F->Insert($T);
  $H->Insert($F);
}

$H->Insert(new Link("forum.php?type={$GET['type']}&s={$GET['s']}","Jump to section"));

$H->Draw();
CloseSQL($sql);
?>
