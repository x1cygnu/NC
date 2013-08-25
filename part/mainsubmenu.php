<?php
$U=new Table();
$U->sClass="menu";
$U->Insert(1,1,new Link("index.php","Home"));
$U->Insert(2,1,new Link("register.php","Register"));
$U->Insert(3,1,new Link("wiki","Help/NCWiki"));
$U->Insert(4,1,new Link("ranking.php","Ranking"));
$U->Insert(5,1,new Link("about.php","Stats"));
$H->Insert($U);
?>