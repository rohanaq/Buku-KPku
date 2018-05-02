public function findType(Request $req) {
  switch($req->nexttarget) {
    case 'regional':
      $data = 
      Regional::where('ID_AREA',3)->get();
      break;
    case 'branch':
      $data = 
      Branch::where('ID_REGIONAL',$req->id)->get();
      break;
    case 'cluster':
      $data = 
      Cluster::where('ID_BRANCH',$req->id)->get();
      break;
  }
  return json_encode($data);
}

public function reportGET() {
   return view('manager.report');
}
