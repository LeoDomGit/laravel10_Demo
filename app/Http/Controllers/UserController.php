<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function TestStaff()
    {
        $user = Auth::user();
        $permissions = Cache::get('user_permissions_' . $user->id);
        if (!$permissions || !in_array('crud_products', array_keys($permissions))) {
            return response()->json(['check' => false, 'msg' => 'You do not have the required permissions']);
        }
        return response()->json(['check' => true]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function TestAdmin()
    {
        $user = Auth::user();
        $permissions = Cache::get('user_permissions_' . $user->id);
        if (!$permissions || !in_array('crud_users', array_keys($permissions))) {
            return response()->json(['check' => false, 'msg' => 'You do not have the required permissions']);
        }
        return response()->json(['check' => true]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        $item = User::create($data);
        $data = User::all();
        return response()->json(['check' => true, 'data' => $data], 201);
    }

    /**
     * Display the specified resource.
     */
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if ($validate->fails()) {
            return response()->json([
                'error' => $validate->errors(),
                'msg' => $validate->errors()->first(),
            ], 400);
        }
    
        if (Auth::guard('web')->attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::guard('web')->user();
            $token = $user->createToken('authToken')->plainTextToken;
            $user->remember_token = $token;
            $user->save();
            $user = Auth::user();
            $roles = $user->roles()->with('permissions')->get();
            $permissionArray = [];
            foreach ($roles as $role) {
                foreach ($role->permissions as $permission) {
                    $permissionArray[$permission->name][] = $permission->id;
                }
            }
            
            // Cache the permissions for the user for 1 hour
            Cache::put('user_permissions_' . $user->id, $permissionArray, 3600);
            
            // Define permissions as gates dynamically based on permissions
            foreach ($permissionArray as $title => $permissionId) {
                Gate::define($title, function ($user) use ($title) {
                    // Retrieve cached permissions for the user
                    $cachedPermissions = Cache::get('user_permissions_' . $user->id);
            
                    // Check if the user has the required permission
                    return isset($cachedPermissions[$title]);
                });
            }
            return response()->json(['token' => $token,'permissions'=>$permissionArray]);
        }
    
        return response()->json([
            'error' => 'Unauthorized',
            'msg' => 'Invalid credentials.',
        ], 401);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
