<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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
    Route::post('/logout', [AuthController::class, 'logout']);

    // register
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/register', [AuthController::class, 'register']);

});

Route::group(['middleware' => 'auth:api'], function () {

    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/me', [AuthController::class, 'me']);

    Route::group(['middleware' => ['role:admin']], function () {

        // Users
        Route::get('/user-need-approval', [UserController::class, 'userNeedApproval']);
        Route::post('/approval-user', [UserController::class, 'approvalUser']);

    });

});

Route::post('/tes', function(){
    // $user = User::find(1);
    // $user->hasRole('admin');

    // $role = Role::find(1);
    // $permission = Permission::find(1);
    // $user->givePermissionTo($permission);
    // return $role->syncPermissions($permission);
});
