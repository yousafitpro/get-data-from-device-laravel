<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class roleController extends Controller
{
    public static $permissions=[
        'user',
        'delete user',
        'add user',
        'update user'
    ];
    public static $employee_roles=[
        'operator'
    ];
    public static $roles=[
        'admin',
        'user',
        'operator'
    ];
    public function index(Request $request)
    {
        Role::where('name','employee')->delete();
        $data['list']=Role::all();
        return view('role.index',$data);
    }
    public function addPermission(Request $request)
    {
          $role=Role::find($request->role_id);
          $permission=Permission::where('name',$request->name)->first();
        $role->givePermissionTo($permission);
         return "ok";
    }
    public function revokePermission(Request $request)
    {

        $role=Role::find($request->role_id);
        $permission=Permission::where('name',$request->name)->first();
        $role->revokePermissionTo($permission);
        return back()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Permission revoked successfully.',
                    'type' => 'success',
                ]
            ]);
    }
    public function permissions($id)
    {
        $data['role']=Role::find($id);
        $data['permissions']=self::$permissions;
        return view('role.role_permissions',$data);
    }
public static function create_roles_and_permissions()
{
        foreach (self::$roles as $r)
        {
            if (!Role::where('name',$r)->exists())
            {
               \Spatie\Permission\Models\Role::create(['name' => $r]);
            }
        }
    foreach (self::$permissions as $p)
    {
        if (!Permission::where('name',$p)->exists()) {
            \Spatie\Permission\Models\Permission::create(['name' => $p]);
        }
    }
    $permissions=Permission::all();
    $role=Role::where('name','admin')->first();
    $role->syncPermissions($permissions);
}
}
