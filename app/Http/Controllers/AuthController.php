<?php

namespace App\Http\Controllers;

use stdClass;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OtpEmail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'login',
                'register',
                'sendOtp'
            ]
        ]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];
        $token = auth()->attempt($credentials);

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if(auth()->user()->is_approved == 0){
            return response()->json([
                'code' => 400,
                'message' => "Your account need approval by Admin"
            ], 400);
        }

        return $this->respondWithToken($token);
    }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users'
        ]);
        if($validator->fails()){
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }

        // Insert DB for validate OTP
        $otp = rand(100000,999999);
        OtpEmail::create([
            'email' => $request->email,
            'action' => 'register',
            'otp' => $otp,
        ]);

        // Hit external API send OTP by Email
        $client = new Client([
            'headers' => [ 'Content-Type' => 'application/json' ]
        ]);
        $url = 'https://script.google.com/macros/s/AKfycbxFNsyMXW8chGL8YhdQE1Q1yBbx5XEsq-BJeNF1a6sKoowaL_9DtcUvE_Pp0r5ootgMhQ/exec';
        $params = [
            'email' => $request->email,
            'subject' => "Register Tes Laravel 9",
            'message' => "Silahkan masukan kode OTP berikut ini : " . $otp,
            'token' => "1dy09eODblmBUCTnIwiY-hbXdzCpZC3jyR4l0ZJgqQqO9L7J3zsZOobdJ",
        ];

        $response = $client->post($url, ['body' => json_encode($params)]);

        return json_decode($response->getBody());
    }
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'otp' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }

        // Pengecekan OTP
        $check_otp = OtpEmail::where([
            'email' => $request->email,
            'action' => 'register',
            'otp' => $request->otp
        ])->first();

        // Pengecekan ketika otp tidak sesuai
        if(!$check_otp){
            return response()->json([
                'code' => 403,
                'message' => 'OTP Not Found!'
            ], 403);
        }else{
            // Cek expired otp selama 10 menit batas waktu
            $expiredTime = date("Y-m-d H:i:s",strtotime($check_otp->created_at) + 600);
            if(now() > $expiredTime){
                return response()->json([
                    'code' => 403,
                    'message' => 'OTP Expired!'
                ], 403);
            }
        }

        $user_created = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        $user_created->assignRole('user');

        return response()->json([
            'code' => 200,
            'message' => 'Register successfully! Please contact Admin for Approval',
            'data' => $user_created
        ], 200);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $user = User::where('id', auth()->user()->id)->with('shipApproved')->first();
        return response()->json($user);
    }

    public function profileEdit(Request $request)
    {
        /** @var User $user_access */
        $user_access = auth()->user();
        $user_will_be_edited = User::find($request->id);
        $name = $request->name;

        if($user_access->hasRole('user')){
            if(auth()->user()->id != $user_will_be_edited->id){
                return response()->json([
                    'code' => 400,
                    'message' => "You can't delete someone else's ship"
                ], 400);
            }
        }

        $user_will_be_edited->name = $name;
        $user_will_be_edited->save();

        return response()->json([
            'code' => 200,
            'message' => 'User profile has been edited',
            'data' => $user_will_be_edited
        ], 200);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
