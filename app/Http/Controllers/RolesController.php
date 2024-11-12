<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Roles;
use App\Traits\HasCrud;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RolesController extends Controller
{
    use HasCrud;

    public function __construct()
    {
        $this->model=Roles::class;
       
    }
    public function store(RoleRequest $request)
    {
        $data = $request->validated();
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $item = $this->model::create($data);
        $data = $this->model::all();

        return response()->json(['check' => true, 'data' => $data], 201);
    }
    public function get(){
       $result= Roles::with('permissions')->get();
       return response()->json($result);
    }
}
