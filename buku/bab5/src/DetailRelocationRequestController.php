<?php

namespace App\Http\Controllers;

use App\DetailRelocationRequest;
use App\Provider;
use App\RelocationRequest;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Validator;
use Session;
use DB;
use App\Http\Controllers\Controller;

class DetailRelocationRequestController extends Controller
{
    public function showDetail($id){
        $detail = DetailRelocationRequest::with('relocation_request')->with('provider')->find($id);
        $relocation_request = RelocationRequest::with('regional_office')->where('id', '=', $detail->id_relocation_request)->first();
        return view('detailRelocationRequest', ['detail' => $detail, 'relocation_request' => $relocation_request]);
    }

    public function showEditForm($id){
        $detail = DetailRelocationRequest::with('relocation_request')->with('provider')->find($id);
        $providers = Provider::get();
        return view('form.editDetailRelocationRequest', ['detail' => $detail, 'providers' => $providers]);
    }

    public function delete($id){
        $detail_relocation_request = DetailRelocationRequest::find($id);
        if($detail_relocation_request->id_work_permit === null){
            $detail_relocation_request->delete();
        }
        return redirect()->route('relocation.request');
    }

    public function update(Request $request) {
        $fields = ['activity', 'original_work_unit_code', 'original_work_unit_name', 'original_location', 'destination_work_unit_code', 'destination_work_unit_name', 'destination_location',
                    'ip_address', 'id_provider', 'reason_of_relocation'];

        $detail = DetailRelocationRequest::where('id', '=', $request->id)->get();

        if (count($detail) > 0) {
            foreach ($fields as $field) {
                $detail[0]->{$field} = $request->{$field};
            }
        }
        $detail[0]->save();
        return redirect()->route('show.relocation.request', ['id'=> $detail[0]->id_relocation_request]);
    }
}
