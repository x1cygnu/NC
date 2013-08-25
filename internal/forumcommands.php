<?php

// forum available commands:
$commands = array(
"b" => array( "<b>", 0),
"i" => array( "<i>", 0),
"u" => array( "<u>", 0),
"tt" => array( "<pre>", 0),
"list" => array( "<ul>", 0),
"*" => array( "<li>", 0),
"img" => array( "<img src=\"", 1, "\">"),
"url" => array( "<a href=\"", 1, "\">"),
"code" => array( "<pre>", 0),
"quote" => array( "<table><tr><td class=\"qa\">", 1, "</td></tr><tr><td class=\"q\">")
);

$commandsending = array(
"b" => "</b>",
"i" => "</i>",
"u" => "</u>",
"list" => "</ul>",
"img" => "",
"pre" => "</pre>",
"url" => "</a>",
"code" => "</pre>",
"quote" => "</td></tr></table>"
);

$inlinecommands = array(
":)" => ":-)"
);
?>
