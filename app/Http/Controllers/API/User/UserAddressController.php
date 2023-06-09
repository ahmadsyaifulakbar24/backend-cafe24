<?php

namespace App\Http\Controllers\API\User;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserAddressResource;
use App\Models\User;
use App\Models\UserAddress;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserAddressController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'user_id' => [
                'required', 
                Rule::exists('users', 'id')->where(function($query) {
                    return $query->where('status', 'active');
                })
            ], 
            'label' => ['required', 'string'],
            'recipients_name' => ['required', 'string'],
            'phone_number' => ['nullable', 'integer'],
            'province_id' => [ 'required', 'exists:provinces,id' ],
            'city_id' => [
                'required',
                Rule::exists('cities', 'id')->where(function($query) use ($request) {
                    return $query->where('province_id', $request->province_id);
                })
            ],
            'district_id' => [
                'required',
                Rule::exists('districts', 'id')->where(function($query) use ($request) {
                    return $query->where('city_id', $request->city_id);
                })
            ],
            'postal_code' => ['required', 'integer'],
            'address' => ['required', 'string'],
            'type' => ['required', 'in:receiver,alone']
        ]);

        $input = $request->all();

        $user = User::find($request->user_id);
        $check_main_address = $user->user_address()->where('main', '1')->count();
        if($check_main_address > 0) {
            $input['main'] = 0;
        }

        $user_address = $user->user_address()->create($input); 

        return ResponseFormatter::success(
            new UserAddressResource($user_address),
            $this->message('create')
        );
    }
    
    public function fetch(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id']
        ]);

        $user = User::findOrFail($request->user_id);
        $user_address = $user->user_address()->orderBy('created_at', 'desc')->get();
        return ResponseFormatter::success(
            UserAddressResource::collection($user_address),
            $this->message('get')
        );
    }

    public function show(UserAddress $user_address)
    {
        return ResponseFormatter::success(
            new UserAddressResource($user_address),
            $this->message('get')
        );
    } 

    public function update_main_address(UserAddress $user_address)
    {
        if(!empty($user_address)) {
            $user = $user_address->user;

            $user_address->update(['main' => 1]);

            $user->user_address()->where('id', '!=', $user_address->id)->update(['main' => 0]);

            return ResponseFormatter::success(new UserAddressResource($user_address), 'success update main address data');
        } else {
            return ResponseFormatter::error([
                'user_address_id' => 'invalid user address id'
            ], 'update main address failed', 422);
        }
    }

    public function update(Request $request, UserAddress $user_address) {
        $request->validate([
            'label' => ['required', 'string'],
            'recipients_name' => ['required', 'string'],
            'phone_number' => ['nullable', 'integer'],
            'province_id' => [ 'required', 'exists:provinces,id' ],
            'city_id' => [
                'required',
                Rule::exists('cities', 'id')->where(function($query) use ($request) {
                    return $query->where('province_id', $request->province_id);
                })
            ],
            'district_id' => [
                'required',
                Rule::exists('districts', 'id')->where(function($query) use ($request) {
                    return $query->where('city_id', $request->city_id);
                })
            ],
            'postal_code' => ['required', 'integer'],
            'address' => ['required', 'string'],
            'type' => ['required', 'in:receiver,alone']
        ]);

        $input = $request->all();
        $user_address->update($input);

        return ResponseFormatter::success(
            new UserAddressResource($user_address),
            $this->message('update')
        );
    }

    public function delete(UserAddress $user_address)
    {
        $user_address->delete();
        return ResponseFormatter::success(
            null,
            'success delete user address data'
        );
    }

    public function message ($type)
    {
        return 'success '.$type.' user address data';
    }
}
