<?php
$M = new Table();
$M(1,1)[] = Ref('news.php')->_(Button()->_("News")->setClass('mainbutton'));
$M(1,2)[] = Ref('logout.php')->_(Button()->_("Logout")->setClass('mainbutton'));

$H[] = $M;
?>
