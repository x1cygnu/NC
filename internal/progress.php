<?
//progress bar
include_once("./internal/html.php");

function Progress(&$obj,$width,$cur,$max,$invert=false)
{
    $img=new Image("IMG/pr1.gif");
    $w=round($width*$cur/$max);
    $img->iWidth=($invert?$width-$w:$w);
    $img->iHeight=16;
    $obj->Insert($img);
    $img=new Image("IMG/pr2.gif");
    $img->iWidth=($invert?$w:$width-$w);
    $img->iHeight=16;
    $obj->Insert($img);
}

?>