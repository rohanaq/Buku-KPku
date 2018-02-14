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

class ExecutionController extends Controller
{
    public function index(){
        if(Auth::user()->id_role !== 1) {
            $executions = DetailRelocationRequest::whereIn('id_work_permit', function($query){
                $query->select('id')
                    ->from(with(new WorkPermit)->getTable())
                    ->whereIn('id_status', [2, 3]);
            })->where('id_provider', '=', Auth::user()->id_provider)->get();
        } else {
            $executions = DetailRelocationRequest::whereIn('id_work_permit', function($query){
                $query->select('id')
                    ->from(with(new WorkPermit)->getTable())
                    ->whereIn('id_status', [2, 3]);
            })->get();
        }
        return view('listExecution', ['executions' => $executions]);
    }

    public function showEditForm($id) {
        $execution = WorkPermit::find($id);
        return view('form.editExecution', ['execution' => $execution]);
    }

    public function update(Request $request){
        $fields = array('covering_letter_reference_number', 'covering_letter_date', 'principle_permit_reference_number',
            'principle_permit_date', 'event_report_attachment', 'bill_payment_attachment');
        $execution = WorkPermit::find($request->id);
        foreach ($fields as $field){
            if($field === 'event_report_attachment'){
                $file = $request->file('event_report_attachment');
                if($file !== null) {
                    $eventReportAttachmentName = 'event_report_'.$execution->id.'.pdf';
                    $file->move(public_path('file'), $eventReportAttachmentName);
                    $execution->event_report_attachment = $eventReportAttachmentName;
                    $execution -> save();
                }

            } elseif($field === 'bill_payment_attachment') {
                $file = $request->file('bill_payment_attachment');
                if($file !== null) {
                    $billPaymentAttachmentName = 'bill_payment_'.$execution->id.'.pdf';
                    $file->move(public_path('file'), $billPaymentAttachmentName);
                    $execution->bill_payment_attachment = $billPaymentAttachmentName;
                    $execution -> save();
                }
            } else {
                $execution[$field] = $request[$field];
            }
        }
        $execution->save();
        return redirect()->route('edit.form.execution', ['id' => $request->id]);
    }

    public function detailExecution($id){
        $execution = WorkPermit::with('status')
        ->find($id);
        $detail = DetailRelocationRequest::where
        ('id_work_permit', $id)->first();
        return view('detailExecution', 
            ['execution' => $execution, 
            'detail' => $detail]);
    }

    public function doneExecution($id){
        $execution = WorkPermit::find($id);
        $execution->id_status = 3;
        $execution->save();
        Session::flash('success', 'Finish');
        return redirect()->route('detail.execution', ['id' => $id]);
    }

    public function downloadEventReport($id){
        $file = public_path()."/file/event_report_$id.pdf";
        $headers = array(
            'Content-Type' => 'application/pdf',
        );
        return response()->download($file, 'event_report_'.$id.'.pdf', $headers);
    }

    public function downloadBillPayment($id) {
        $file = public_path()."/file/bill_payment_$id.pdf";
        $headers = array(
            'Content-Type' => 'application/pdf',
        );
        return response()->download($file, 'bill_payment_'.$id.'.pdf', $headers);
    }

    public function delete($id){
        $execution = WorkPermit::find($id);
        $execution->id_status = 2;
        $execution->save();
        return redirect()->route('execution');
    }
}
