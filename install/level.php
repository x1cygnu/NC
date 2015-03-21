<?php

function gen_attribute($name, $points_to_lvl, $points_for_lvl) {
  $result = <<<"END"
  function @_lvl(\$pts) { return $points_to_lvl; }
  function @_for_lvl(\$lvl) { return $points_for_lvl; }

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
print "<?php\n";
print gen_attribute('pop', '$pts/1000', '1000');
print "?>\n"

?>
