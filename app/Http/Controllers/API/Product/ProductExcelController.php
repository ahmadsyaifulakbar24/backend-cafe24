<?php

namespace App\Http\Controllers\API\Product;

use App\Exports\ProductCombinationExcel;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Imports\ProductCombinationUpdateExcel;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductExcelController extends Controller
{
    public function product_combination_excel() 
    {
        return Excel::download(new ProductCombinationExcel, 'product.xlsx');
    }

    public function product_combination_import(Request $request) 
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx']
        ]);

        Excel::import(new ProductCombinationUpdateExcel, $request->file);

        return ResponseFormatter::success(null, 'success update stock product');
    }
}
