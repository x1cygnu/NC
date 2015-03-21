<?php

function gen_attribute($name, $points_to_lvl, $points_for_lvl) {
  $result = <<<"END"
  function @_lvl(\$pts) { return (int)($points_to_lvl); }
  function @_for_lvl(\$lvl) { return \$lvl>0 ? (int)($points_for_lvl) : 0; }

END;
  $result .= <<<'END'
  function @_for_next_lvl($lvl) { return @_for_lvl($lvl+1)-@_for_lvl($lvl); }
  function @_next_lvl_remain($pts) {
    $lvl = @_lvl($pts);
    $next = @_for_lvl($lvl+1);
    return $next-$pts;
  }
  function @_towards_next_lvl($pts) {
    $lvl = @_lvl($pts);
    $curr = @_for_lvl($lvl);
    return $pts-$curr;
  }
  function @_lvl_fraction($pts) {
    $lvl = @_lvl($pts);
    $curr = @_for_lvl($lvl);
    $next = @_for_lvl($lvl+1);
    return ($ptx-$curr) / ($next-$curr);
  }

END;
  $result = str_replace('@',$name,$result);
  return $result;
}

function gen_attribute_typed($name, $points_to_lvl, $points_for_lvl) {
  $result = <<<"END"
  function @_lvl(\$type, \$pts) { return (int)($points_to_lvl); }
  function @_for_lvl(\$type, \$lvl) { return \$lvl>0 ? (int)($points_for_lvl) : 0; }

END;
  $result .= <<<'END'
  function @_for_next_lvl($type, $lvl) { return @_for_lvl($type, $lvl+1)-@_for_lvl($type, $lvl); }
  function @_next_lvl_remain($type, $pts) {
    $lvl = @_lvl($type, $pts);
    $next = @_for_lvl($type, $lvl+1);
    return $next-$pts;
  }
  function @_towards_next_lvl($type, $pts) {
    $lvl = @_lvl($type, $pts);
    $curr = @_for_lvl($type, $lvl);
    return $pts-$curr;
  }
  function @_lvl_fraction($type, $pts) {
    $lvl = @_lvl($type, $pts);
    $curr = @_for_lvl($type, $lvl);
    $next = @_for_lvl($type, $lvl+1);
    return ($ptx-$curr) / ($next-$curr);
  }

END;
  $result = str_replace('@',$name,$result);
  return $result;
}

print "<?php\n";
print gen_attribute('pop', '$pts/1000', '1000*$lvl');
print gen_attribute_typed('sci', '$pts/RESEARCH_COST()[$type]', 'RESEARCH_COST()[$type]*$lvl');
print "?>\n"

?>
