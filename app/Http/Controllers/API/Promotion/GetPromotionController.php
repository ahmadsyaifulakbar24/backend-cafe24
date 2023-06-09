<?php

namespace App\Http\Controllers\API\Promotion;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Promotion\PromotionResource;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GetPromotionController extends Controller
{
    public function fetch(Request $request)
    {
        $request->validate([
            'status' => ['required', 'in:all,active'],
            'paginate' => ['nullable', 'in:0,1'],
            'limit' => ['nullable', 'integer'],
        ]);

        $status = $request->status;
        $paginate = $request->paginate;
        $limit = $request->input('limit', 10);

        $promotion = Promotion::orderBy('created_at', 'desc');

        if($status == 'active') {
            $promotion->where([
                ['start_date', '<=', Carbon::now()],
                ['end_date', '>=', Carbon::now()],
            ]);
        }

        $result = $paginate ? $promotion->paginate($limit) : $promotion->get();

        return ResponseFormatter::success(
            PromotionResource::collection($result)->response()->getData(true),
            'success get promotion data',
        );
    }

    public function show(Promotion $promotion)
    {
        return ResponseFormatter::success(
            new PromotionResource($promotion),
            'success show promotion data',
        );
    }
}
