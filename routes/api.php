<?php

use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('roles',RolesController::class);
Route::resource('permissions',PermissionsController::class);
Route::resource('users',UserController::class);
Route::post('/import_permissions',[PermissionsController::class,'import_permissions']);
Route::post('/checkLogin',[UserController::class,'login']);
Route::get('/test_admin',[UserController::class,'TestAdmin'])->middleware('auth:sanctum');
Route::get('/test_staff',[UserController::class,'TestStaff'])->middleware('auth:sanctum');

Route::get('/role',[RolesController::class,'get']);

