<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionsRequest;
use App\Models\Permissions;
use App\Models\RoleHasPermissions;
use App\Traits\HasCrud;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
class PermissionsController extends Controller
{
    use HasCrud;

    public function __construct()
    {
        $this->model=Permissions::class;
       
    }
    public function store(PermissionsRequest $request)
    {
        $data = $request->validated();
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $item = $this->model::create($data);
        $data = $this->model::all();

        return response()->json(['check' => true, 'data' => $data], 201);
    }
    // This will use the UserRequest for validation
    public function update(PermissionsRequest $request, $id)
    {
       $data=$request->all();
       $data['updated_at']=now();
        $this->model::find($id)->update($data);
        $data=$this->model::all();
        return response()->json(['check'=>true,'data'=>$data]);
    }

    public function import_permissions(Request $request){
        $validate = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
            'role_id'=>'required|exists:roles,id'
        ]);
        if ($validate->fails()) {
            return response()->json(['error' => $validate->errors(),
            'msg'=>$validate->errors()->first(),
        ], 400);
        }
        RoleHasPermissions::where('role_id',$request->role_id)->delete();
        foreach($request->permissions as $permission){
            RoleHasPermissions::create([
                'role_id'=>$request->role_id,
                'permission_id'=>$permission
            ]);
        }
        return response()->json(['check'=>true]);
    }
}
