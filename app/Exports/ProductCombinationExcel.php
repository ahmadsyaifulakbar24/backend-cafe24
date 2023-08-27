<?php

namespace App\Exports;

use App\Models\ProductCombination;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ProductCombinationExcel implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        return view('excel.product_combinatioin', [
            'product_combinations' => ProductCombination::all()
        ]);
    }
}
