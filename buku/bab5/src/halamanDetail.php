private function getTarget($type, $target){
  $sasaran = Target::whereHas('cluster', 
    function($a) use($target, $type){
      if($type == 'cluster') 
        $a->where('ID', $target);
      else $a->whereHas('branch', function($b) 
       use($target, $type){
        if($type == 'branch') 
          $b->where('ID', $target);
        else $b->whereHas('regional', 
         function($c) use($target, $type){
          if($type == 'regional') 
            $c->where('ID', $target);
          else $c->whereHas('area', 
           function($d) use($target, $type){
            $d->where('ID', $target);
          });
        });
      });
  })->get();
  $target = $sasaran->sum('TARGET');
  $target = floatval($target/1000000000);
  return $target;
}