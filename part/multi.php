<?php
$T=new Table();
$T->sClass='block standard';
$T->Insert(1,1,new Link("logins.php","Login lists"));
$T->Insert(2,1,new Link("logaddr.php","Login addresses"));
$T->Insert(3,1,new Link("multiprofiles.php","Multi profiles"));
$T->Insert(4,1,new Link("multiexceptions.php","Exception"));
$T->Insert(5,1,new Link("multilist.php","Players"));
$T->Insert(6,1,new Link("ipban.php","IP"));
$H->Insert($T);
?>
