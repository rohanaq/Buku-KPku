<?php

namespace App\Http\Controllers;

use App\DetailRelocationRequest;
use App\WorkPermit;
use Illuminate\Http\Request;

use App\RelocationRequest;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Validator;
use Session;
use DB;
use PDF;
use App\Http\Controllers\Controller;

class WorkPermitController extends Controller
{
    public function index(){
        if (Auth::user()->id_role !== 1) {
            $details = DetailRelocationRequest::whereIn('id_work_permit', function($query){
                $query->select('id')
                    ->from(with(new WorkPermit)->getTable())
                    ->whereIn('id_status', [1, 2]);
            })->where('id_provider', '=', Auth::user()->id_provider)->get();
        } else {
            $details = DetailRelocationRequest::whereIn('id_work_permit', function($query){
                $query->select('id')
                    ->from(with(new WorkPermit)->getTable())
                    ->whereIn('id_status', [1, 2]);
            })->get();
        }
        return view('listWorkPermit', ['details' => $details]);
    }

    public function addIndex(){
        $relocationRequests = RelocationRequest::where()->get();
        if (Auth::user()->id_role !== 1) {
            $relocationRequestDetails = DetailRelocationRequest::whereNull('id_work_permit')->where('id_provider', '=', Auth::user()->id_provider)->with('provider')->get();
        } else {
            $relocationRequestDetails = DetailRelocationRequest::whereNull('id_work_permit')->with('provider')->get();
        }

        $detailByRelocationRequest = new \stdClass();

        foreach ($relocationRequests as $relocationRequest) {
            $detailByRelocationRequest->{$relocationRequest->id} = [];

            foreach ($relocationRequestDetails as $relocationRequestDetail) {
                if ($relocationRequestDetail->id_relocation_request == $relocationRequest -> id) {
                    array_push($detailByRelocationRequest->{$relocationRequest->id}, $relocationRequestDetail);
                }
            }
        }

        $initials = new \stdClass();
        $initials -> relocationRequests = $relocationRequests;
        $initials -> relocationRequestDetails = $relocationRequestDetails;
        $initials -> detailByRelocationRequest = $detailByRelocationRequest;

        return view('form.addWorkPermit', ['initials' => json_encode($initials), 'relocationRequests' => $relocationRequests]);
    }

    public function detailWorkPermit($id){
        $work_permit = WorkPermit::with('status')
        ->find($id);
        $detail = DetailRelocationRequest::where
        ('id_work_permit', $id)->first();
        return view('detailWorkPermit', 
            ['work_permit' => $work_permit, 
            'detail' => $detail]);
    }

    public function create(Request $request){
        $newWorkPermit = new WorkPermit();

        $newWorkPermit -> isSiteSurvey = $request 
        -> isSiteSurvey == "on" ? true : false;
        $newWorkPermit->isInstalasiHW = $request 
        -> isInstalasiHW == "on" ? true : false;
        $newWorkPermit->isUpgradeHardware = $request 
        -> isUpgradeHardware == "on" ? true : false;
        $newWorkPermit->isMoveHardware = $request 
        -> isMoveHardware == "on" ? true : false;
        $newWorkPermit->isInstallingSystem = $request 
        -> isInstallingSystem == "on" ? true : false;
        $newWorkPermit->isUpgradeSystem = $request 
        -> isUpgradeSystem == "on" ? true : false;
        $newWorkPermit->isModifySystem = $request 
        -> isModifySystem == "on" ? true : false;
        $newWorkPermit->isMaintenance = $request 
        -> isMaintenance == "on" ? true : false;
        $newWorkPermit->person_in_charge = $request 
        -> person_in_charge;
        $newWorkPermit->work_detail = $request 
        -> work_detail;
        $newWorkPermit->id_status = 1;

        $newWorkPermit -> save();

        $details = json_decode($request->details);

        foreach($details as $detail) {
            $detailRelocationRequest = 
            DetailRelocationRequest::where
            ('id', '=', $detail)->get();
            $detailRelocationRequest[0] 
            -> id_work_permit = $newWorkPermit -> id;
            $detailRelocationRequest[0] -> save();
        }
        return redirect()->route('work.permit');
    }

    public function showEditForm($id){
        $work_permit = WorkPermit::find($id);
        return view('form.editWorkPermit', ['work_permit' => $work_permit]);
    }

    public function update(Request $request){
        $fields = array('work_permit_reference_number', 'person_in_charge', 'work_order_reference_number',
            'work_order_date_letter', 'work_permit_attachment');
        $work_permit = WorkPermit::find($request->id);
        foreach ($fields as $field){
            if($field !== 'work_permit_attachment'){
                $work_permit[$field] = $request[$field];
            } else {
                $file = $request->file('work_permit_attachment');
                if($file !== null) {
                    $workPermitAttachmentName = 'work_permit_'.$work_permit->id.'.pdf';
                    $file->move(public_path('file'), $workPermitAttachmentName);
                    $work_permit->work_permit_attachment = $workPermitAttachmentName;
                    $work_permit -> save();
                }
            }
        }
        $work_permit->save();
        return redirect()->route('detail.work.permit', ['id' => $request->id]);
    }

    public function delete($id){
        $detail_relocation_request = DetailRelocationRequest::where('id_work_permit', $id)->first();
        $detail_relocation_request->id_work_permit = null;
        $detail_relocation_request->save();
        $work_permit = WorkPermit::find($id);
        $work_permit->delete();
        return redirect()->route('work.permit');
    }

    public function acceptWorkPermit($id){
        $work_permit = WorkPermit::find($id);
        if($work_permit->work_permit_reference_number != null && $work_permit->work_permit_attachment != null){
            $work_permit->id_status = 2;
            $work_permit->save();
            Session::flash('success', 'Approved');
        } else {
            Session::flash('fail', 'Data harus dilengkapi.');
        }
        return redirect()->route('detail.work.permit', ['id' => $id]);
    }

    public function letter($id){
        $work_permit = WorkPermit::with('status')->find($id);
        $detail = DetailRelocationRequest::where('id_work_permit', $id)->first();
        return view('letter', ['work_permit' => $work_permit, 'detail' => $detail]);
    }

    public function downloadWorkPermit($id){
        $file = public_path()."/file/work_permit_$id.pdf";
        $headers = array(
            'Content-Type' => 'application/pdf',
        );
        return response()->download($file, 'work_permit_'.$id.'.pdf', $headers);
    }

    public function pdfview(Request $request, $id)
    {
        $work_permit = WorkPermit::with('status')->find($id);
        $detail = DetailRelocationRequest::where('id_work_permit', $id)->first();
        view()->share('work_permit', $work_permit, 'detail', $detail);

        if($request->has('download')){
            $pdf = PDF::loadView('letter', ['work_permit' => $work_permit, 'detail' => $detail]);
            return $pdf->download('pdfview.pdf');
        }

        return view('pdfview');
    }
}
