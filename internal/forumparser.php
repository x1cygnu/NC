<?php

include_once("./internal/stack.php");
include_once("./internal/security/validator.php");


function inlineSubstitute($source) {
  $source=str_replace("\n","<br>",$source);
  return $source;
}

$commands=array(
    "debug" => true,
    "b" => true,
    "i" => true,
    "u" => true,
    "tt" => true,
    "sup" => true,
    "sub" => true,
    "url" => true,
    "img" => true,
    "^" => false,
    "_" => false,
    "*" => false,
    "color" => true,
    "colour" => true,
    "size" => true,
    "list" => true,
    "table" => true,
    "quote" => true,
    "::" => false,
    ":" => false
    );

$commandTranslation=array(
    "*" => "listpoint",
    "^" => "shsup",
    "_" => "shsub",
    "colour" => "color",
    "::" => "row",
    ":" => "cell"
    );

$closingCommands=array(
    "/debug" => false,
    "/b" => false,
    "/i" => false,
    "/u" => false,
    "/tt" => false,
    "/sup" => false,
    "/sub" => false,
    "/url" => false,
    "/img" => false,
    "/color" => false,
    "/colour" => false,
    "/size" => false,
    "/list" => false,
    "/table" => false,
    "/quote" => false
    );

function decode_debug($params,$interior) {
  $output='[Debug(';
  foreach ($params as $key=>$param) {
    $output.=$param.' ';
  }
  $output.='){' . $interior . '}]';
  return $output;
}

function decode_b($params,$interior) {
  return '<b>' . $interior . '</b>';
}

function decode_i($params,$interior) {
  return '<i>' . $interior . '</i>';
}

function decode_u($params,$interior) {
  return '<u>' . $interior . '</u>';
}

function decode_tt($params,$interior) {
  return '<pre>' . $interior . '</pre>';
}

function decode_sup($params,$interior) {
  return '<sup>' . $interior . '</sup>';
}

function decode_sub($params,$interior) {
  return '<sub>' . $interior . '</sub>';
}

function decode_shsup($params,$interior) {
  return '<sup>' . $params[""] . '</sup>';
}

function decode_shsub($params,$interior) {
  return '<sub>' . $params[""] . '</sub>';
}

function decode_code($params,$interior) {
  return '<pre>' . $interior . '</pre>';
}

function decode_quote($params,$interior) {
  return '<table><tr><td class="qa">' . $params[""] . '</td></tr><tr><td class="q">' . $interior . '</td></tr></table>';
}

function decode_url($params,$interior) {
  if (array_key_exists("",$params)) {
    if ($interior!="")
      return '<a href="' . $params[""] . '">' . $interior . '</a>';
    return '<a href="' . $params[""] . '">' . $params[""] . '</a>'; //<-DEPRECATED
  }
  return '<a href="' . $interior . '">' . $interior . '</a>';
}

function decode_img($params,$interior) {
  if ($interior!="")
    $ans='<img src="' . $interior. '"';
  else
    $ans='<img src="' . $params[""] . '"'; //<-DEPRECATED
  if (array_key_exists("alt",$params))
    $ans.=' alt="' . $params["alt"] . '"';
  if (array_key_exists("width",$params)) {
    $w=$params['width'];
    if ($w<1) $w=1;
    if ($w>700) $w=700;
    $ans.=' width="' . $w . '"';
  }
  if (array_key_exists("height",$params)) {
    $w=$params['height'];
    if ($w<1) $w=1;
    if ($w>500) $w=500;
    $ans.=' height="' . $w . '"';
  }
  $ans.='>';
  return $ans;
}

function decode_color($params,$interior) {
  if (array_key_exists("",$params)) {
      return '<span style="color:' . str_replace(";","",$params[""]) . ';">' . $interior . '</span>';
      }
  return $interior;
}

function decode_size($params,$interior) {
  if (array_key_exists("",$params)) {
    $v=makeinteger($params[""]);
    if ($v<1) $v=1;
    if ($v>64) $v=64;
    return '<span style="font-size:' . str_replace(";","",$params[""]) . 'pt;">' . $interior . '</span>';
  }
}

function decode_list($params,$interior) {
  return '<ul style="">' . $interior . '</ul>';
}

function decode_listpoint($params,$interior) {
  return '<li>';
}

function decode_table($params,$interior) {
  return '<table class="block"><tr><td>' . $interior . '</tr></td></table>';
}

function decode_row($params,$interior) {
  if (array_key_exists("",$params))
    return '</td></tr><tr class="' . $params[""] . '"><td>';
  return '</td></tr><tr><td>';
}

function decode_cell($params,$interior) {
  if (array_key_exists("",$params))
    return '</td><td class="' . $params[""] . '">';
  return '</td><td>';
}

function completeToken(&$array, &$curtoken) {
  if ($curtoken!="") {
    $array[]=$curtoken;
    $curtoken="";
  }
}

function generateTokens($s) {
  $tokens=array();
  $len=strlen($s);
  $curtoken="";
  $weAreInside=false;
  for($i=0; $i<$len; ++$i) {
    $char=$s[$i];
    if ($char=='[' and !$weAreInside) {
      completeToken($tokens,$curtoken);
      $tokens[]='[';
      $weAreInside=true;
    }
    elseif ($char==']' and $weAreInside) {
      completeToken($tokens,$curtoken);
      $weAreInside=false;
      $tokens[]=']';
    }
    elseif ($char=='=' and $weAreInside) {
      completeToken($tokens,$curtoken);
      $tokens[]='=';
    }
    elseif ($char==' ' and $weAreInside) {
      completeToken($tokens,$curtoken);
    }
    else if ($char=="\r" and !$weAreInside) {
      completeToken($tokens,$curtoken);
      //drop \r completely
    }
    else if ($char=="\n" and !$weAreInside) {
      completeToken($tokens,$curtoken);
      $tokens[]="\n";
    }
    else {
      $curtoken.=$char;
    }
  }
  completeToken($tokens,$curtoken);
  return $tokens;
}

function launchFunction($command, $commandParams, $interior) {
  global $commandTranslation;
  if (array_key_exists($command, $commandTranslation))
      $command=$commandTranslation[$command];
  eval('$ans=decode_' . $command . '($commandParams,$interior);');
  return $ans;
}

function decode($s) {
  $s=htmlentities($s,ENT_QUOTES,"UTF-8");
  global $commands;
  global $closingCommands;
  $noCode=false;
  $tokens=generateTokens($s);
  $currentCommand="";
  $commandParams=array();
  $state=0; //plain text
  $output="";
  $currParam='';
  $commandStack=new Stack;
  $paramStack=new Stack;
  $innerStack=new Stack;
  $tokenPosition=0;
  $getNextToken=false;
  $skipSingleEndline=false;
  $token=$tokens[0];
  while($token!="") {
    if ($getNextToken) {
      ++$tokenPosition;
      $token=$tokens[$tokenPosition];
      if ($token=="\n" and $skipSingleEndline) {
        $skipSingleEndline=false;
        continue;
      }
      $skipSingleEndline=false;
      if ($token=="")
	break;
    }
    $getNextToken=true;
    switch ($state) {
      case 0: //plain text
        if ($token=='[')
	  $state=1; //awaiting command
	else {
	  $output.=$token;
	}
	break;
      case 1: //awaiting command
	if ($token==']') {
	  $output.="[]";
	  $state=0; //plain text
	} elseif ($token=='=') {
	  $output.="[=";
	  $state=0; //plain text
	} elseif (strtolower($token)=="nocode") {
	  $currentCommand=strtolower($token);
	  $commandParams=array();
	  $state=2; //fetching param name
	} elseif (array_key_exists(strtolower($token),$commands)) {
	  $currentCommand=strtolower($token);
	  $commandParams=array();
	  $state=2; //fetching param name
	} elseif (strtolower($token)=="/nocode") {
	  $currentCommand=strtolower($token);
	  $commandParams=array();
	  $state=3; //fetching param name
	} elseif (array_key_exists(strtolower($token),$closingCommands)) {
	  $currentCommand=strtolower($token);
	  $commandParams=array();
	  $state=3; //awaiting close tag completion
	} else {
	  $output.='['.$token;
	  $state=0;
	}
	break;
      case 2: //fetching param name
	if ($token==']') {
	  if ($noCode) { //code is disabled
            if ($currentCommand=="/nocode") {
	      $noCode=false;
	    }
	    else {
	      $output.='[' . $currentCommand;
	      $firstParam=true;
	      foreach ($commandParams as $par => $val) {
		if (!$firstParam)
		  $output.=" ";
		$output.="$par=$val";
		$firstParam=false;
	      }
	      $output.=']';
	    }
	    $state=0;
	  } elseif ($currentCommand=="nocode") {
	    $noCode=true;
	    $state=0;
	  } elseif (!$commands[$currentCommand]) { //no closing tag
	    $output.=launchFunction($currentCommand,$commandParams,"");
            $skipSingleEndline=true;
	    $state=0; //plain text
	  } else { //closing tag required
//	    var_dump("CLOSE");
	    $innerStack->push($output);
	    $commandStack->push($currentCommand);
	    $paramStack->push($commandParams);
	    //reset values:
	    $commandParams=array();
	    $currentCommand="";
	    $output="";
            $skipSingleEndline=true;
	    $state=0; //plain text
	  }
	} elseif ($token==' ')
	  break; //ignore whitespace
	elseif ($token=='=') { //direct parameter i.e. [quote=ugabuga]
	  $currParam='';
	  $state=5; //awaiting param value
	}
	else {
	  $currParam=$token;
	  $state=4; //awaiting equal sign after param
	}
	break;
      case 3: //awaiting clost tag completion
	if ($token==']') {
	  if ($noCode) { //code is disabled
            if ($currentCommand=="/nocode")
	      $noCode=false;
	    else {
	      $output.='[' . $currentCommand;
	      foreach ($commandParams as $par => $val)
		$output.=" $par=$val";
	      $output.=']';
	    }
	    $state=0;
	    break;
	  }
	  $openToken=ltrim($currentCommand,'/');
	  if ($commandStack->find($openToken)>-1) { //there is such token to close
	    while (1) {
	      $output=launchFunction($commandStack->top(),$paramStack->top(),$output);
	      if ($commandStack->top()==$openToken) //we closed what we had to close
		break;
	      //else: we are closing something else
	      $commandStack->pop();
	      $paramStack->pop();
	      $output=$innerStack->top() . $output;
	      $innerStack->pop();
	    }
	    $commandStack->pop();
	    $paramStack->pop();
	    $output=$innerStack->top() . $output;
	    $innerStack->pop();
            $skipSingleEndline=true;
	  } else { //there is no such token to close
	    $output.='[' . $currentCommand . ']';
	  }
	  $state=0; //plain text
	} elseif ($token==' ')
	  break; //ignore whitespace
	else { //tag was not closed as expected
	  $output.='[' . $currentCommand;
	  $getNextToken=false;
	  $state=0; //plain text
	}
	break;
      case 4: //awaiting equal sign after param
	if ($token=='=')
	  $state=5;
	elseif ($token==' ')
	  break; //ignore whitespace
	else { //empty param or what? Fallback to param name fetch
	  $getNextToken=false;
	  $state=2; //fetching param name
	}
	break;
      case 5: //awaiting param value
	if ($token==' ') //allow empty space
	  break;
	if ($token==']') { //tag closed unexpectedy, ingore param
	  $getNextToken=false;
	  $state=2;
	  break;
	} //we got param!
	$commandParams[$currParam].=$token;
	if ($tokens[$tokenPosition+1]==' ' or $tokens[$tokenPosition+1]==']') //look ahead to decide which state to choose
	  $state=2;
	else
	  $state=5; //keep the state and continue with param reading
	break;
    } //end of switch
  }//end of while

  while (!($commandStack->isEmpty())) {
    $output=launchFunction($commandStack->top(),$paramStack->top(),$output);
    $commandStack->pop();
    $paramStack->pop();
    $output=$innerStack->top() . $output;
    $innerStack->pop();
  }

  return inlineSubstitute($output);
}

?>
