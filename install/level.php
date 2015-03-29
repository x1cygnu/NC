<?php

function gen_attribute($name, $points_to_lvl, $points_for_lvl) {
  $result = <<<"END"
  function @_lvl(\$pts) { return (int)($points_to_lvl); }
  function @_needed_for_lvl(\$lvl) { return \$lvl>0 ? (int)($points_for_lvl) : 0; }

END;
  $result .= <<<'END'
  function @_needed_for_next_lvl($lvl) { return @_needed_for_lvl($lvl+1)-@_needed_for_lvl($lvl); }
  function @_for_next_lvl($pts) {
    $lvl = @_lvl($pts);
    $next = @_needed_for_next_lvl($lvl);
    return $next;
  }
  function @_next_lvl_remain($pts) {
    $lvl = @_lvl($pts);
    $next = @_needed_for_lvl($lvl+1);
    return $next-$pts;
  }
  function @_towards_next_lvl($pts) {
    $lvl = @_lvl($pts);
    $curr = @_needed_for_lvl($lvl);
    return $pts-$curr;
  }
  function @_lvl_fraction($pts) {
    $lvl = @_lvl($pts);
    $curr = @_needed_for_lvl($lvl);
    $next = @_needed_for_lvl($lvl+1);
    return ($pts-$curr) / ($next-$curr);
  }
  function @_get_all($pts) {
    $result['Points'] = $pts;
    $result['Level'] = @_lvl($pts);
    $result['Next'] = @_for_next_lvl($pts);
    $result['Towards'] = @_towards_next_lvl($pts);
    $result['Remain'] = @_next_lvl_remain($pts);
    $result['Fraction'] = @_lvl_fraction($pts);
    return $result;
  }

END;
  $result = str_replace('@',$name,$result);
  return $result;
}

function gen_attribute_typed($name, $points_to_lvl, $points_for_lvl) {
  $result = <<<"END"
  function @_lvl(\$type, \$pts) { return (int)($points_to_lvl); }
  function @_needed_for_lvl(\$type, \$lvl) { return \$lvl>0 ? (int)($points_for_lvl) : 0; }

END;
  $result .= <<<'END'
  function @_needed_for_next_lvl($type, $lvl) {
    return @_needed_for_lvl($type, $lvl+1)-
           @_needed_for_lvl($type, $lvl);
  }
  function @_for_next_lvl($type, $pts) {
    $lvl = @_lvl($type, $pts);
    $next = @_needed_for_next_lvl($lvl);
    return $next;
  }
  function @_next_lvl_remain($type, $pts) {
    $lvl = @_lvl($type, $pts);
    $next = @_needed_for_lvl($type, $lvl+1);
    return $next-$pts;
  }
  function @_towards_next_lvl($type, $pts) {
    $lvl = @_lvl($type, $pts);
    $curr = @_needed_for_lvl($type, $lvl);
    return $pts-$curr;
  }
  function @_lvl_fraction($type, $pts) {
    $lvl = @_lvl($type, $pts);
    $curr = @_needed_for_lvl($type, $lvl);
    $next = @_needed_for_lvl($type, $lvl+1);
    return ($ptx-$curr) / ($next-$curr);
  }
  function @_get_all($type, $pts) {
    $result['Points'] = $pts;
    $result['Level'] = @_lvl($type, $pts);
    $result['Next'] = @_for_next_lvl($type, $pts);
    $result['Towards'] = @_towards_next_lvl($type, $pts);
    $result['Remain'] = @_next_lvl_remain($type, $pts);
    $result['Fraction'] = @_lvl_fraction($type, $pts);
    return $result;
  }

END;
  $result = str_replace('@',$name,$result);
  return $result;
}

print "<?php\n";
print gen_attribute('pop', '$pts/1000', '1000*$lvl');
print gen_attribute('minerals', '$pts/3600', '3600*$lvl');
print gen_attribute_typed('sci', '$pts/RESEARCH_COST()[$type]', 'RESEARCH_COST()[$type]*$lvl');
print "?>\n"

?>
