<?php

namespace App\Imports;

use App\Models\ProductCombination;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductCombinationUpdateExcel implements ToCollection, WithHeadingRow, WithValidation
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            $product_combination_id = $row['product_combination_id'];
            $stock = $row['stock'];

            ProductCombination::where('id', $product_combination_id)->update([
                'stock' => $stock,
            ]);

        }
    }

    public function rules(): array
    {
        return [
            'product_combination_id' => ['required', 'exists:product_combinations,id'],
            'stock' => ['required', 'integer'],
        ];
    }
}
