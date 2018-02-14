<?php

namespace App\Http\Controllers;

use App\RelocationRequest;
use App\RegionalOffice;
use App\Provider;
use App\DetailRelocationRequest;
use Faker\Provider\File;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Validator;
use Session;
use DB;
use App\Http\Controllers\Controller;

class RelocationRequestController extends Controller
{
    public function index(){
        $details = DetailRelocationRequest::with('provider')->with('relocation_request.regional_office')->orderByDesc('created_at')->whereNull('id_work_permit')->get();
        return view('listRelocationRequest', ['details' => $details]);
    }

    public function addIndex(){
        $providers = Provider::all(); 
        $regionalOffices = RegionalOffice::all();       
        return view('form.addRelocationRequest', ['providers' => $providers, 'regionalOffices' => $regionalOffices]);
    }

    public function create(Request $request){
        $relocationRequestData = json_decode
        ($request->relocation_request_data);
        $detailsData = json_decode($request->
            details_data);
    
        $relocationRequest = new RelocationRequest();        
        $relocationRequest -> 
        relocation_request_reference_number = 
        $relocationRequestData->referenceNumber;
        $relocationRequest -> 
        relocation_request_date_letter = 
        $relocationRequestData->date;
        $relocationRequest -> id_regional_office = 
        $relocationRequestData->regionalOffice;
        $relocationRequest -> id_user = Auth::id();        
        $relocationRequest -> save();
        $file = $request->file
        ('relocation_request_attachment');                
        
        if($file !== null) {            
            $relocationRequestAttachmentName = 
            'relocation_request_'
            .$relocationRequest->id.'.pdf';
            $file->move(public_path('file'), 
                $relocationRequestAttachmentName);
            $relocationRequest->
            relocation_request_attachment = 
            $relocationRequestAttachmentName;
            $relocationRequest -> save();
        }        

        foreach ($detailsData as $detailsDatum) {
            $detailRelocationRequest = 
            new DetailRelocationRequest();
            $detailRelocationRequest -> activity = 
            $detailsDatum -> activity;
            $detailRelocationRequest 
            ->id_relocation_request = 
            $relocationRequest -> id;
            $detailRelocationRequest 
            ->original_work_unit_code = $detailsDatum 
            ->originalWorkUnitCode;
            $detailRelocationRequest 
            ->original_work_unit_name = 
            $detailsDatum -> origWorkUnitName;
            $detailRelocationRequest 
            ->original_location = $detailsDatum 
            ->originalLocation;
            $detailRelocationRequest 
            ->destination_work_unit_code = 
            $detailsDatum->destinationWorkUnitCode ? 
            $detailsDatum->destinationWorkUnitCode : 
            null;
            $detailRelocationRequest 
            ->destination_work_unit_name = 
            $detailsDatum->destinationWorkUnitName ? 
            $detailsDatum ->destinationWorkUnitName : 
            null;
            $detailRelocationRequest 
            ->destination_location = $detailsDatum 
            ->destinationLocation ? $detailsDatum 
            ->destinationLocation : null;
            $detailRelocationRequest -> ip_address = 
            $detailsDatum -> ipAddress;
            $detailRelocationRequest 
            ->reason_of_relocation = $detailsDatum 
            ->reason ? $detailsDatum->reason : null;
            $detailRelocationRequest -> id_provider = 
            $detailsDatum -> provider;
            $detailRelocationRequest -> save();
        }
        return redirect()->route
        ('show.relocation.request', ['id' => 
            $relocationRequest->id]);
    }

    public function showRelocationRequest($id){
        $relocation_request = RelocationRequest::with('regional_office')->find($id);
        $details = DetailRelocationRequest::with('relocation_request')->with('provider')->where('id_relocation_request', $id)->get();
        return view('relocationRequest', ['relocation_request' => $relocation_request,'details' => $details]);
    }

    public function editForm($id){
        $relocation_request = RelocationRequest::with('regional_office')->find($id);
        $regional_offices = RegionalOffice::all();
        return view('form.editRelocationRequest', ['regional_offices' => $regional_offices, 'relocation_request' => $relocation_request]);
    }

    public function update(Request $request){
        $fields = array('relocation_request_reference_number', 'relocation_request_date_letter', 'id_regional_office', 'relocation_request_attachment');
        $relocation_request = RelocationRequest::find($request->id);
        $fields = array('relocation_request_reference_number', 'relocation_request_date_letter', 'relocation_request_attachment', 'id_regional_office');
        $relocation_request = RelocationRequest::where('id', '=', $request->id)->get();
        foreach ($fields as $field){
            if($field !== 'relocation_request_attachment'){
                $relocation_request[0][$field] = $request[$field];
            } else {
                $file = $request->file('relocation_request_attachment');
                if($file !== null) {
                    $relocationRequestAttachmentName = 'relocation_request_'.$relocation_request[0]->id.'.pdf';
                    $file->move(public_path('file'), $relocationRequestAttachmentName);
                    $relocation_request[0]->relocation_request_attachment = $relocationRequestAttachmentName;
                    $relocation_request[0] -> save();
                }
            }
        }

        $relocation_request[0]->save();
        return redirect()->route('edit.form.relocation.request', ['id' => $relocation_request[0]->id]);
    }

    public function downloadRelocationRequest($id){
        $file = public_path()."/file/relocation_request_$id.pdf";
        $headers = array(
            'Content-Type' => 'application/pdf',
        );
        return response()->download($file, 'relocation_request_'.$id.'.pdf', $headers);
    }

    public function delete($id){
        $relocation_request = RelocationRequest::find($id);
        $details = DetailRelocationRequest::where('id_relocation_request', $id)->get();
        foreach ($details as $detail){
            $detail->delete();
        }
        $relocation_request->delete();
        return redirect()->route('relocation.request');
    }
}
