<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShipController;
use App\Http\Controllers\UserController;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    // register
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::group(['middleware' => ['auth', 'isActiveUser']], function () {
    Route::post('/refresh', [AuthController::class, 'refresh']);
    // Ship
    Route::post('/ship', [ShipController::class, 'store']);
    Route::post('/ship-edit', [ShipController::class, 'update']);
    Route::get('/ship', [ShipController::class, 'index']);
    // Users
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile-edit', [AuthController::class, 'profileEdit']);

    Route::group(['middleware' => ['role:admin']], function () { // Admin Only
        // Users
        Route::get('/user-need-approval', [UserController::class, 'userNeedApproval']);
        Route::post('/approval-user', [UserController::class, 'approvalUser']);
        Route::post('/delete-user', [UserController::class, 'deleteUser']);
        // Ships
        Route::get('/ship-need-approval', [ShipController::class, 'shipNeedApproval']);
        Route::post('/ship-approval', [ShipController::class, 'shipApproval']);
        Route::post('/ship-delete', [ShipController::class, 'shipDelete']);
    });

});

Route::get('/ship-public', [ShipController::class, 'getShipPublic']);

Route::post('/tes', function(){
    // $user = User::find(1);
    // $user->hasRole('admin');

    // $role = Role::find(1);
    // $permission = Permission::find(1);
    // $user->givePermissionTo($permission);
    // return $role->syncPermissions($permission);
});
