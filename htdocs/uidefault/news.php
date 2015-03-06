<?php
include_once('./uidefault/menu.php');
include('./uidefault/messages.php');

$H->addStyle($UI.'/news.css');

$T=new Table();
$T(1,1)->_("News")->setClass('title')->span(1,2);
foreach ($news as $entry) {
  $i = $T->maxRows+1;
  $head = $T($i,1)->setClass('newshead');
  $body = $T($i,2)->setClass('newsbody');
  $head->_(timedecode($entry->showtime));
  switch ($entry->type) {
    case NEWS_WELCOME:
      $body[]="Welcome to Northern Cross!\n";
      $head->setClass('newsinfo');
      break;
    default:
      $body[]=Paragraph()->_("Invalid message item: ".$entry->type);
      $head->setClass('newserr');
      break;
  }
}

$H[]=$T;
?>
