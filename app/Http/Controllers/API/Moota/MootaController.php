<?php

namespace App\Http\Controllers\API\Moota;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class MootaController extends Controller
{
    public function bank()
    {
        $response = Http::withoutVerifying()
        ->withHeaders([
            'Authorization' => 'Bearer ' . env("MOOTA_API_TOKEN")
        ])->get(env("MOOTA_URL") . 'bank')
        ->json();
        
        $new_data = collect($response['data'])->filter(function($value) {
            return $value['is_active'] == true;
        })->values();
        $data = Arr::except($response, ['data']);
        $data['data'] = $new_data;
        return $data;
    }
}
