<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Traits\ResponseTrait;
use App\Models\Option;
use Auth;

class OptionsController extends Controller
{
    use ResponseTrait;

    public function index()
    {
        try {
            $roles = Role::select('id as value', 'name as text')->get();
            $permissions = Permission::select('id as value', 'name as text')->get();

            if($options = Option::select('id', 'category', 'code as value', 'display as text', 'flag')->get()) {
                $optionsObj = $options->groupBy('category');
                $optionsObj = json_encode($optionsObj);
                $optionsObj = json_decode($optionsObj);
                $optionsObj->roles = $roles;
                $optionsObj->permissions = $permissions;

                return $this->successWithData("Success", $optionsObj);
            } else {
                return $this->failure("Failed to list reference codes");
            }
        } catch (\Exception $e) {
            return $this->failure("Failed to list all reference codes");
        }
    }

    public function detailedList()
    {
        if(!Auth::user()->can('options.list')) {
            return $this->forbidden();
        }

        if($options = Option::orderBy('category')->orderBy('code')->get()) {
            return $this->successWithData("Success", $options);
        } else {
            return $this->failure("Failed to list reference codes");
        }
    }

    public function categoryList()
    {
        if($options = Option::distinct()->pluck('category')->toArray()) {
            return $this->successWithData("Success", $options);
        } else {
            return $this->failure("Failed to list reference code categories");
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!Auth::user()->can('options.add')) {
            return $this->forbidden();
        }

        // Logging into audit log
        Controller::audit_log(Auth::user()->user_id, $request, "options.store");

        $validated = $request->validate([
            'category' => 'required|string',
            'code' => 'required|string',
            'display' => 'required|string',
            'description' => 'nullable|string',
            'flag' => 'nullable|string|max:5'
        ]);

        if($option = Option::create($validated)) {
            return $this->successWithID("Option created successfully", $option->id);
        } else {
            return $this->failure("Failed to create option");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(!Auth::user()->can('options.view')) {
            return $this->forbidden();
        }

        if($options = Option::find($id)) {
            return $this->successWithData("Success", $options);
        } else {
            return $this->failure("Failed to list reference codes", 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(!Auth::user()->can('options.update')) {
            return $this->forbidden();
        }

        // Logging into audit log
        Controller::audit_log(Auth::user()->user_id, $request, "options.update");

        $validated = $request->validate([
            'category' => 'required|string',
            'code' => 'required|string',
            'display' => 'required|string',
            'description' => 'nullable|string',
            'flag' => 'nullable|string|max:5'
        ]);

        if($user = Option::find($id)) {
            $user->update($validated);

            return $this->successWithID("Option updated successfully", $id);
        } else {
            return $this->failure("Failed to update option");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        if(!Auth::user()->can('options.delete')) {
            return $this->forbidden();
        }

        // Logging into audit log
        Controller::audit_log(Auth::user()->user_id, $request, "options.delete");

        if((Option::find($id) != null) && Option::find($id)->delete()) {
            return $this->success("Option deleted successfully");
        } else {
            return $this->failure("Failed to delete option");
        }
    }
}
