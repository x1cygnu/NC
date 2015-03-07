<?php
$M = new Table();
$M(1,1)[] = Ref('news.php')->_(Button()->_("News")->setClass('mainbutton'));
$M(1,2)[] = Ref('map.php')->_(Button()->_("Map")->setClass('mainbutton'));
$M(1,3)[] = Ref('logout.php')->_(Button()->_("Logout")->setClass('mainbutton'));

$H[] = $M;
?>
