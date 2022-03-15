<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\ResponseTrait;
use App\Models\AuditTrail;
use Carbon\Carbon;
use Auth;

class AuditTrailsController extends Controller
{
    use ResponseTrait;

    public function __construct(AuditTrail $auditTrail) {
        $this->auditTrail = $auditTrail;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!Auth::user()->can('auditTrails.list')) {
            return $this->forbidden();
        }

        if($auditTrails = AuditTrail::select('id', 'user_id', 'req_time', 'category')->orderBy('id', 'desc')->get()) {
            return $this->successWithData("Success", $auditTrails);
        } else {
            return $this->failure("Failed to list audit trails");
        }
    }

    public function search(Request $request) {
        if(!Auth::user()->can('auditTrails.list')) {
            return $this->forbidden();
        }

        $validated = $request->validate([
            'start_date' => 'string',
            'end_date' => 'string',
            'vardata' => 'string',
        ]);

        $search = $this->auditTrail->select('id', 'user_id', 'req_time', 'category');

        if($request->start_date) {
            $search->where('req_time', '>=', Carbon::createFromTimeString($request->start_date, 'UTC')->setTimezone(config('app.timezone'))->format('Y-m-d') . ' 00:00:00');
        }

        if($request->end_date) {
            $search->where('req_time', '<=', Carbon::createFromTimeString($request->end_date, 'UTC')->setTimezone(config('app.timezone'))->format('Y-m-d') . ' 23:59:59');
        }

        if($request->vardata) {
            $search->where('vardata', 'LIKE', '%' . $request->vardata . '%');
        }

        $search->orderBy('id', 'desc');

        if($auditTrails = $search->get()) {
            return $this->successWithData("Success", $auditTrails);
        } else {
            return $this->failure("Failed to search audit trails");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if(!Auth::user()->can('auditTrails.view')) {
            return $this->forbidden();
        }

        $request->merge(['id' => $request->route('id')]);
        $validated = $request->validate([
            'id' => 'required|integer',
        ]);

        if($auditTrail = AuditTrail::find($id)) {
            return $this->successWithData("Success", $auditTrail);
        } else {
            return $this->failure("Failed to view audit trail record", 404);
        }
    }
}
