<?php

namespace App\Http\Controllers;

use App\DetailRelocationRequest;
use App\RelocationRequest;
use App\WorkPermit;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Validator;
use Session;
use DB;
use App\Http\Controllers\Controller;

class MainController extends Controller
{
    public function index(){
        $detail_relocation_requests = 
        DetailRelocationRequest::whereNull
        ('id_work_permit')->count();
        $work_permit = WorkPermit::whereIn
        ('id_status', [1, 2])->count();
        $execution = WorkPermit::whereIn
        ('id_status', [2, 3])->count();
        $billing = WorkPermit::whereIn
        ('id_status', [3, 4])->count();
        $finish = WorkPermit::where
        ('id_status', '4')->count();
        return view('index',
            ['detail_relocation_requests' 
            => $detail_relocation_requests, 
            'work_permit' => $work_permit,
            'execution' => $execution,
            'billing' => $billing, 
            'finish' => $finish]);
    }

    public function finish(){
        if( Auth::user()->id_role !== 1) {
            $finishes = DetailRelocationRequest::
            whereIn('id_work_permit', 
                function($query){ 
                    $query->select('id')
                    ->from(with(new WorkPermit)
                        ->getTable())
                    ->where('id_status', 4);
            })->where('id_provider', '=', 
            Auth::user()->id_provider)->get();
        } else {
            $finishes = DetailRelocationRequest::
            whereIn('id_work_permit', 
                function($query){ 
                    $query->select('id')
                    ->from(with(new WorkPermit)
                        ->getTable())
                    ->where('id_status', 4);
            })->get();
        }
        return view('listFinish', 
            ['finishes' => $finishes]);
    }

    public function detailFinish($id){
        $finish = WorkPermit::with('status')->find($id);
        $detail = DetailRelocationRequest::with('provider')->with('relocation_request')->where('id_work_permit', $id)->first();
        return view('detailFinish', ['finish' => $finish, 'detail' => $detail]);
    }

    public function deleteFinish($id){
        $finish = WorkPermit::find($id);
        $finish->id_status = 3;
        $finish->save();
        return redirect()->route('finish');
    }
}
