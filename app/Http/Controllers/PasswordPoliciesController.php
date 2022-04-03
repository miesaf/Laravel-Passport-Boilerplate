<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\ResponseTrait;
use App\Models\PasswordPolicy;
use Auth;

class PasswordPoliciesController extends Controller
{
    use ResponseTrait;

    public function index()
    {
        if(!Auth::user()->can('pwdPolicies.list')) {
            return $this->forbidden();
        }

        if($pwdPolicies = PasswordPolicy::orderBy('id')->get()) {
            return $this->successWithData("Success", $pwdPolicies);
        } else {
            return $this->failure("Failed to list password policies");
        }
    }

    public function show(Request $request, $id)
    {
        if(!Auth::user()->can('pwdPolicies.view')) {
            return $this->forbidden();
        }

        $request->merge(['id' => $request->route('id')]);
        $validated = $request->validate([
            'id' => 'required|integer',
        ]);

        if($pwdPolicy = PasswordPolicy::find($id)) {
            return $this->successWithData("Success", $pwdPolicy);
        } else {
            return $this->failure("Failed to view password policy record");
        }
    }

    public function update(Request $request, $id)
    {
        if(!Auth::user()->can('pwdPolicies.update')) {
            return $this->forbidden();
        }

        // Logging into audit log
        Controller::audit_log(Auth::user()->user_id, $request, "pwdPolicies.update");

        $request->merge(['id' => $request->route('id')]);
        $validated = $request->validate([
            'id' => 'required|integer',
            'value' => 'nullable|integer',
            'status' => 'required|boolean',
        ]);

        if(in_array($id, [7, 8]) && $request->status) {
            $failed_attempt = PasswordPolicy::find(6);

            if(!$failed_attempt->status) {
                return $this->failure("Update not saved. Please activate the 6th policy (Maximum failed attempt) before enabling this policy");
            }
        }

        if($pwdPolicy = PasswordPolicy::find($id)) {
            if($pwdPolicy->value) {
                $pwdPolicy->value = $request->value;
            }

            $pwdPolicy->status = $request->status;

            if($pwdPolicy->update()) {
                return $this->success("Password policy updated successfully");
            } else {
                return $this->failure("Failed to update password policy");
            }
        } else {
            return $this->failure("Password policy not found");
        }
    }
}
