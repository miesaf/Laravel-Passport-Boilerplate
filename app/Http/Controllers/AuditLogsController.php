<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\ResponseTrait;
use App\Models\AuditLog;
use Carbon\Carbon;
use Auth;

class AuditLogsController extends Controller
{
    use ResponseTrait;

    public function __construct(AuditLog $auditLog) {
        $this->auditLog = $auditLog;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!Auth::user()->can('auditLogs.list')) {
            return $this->forbidden();
        }

        if($auditLogs = AuditLog::select('id', 'user_id', 'req_time', 'category')->orderBy('id', 'desc')->get()) {
            return $this->successWithData("Success", $auditLogs);
        } else {
            return $this->failure("Failed to list audit logs");
        }
    }

    public function search(Request $request) {
        if(!Auth::user()->can('auditLogs.list')) {
            return $this->forbidden();
        }

        $validated = $request->validate([
            'start_date' => 'string',
            'end_date' => 'string',
            'vardata' => 'string',
        ]);

        $search = $this->auditLog->select('id', 'user_id', 'req_time', 'category');

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

        if($auditLogs = $search->get()) {
            return $this->successWithData("Success", $auditLogs);
        } else {
            return $this->failure("Failed to search audit logs");
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
        if(!Auth::user()->can('auditLogs.view')) {
            return $this->forbidden();
        }

        $request->merge(['id' => $request->route('id')]);
        $validated = $request->validate([
            'id' => 'required|integer',
        ]);

        if($auditLog = AuditLog::find($id)) {
            return $this->successWithData("Success", $auditLog);
        } else {
            return $this->failure("Failed to view audit log record", 404);
        }
    }
}
