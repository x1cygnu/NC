<?php


$U=new Table();
$U->sClass="menu";

if (CheckActivePlayer())
{
$U->Insert(1,2,new Link("news.php","News"));
    if ($menuselected=="News") $U->SetClass(1,2,"msd");
$U->Insert(4,3,new Link("alliance.php","Alln"));
    if ($msd=="Alliance") $U->SetClass(4,3,"msd");
$U->Insert(5,3,new Link("agreement.php","Agr"));
    if ($msd=="Agreements") $U->SetClass(5,3,"msd");

$U->Insert(2,2,new Link("map.php","Map"));
    if ($msd=="Map") $U->SetClass(2,2,"msd");
$U->Insert(2,3,new Link("planets.php","Plns"));
    if ($msd=="Planets") $U->SetClass(2,3,"msd");
$U->Insert(3,3,new Link("science.php","Sci"));
    if ($msd=="Science") $U->SetClass(3,3,"msd");
$U->Insert(1,3,new Link("fleet.php","Flt"));
    if ($msd=="Fleet") $U->SetClass(1,3,"msd");
$U->Insert(5,2,new Link("trade.php","Trd"));
    if ($msd=="Trade") $U->SetClass(5,2,"msd");

}

if (CheckPlayer())
{
$U->Insert(1,4,new Link("forum.php","Frum"));
    if ($msd=="Forum") $U->SetClass(1,4,"msd");
$U->Insert(2,4,new Link("pm.php","PM"));
    if ($msd=="PM") $U->SetClass(2,4,"msd");
$U->Insert(3,4,new Link("battlecalculator.php","Calc"));
    if ($msd=="BC") $U->SetClass(3,4,"msd");
$U->Insert(5,4,new Link("logout.php","Lout"));
    if ($msd=="Logout") $U->SetClass(5,4,"msd");
}

$U->Insert(1,1,"Northern Cross");
$U->aRowClass[1]='t';
$U->Join(1,1,5,1);

$H->Insert($U);
?>