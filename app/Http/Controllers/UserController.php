<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function userNeedApproval(Request $request)
    {
        $users = User::where('is_approved', 0)->get();

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $users
        ], 200);
    }

    public function approvalUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if($validator->fails()){
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $user = User::whereId($request->id)->first();
        $user->is_approved = 1;
        $user->save();

        return response()->json([
            'code' => 200,
            'message' => 'User has been deactivated!',
            'data' => $user
        ], 200);
    }

    public function deleteUser(Request $request)
    {
        /** @var User $user */
        $user = User::find($request->id);
        $user->is_approved = 0;
        $user->save();

        return response()->json([
            'code' => 200,
            'message' => 'User is not active!',
            'data' => $user
        ], 200);
    }
}
