<?php

namespace App\Http\Controllers\API\Promotion;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeletePromotionController extends Controller
{
    public function __invoke(Promotion $promotion)
    {
        Storage::disk('public')->delete($promotion->image);
        $promotion->delete();
        return ResponseFormatter::success(
            null,
            'success delete promotion data'
        );
    }
}
