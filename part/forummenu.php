<?php
$U=new Table();
$U->sClass='menu';
$U->Insert(1,1,"Northern Cross Forum");
$U->Insert(1,2,new Link("forum.php?type=pub","Main"));
if ($forummenuselected=='pub')
  $U->SetClass(1,2,'menuselected');
$U->Insert(2,2,new Link("forum.php?type=dpub","Public"));
if ($forummenuselected=='dpub')
  $U->SetClass(2,2,'menuselected');
$U->Insert(3,2,new Link("forum.php?type=dpriv","Private"));
if ($forummenuselected=='dpriv')
  $U->SetClass(3,2,'menuselected');
$U->Insert(4,2,new Link("group.php","Group"));
if ($forummenuselected=='group')
  $U->SetClass(4,2,'menuselected');
$U->Insert(5,2,new Link("fsearch.php","Search"));
if ($forummenuselected=='s')
  $U->SetClass(5,2,'menuselected');
$U->Insert(6,2,new Link("ffav.php","Favourites"));
if ($forummenuselected=='fav')
  $U->SetClass(6,2,'menuselected');
$U->Insert(7,2,new Link("forum.php?type=own","Owned"));
if ($forummenuselected=='own')
  $U->SetClass(7,2,'menuselected');
$U->Join(1,1,7,1);
$U->SetClass(1,1,'title');
$H->Insert($U);
?>
