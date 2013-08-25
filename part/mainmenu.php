<?php

include_once("internal/account.php");

$U=new Table();
$U->sClass="menu";

if (CheckActivePlayer())
{
  $U->Insert(1,2,new Link("news.php","News"));
  if ($menuselected=="News") $U->SetClass(1,2,"menuselected");
  $U->Insert(4,3,new Link("alliance.php","Alliance"));
  if ($menuselected=="Alliance") $U->SetClass(4,3,"menuselected");
  $U->Insert(5,3,new Link("agreement.php","Agreements"));
  if ($menuselected=="Agreements") $U->SetClass(5,3,"menuselected");

  $U->Insert(2,2,new Link("map.php","Map"));
  if ($menuselected=="Map") $U->SetClass(2,2,"menuselected");
  $U->Insert(2,3,new Link("planets.php","Planets"));
  if ($menuselected=="Planets") $U->SetClass(2,3,"menuselected");
  $U->Insert(3,3,new Link("science.php","Science"));
  if ($menuselected=="Science") $U->SetClass(3,3,"menuselected");
  $U->Insert(3,2,new Link("tech.php","Technology"));
  if ($menuselected=="Technology") $U->SetClass(3,2,"menuselected");
  $U->Insert(1,3,new Link("fleet.php","Fleet"));
  if ($menuselected=="Fleet") $U->SetClass(1,3,"menuselected");
  $U->Insert(5,2,new Link("trade.php","Trade"));
  if ($menuselected=="Trade") $U->SetClass(5,2,"menuselected");
  $U->Insert(4,2,new Link("forum.php?type=dpriv","Comm"));
  if ($menuselected=="Dipl") $U->SetClass(4,2,"menuselected");

}

if (CheckPlayer())
{
  $U->Insert(1,4,new Link("forum.php?type=pub","Forum"));
  if ($menuselected=="Forum") $U->SetClass(1,4,"menuselected");
  $U->Insert(2,4,new Link("pm.php","Mail box"));
  if ($menuselected=="PM") $U->SetClass(2,4,"menuselected");
  $U->Insert(3,4,new Link("battlecalculator.php","Calculators"));
  if ($menuselected=="BC") $U->SetClass(3,4,"menuselected");
  $U->Insert(4,4,new Link("settings.php","Settings"));
  if ($menuselected=="Settings") $U->SetClass(4,4,"menuselected");
  $U->Insert(5,4,new Link("logout.php","Logout"));
  if ($menuselected=="Logout") $U->SetClass(5,4,"menuselected");
}

if ($_SESSION['IsAdmin'])

{
    $U->Insert(1,5,new Link("group.php","Groups"));
        if ($menuselected=="Group") $U->SetClass(1,5,"menuselected");
    $U->Insert(2,5,new Link("broadcast.php","Broadcast"));
        if ($menuselected=="Broadcast") $U->SetClass(2,5,"menuselected");
    $U->Insert(3,5,new Link("su.php","Su"));
        if ($menuselected=="SU") $U->SetClass(3,5,"menuselected");
    $U->Insert(4,5,new Link("frozen.php","Frozen"));
        if ($menuselected=="Frozen") $U->SetClass(4,5,"menuselected");
    $U->Insert(5,5,new Link("armageddon.php","Round"));
        if ($menuselected=="Armageddon") $U->SetClass(5,5,"menuselected");
    
    $U->Insert(1,6,new Link("helplist.php","Help"));
        if ($menuselected=="Help") $U->SetClass(1,6,"menuselected");
    $U->Insert(2,6,new Link("bgmap.php","Background"));
        if ($menuselected=="Bgmap") $U->SetClass(2,6,"menuselected");
    $U->Insert(3,6,new Link("log.php","Log"));
        if ($menuselected=="Log") $U->SetClass(3,6,"menuselected");
    $U->Insert(4,6,new Link("multi.php","Multi"));
        if ($menuselected=="Multi") $U->SetClass(4,6,"menuselected");
}

if ($_SESSION['SitPID']>0)
{
    $U->Insert(1,7,'Account of ' . account_get_name_from_pid($sql, $_SESSION['SitPID']));
    $U->Join(1,7,5,1);
    $U->Insert(1,8,new Link("news.php?sit=1","News"));
    $U->Insert(2,8,new Link("fleet.php?sit=1","Fleet"));
    $U->Insert(3,8,new Link("planets.php?sit=1","Planets"));
    $U->Insert(4,8,new Link("science.php?sit=1","Science"));
    $U->Insert(5,8,new Link("trade.php?sit=1","Trade"));
    $U->SetClass(1,7,'legend');
    for ($ajhfg=1; $ajhfg<=5; ++$ajhfg)
	$U->SetClass($ajhfg,8,'sublegend');
}
$U->Insert(1,1,"Northern Cross");
$U->Join(1,1,5,1);
$U->SetClass(1,1,'title');


$H->Insert($U);
?>
