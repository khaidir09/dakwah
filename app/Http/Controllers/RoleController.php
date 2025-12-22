<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::orderBy('id', 'DESC')->paginate(10);
        return view('pages.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('pages.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permission' => 'nullable|array',
        ]);

        $role = Role::create(['name' => $request->input('name')]);

        if($request->has('permission')){
            $role->syncPermissions($request->input('permission'));
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('pages.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'permission' => 'nullable|array',
        ]);

        $role = Role::findOrFail($id);
        $role->name = $request->input('name');
        $role->save();

        if($request->has('permission')){
            $role->syncPermissions($request->input('permission'));
        } else {
             $role->syncPermissions([]);
        }

        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully');
    }
}
