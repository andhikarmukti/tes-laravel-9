<?php

namespace App\Http\Controllers;

use App\Models\Ship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Requests\StoreShipRequest;
use App\Http\Requests\UpdateShipRequest;
use Illuminate\Support\Facades\Validator;

class ShipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();

        if($user->hasRole('admin')){
            $ships = Ship::all();
        }else{
            $ships = Ship::where('user_id', auth()->user()->id)->get();
        }

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $ships
        ], 200);
    }

    public function shipNeedApproval()
    {
        $ships = Ship::where('is_verification', 0)->get();

        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'data' => $ships
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreShipRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'owner' => 'required|string|max:100',
            'address' => 'required',
            'size' => 'required',
            'captain' => 'required|string|max:100',
            'total_member' => 'required|numeric|min:0',
            'image' => 'required|image|mimes:jpg,png,jpeg|max:10240',
            'licence_number' => 'required',
            'permit_document' => 'required|mimes:pdf|max:10240',
        ];
        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $image_name = strtotime(now()) . $request->file('image')->getClientOriginalName();
        $image_path = $request->file('image')->storeAs('public/images', $image_name);
        $permit_document_path = $request->file('permit_document')->store('permit_documents');
        $code = generate_code(auth()->user()->id);

        $ship_created = Ship::create([
            'user_id' => auth()->user()->id,
            'code' => $code,
            'name' => $request->name,
            'owner' => $request->owner,
            'address' => $request->address,
            'size' => $request->size,
            'captain' => $request->captain,
            'total_member' => $request->total_member,
            'image' => env('APP_URL') . '/storage' . '/images' . '/' . $image_name,
            'licence_number' => $request->licence_number,
            'permit_document' => $permit_document_path
        ]);

        return response()->json([
            'code' => 200,
            'message' => 'Successfully registered the ship, please contact Admin for approval',
            'data' => $ship_created
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ship  $ship
     * @return \Illuminate\Http\Response
     */
    public function show(Ship $ship)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ship  $ship
     * @return \Illuminate\Http\Response
     */
    public function edit(Ship $ship)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateShipRequest  $request
     * @param  \App\Models\Ship  $ship
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $ship_existing = Ship::find($request->ship_id);
        $rules = [
            'name' => 'required|string|max:100',
            'owner' => 'required|string|max:100',
            'address' => 'required',
            'size' => 'required',
            'captain' => 'required|string|max:100',
            'image' => 'image|mimes:jpg,png,jpeg|max:10240',
            'total_member' => 'required|numeric|min:0',
            'licence_number' => 'required',
            'permit_document' => 'mimes:pdf|max:10240',
        ];

        if($user->hasRole('user')){
            if(auth()->user()->id != $ship_existing->user_id){
                return response()->json([
                    'code' => 400,
                    'message' => "You can't edit someone else's ship"
                ], 400);
            }
        }

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }

        if($request->file('image')){
            $image_name = strtotime(now()) . $request->file('image')->getClientOriginalName();
            $request->file('image')->storeAs('public/images', $image_name);
            if(File::exists(storage_path('app/' . $ship_existing->image))){
                File::delete(storage_path('app/' . $ship_existing->image));
            }
        }
        if($request->file('permit_document')){
            $permit_document_path = $request->file('permit_document')->store('permit_documents');
            if(File::exists(storage_path('app/' . $ship_existing->permit_document))){
                File::delete(storage_path('app/' . $ship_existing->permit_document));
            }
        }

        $ship_existing->update([
            'user_id' => auth()->user()->id,
            'code' => $ship_existing->code,
            'name' => $request->name,
            'owner' => $request->owner,
            'address' => $request->address,
            'size' => $request->size,
            'captain' => $request->captain,
            'total_member' => $request->total_member,
            'image' => env('APP_URL') . '/storage' . '/images' . '/' . $image_name ?? env('APP_URL') . '/storage' . $ship_existing->image,
            'licence_number' => $request->licence_number,
            'permit_document' => $permit_document_path ?? $ship_existing->permit_document_path
        ]);

        return response()->json([
            'code' => 200,
            'message' => 'Successfully registered the ship, please contact Admin for approval',
            'data' => $ship_existing
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ship  $ship
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ship $ship)
    {
        //
    }

    public function shipApproval(Request $request)
    {
        $ship = Ship::whereId($request->id)->first();
        $verivication = $request->is_verivication;
        $admin_note = $request->admin_note;

        if(!$ship){
            return response()->json([
                'code' => 400,
                'message' => 'Data ship not found!'
            ], 400);
        }

        $ship->is_verification = $verivication;
        $ship->admin_note = $admin_note;
        $ship->updated_by = auth()->user()->id;
        $ship->save();

        return response()->json([
            'code' => 200,
            'message' =>  $verivication == 1 ? 'Verification Successfully!' : 'Verification Rejected!',
            'data' => $ship
        ], 200);
    }

    public function shipDelete(Request $request)
    {
        $ship = Ship::whereId($request->id)->first();

        if(!$ship){
            return response()->json([
                'code' => 400,
                'message' => 'Data ship not found!'
            ], 400);
        }

        $ship->is_verification = 0;
        $ship->updated_by = auth()->user()->id;
        $ship->save();

        return response()->json([
            'code' => 200,
            'message' =>  'Ship has been deleted / not active!',
            'data' => $ship
        ], 200);
    }

    public function getShipPublic()
    {
        $ships = Ship::all()->makeHidden([
            'created_at',
            'updated_at',
            'permit_document',
            'updated_by',
            'id',
            'user_id'
        ]);

        return response()->json([
            'code' => 200,
            'message' =>  'Success',
            'data' => $ships
        ], 200);
    }
}
