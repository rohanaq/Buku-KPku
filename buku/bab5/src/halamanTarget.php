public function showTarget() {
  if (Auth::user()->hasRole('admin')) {
    $data['Area'] = Area::get();
    $data['Regional'] = 
      Regional::where('ID_AREA',3)->get();
    $data['Branch'] = Branch::get();
    $data['Cluster'] = Cluster::get();
    $data['Service'] = Service::get();
    return view('admin.showTarget', $data);
  } 
  else {
    return redirect('/home');
  }
}

public function inputTarget(Request $req) {
  $result = $this->calculatedTarget($req);
  $idarea = 3;
  $idregional = $req->input('INPUTREGIONAL');
  $idbranch = $req->input('INPUTBRANCH');
  $idcluster = $req->input('INPUTCLUSTER');
  if ($idcluster == 'all') {
      $sasaran = Target::with('cluster')
        ->whereHas('cluster',function ($a) 
          use ($idbranch) {
            $a->where('ID_BRANCH', $idbranch);
      })->get();
  } 
  else {
    $sasaran = Target::with('cluster')
      ->whereHas('cluster', function ($a) 
        use ($idbranch) {
          $a->where('ID_BRANCH', $idbranch);
    })->where('ID_CLUSTER', $idcluster)->get();
  }
  $data['area'] = $idarea;
  $data['regional'] = $idregional;
  $data['cluster'] = $idcluster;
  $data['result'] = $sasaran;
  $data['branch'] = $idbranch;
  $data['target'] = $result;
  return view('admin.inputTarget', $data);
}