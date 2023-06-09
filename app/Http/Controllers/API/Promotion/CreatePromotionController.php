<?php

namespace App\Http\Controllers\API\Promotion;

use App\Helpers\FileHelpers;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Promotion\PromotionResource;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CreatePromotionController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:png,jpg,jpeg,gif'],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required','date_format:Y-m-d H:i:s'],
            'end_date' => ['required', 'date_format:Y-m-d H:i:s']
        ]);

        $input = $request->all();
        $image = $request->file('image');

        if($image) {
            $input['image'] = FileHelpers::upload_file('promotion', $image);
        }

        $promotion = Promotion::create($input);
        return ResponseFormatter::success(
            new PromotionResource($promotion),
            'success get promotion data'
        );
    }
}
