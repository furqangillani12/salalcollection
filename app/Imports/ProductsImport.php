<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    private $rowCount = 0;
    public function model(array $row)
    {
        $this->rowCount++;
        Log::debug('Importing row:', $row);
        // Clean up headers from your current file
        $row = [
            'name' => isset($row['name']) ? $row['name'] : null,
            'barcode' => isset($row['barcode']) ? $row['barcode'] : null,
            'category' => isset($row['category']) ? $row['category'] : null,
            'description' => isset($row['description']) ? $row['description'] : null, // Notice space in header
            'price' => isset($row['price']) ? $row['price'] : null,
            'cost_price' => isset($row['cost_price']) ? $row['cost_price'] : null, // Notice space in header
            'stock_quantity' => isset($row['stock_quantity']) ? $row['stock_quantity'] : 0, // Notice truncated header
            'reorder_level' => isset($row['reorder_level']) ? $row['reorder_level'] : 5, // Notice truncated header
            'is_active' => isset($row['is_active']) ? $row['is_active'] : true // Notice malformed header
        ];

        $category = Category::firstOrCreate([
            'name' => $row['category']
        ]);

        return new Product([
            'name' => $row['name'],
            'barcode' => $row['barcode'],
            'category_id' => $category->id,
            'description' => $row['description'],
            'price' => $row['price'],
            'cost_price' => isset($row['cost_price']) ? $row['cost_price'] : $row['price'],
            'stock_quantity' => $row['stock_quantity'],
            'reorder_level' => $row['reorder_level'],
            'is_active' => strtolower($row['is_active']) === 'yes' ? true : false,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
        ];
    }
    public function getRowCount()
    {
        return $this->rowCount;
    }
}
