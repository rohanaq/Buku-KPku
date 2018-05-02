public function uploadGET(Request $req) {
  if (Auth::user() - > hasRole('admin')) {
    $data['Area'] = Area::get();
    $data['Regional'] = 
      Regional::where('ID_AREA', 3)->get();
    $data['Branch'] = Branch::get();
    $data['Cluster'] = Cluster::get();
    return view('admin.input', $data);
  } 
  else {
    return back()
      ->withMessage('message','NoAccess!');
  }
}

public function uploadPOST(Request $req) {
  $idarea = 3;
  $idregional = $req->input('INPUTREGIONAL');
  $idbranch = $req->input('INPUTBRANCH');
  $idcluster = $req->input('INPUTCLUSTER');
  $file = $req->input('fileToUpload');
  $detail = array(
    "area" => $idarea,
    "regional" => $idregional,
    "branch" => $idbranch,
    "cluster" => $idcluster);
  $startdate = $req->input('UPLOADDATE');
  $finishdate = $req->input('FINISHDATE');
  $terakhirdatedb = 
    Revenue::where('ID_CLUSTER', $idcluster)
       ->orderBy('date', 'desc')->first();
  
  $awal = strtotime($startdate);
  $akhir = strtotime($finishdate);

  if ($terakhirdatedb != null) {
    $terakhirdb = strtotime($terakhirdatedb['DATE']);
    $range = $awal - $terakhirdb;
    $range = intval($range/(60*60*24));
      if ($range > 1) {
        dd("WrongDate");
      }
  }
  
  $rangedate = $akhir - $awal;
  $rangedate = intval($rangedate/(60*60*24))+1;
  
  $date = 
      DateTime::createFromFormat('D-M-d-Y', 
        $startdate);
  $date_format = $date->format("Y-m-d");
  $date_formated = explode("-", $date_format);

  $filepath = $this
      ->saveCSV($req, $date_formated, $date);
  return $this
      ->readCSV($filepath, $detail, 
        $date_format, $rangedate);
}