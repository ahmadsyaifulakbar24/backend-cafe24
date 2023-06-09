<?php

namespace App\Http\Controllers\API\Promotion;

use App\Helpers\FileHelpers;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Promotion\PromotionResource;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UpdatePromotionController extends Controller
{
    public function __invoke(Request $request, Promotion $promotion)
    {
        $request->validate([
            'image' => ['nullable', 'file', 'mimes:png,jpg,jpeg,gif'],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required','date_format:Y-m-d H:i:s'],
            'end_date' => ['required', 'date_format:Y-m-d H:i:s']
        ]);

        $input = $request->all();
        $image = $request->file('image');

        if($image) {
            $input['image'] = FileHelpers::upload_file('promotion', $image);
            Storage::disk('public')->delete($promotion->image);
        }

        $promotion->update($input);
        
        return ResponseFormatter::success(
            new PromotionResource($promotion),
            'success update promotion data'
        );
    }
}
