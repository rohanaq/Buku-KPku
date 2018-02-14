<?php

namespace App\Http\Controllers;

use App\DetailRelocationRequest;
use App\WorkPermit;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Validator;
use Session;
use DB;
use App\Http\Controllers\Controller;

class BillingController extends Controller
{
    public function index(){
        if(Auth::user()->id_role !== 1) {
            $billings = DetailRelocationRequest::whereIn('id_work_permit', function($query){
                $query->select('id')
                    ->from(with(new WorkPermit)->getTable())
                    ->whereIn('id_status', [3, 4]);
            })->where('id_provider', '=', Auth::user()->id_provider)->get();
        } else {
            $billings = DetailRelocationRequest::whereIn('id_work_permit', function($query){
                $query->select('id')
                    ->from(with(new WorkPermit)->getTable())
                    ->whereIn('id_status', [3, 4]);
            })->get();
        }
        return view('listBilling', ['billings' => $billings]);
    }

    public function showEditForm($id) {
        $billing = WorkPermit::find($id);
        return view('form.editBilling', ['billing' => $billing]);
    }

    public function update(Request $request){
        $fields = array('payment_letter_reference_number', 'payment_letter_date');
        $billing = WorkPermit::find($request->id);
        foreach ($fields as $field){
            $billing[$field] = $request[$field];
        }
        $billing->save();
        return redirect()->route('edit.form.billing', ['id' => $request->id]);
    }

    public function detailBilling($id){
        $billing = WorkPermit::with('status')
        ->find($id);
        $detail = DetailRelocationRequest::where
        ('id_work_permit', $id)->first();
        return view('detailBilling', 
            ['billing' => $billing, 
            'detail' => $detail]);
    }

    public function finishBilling($id){
        $billing = WorkPermit::find($id);
        $billing->id_status = 4;
        $billing->save();
        Session::flash('success', 'Sukses.');
        return redirect()->route('detail.billing', ['id' => $id]);
    }

    public function delete($id){
        $billing = WorkPermit::find($id);
        $billing->id_status = 2;
        $billing->save();
        return redirect()->route('billing');
    }
}
