<?php

include_once("./internal/common.php");

class CError
{
    var $title;
    var $explanation;
    var $type;
    function CError($stitle, $sexplanation, $stype)
    {
	$this->type=$stype;
	$this->explanation=$sexplanation;
	$this->title=$stitle;
    }
    function Report()
    {
	switch ($this->type)
	{
	case 0:return new Error("{$this->title}<br>{$this->explanation}");
	case 1:return new Error("Internal problem<br>{$this->title}<br>{$this->explanation}<br><br>Please be patient, wait few minutes and retry<br>If the error repeats please report it");
	case 2:return new Error("Internal error of unknown source<br>{$this->title}<br>{$this->explanation}<br><br>Please, report this error and explain what caused it");
	}
    }
}

$Error = 0;

?>