<?php


namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Product::with('category')->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Barcode',
            'Category',
            'Description',
            'Price',
            'Cost Price',
            'Stock Quantity',
            'Reorder Level',
            'Active'
        ];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->barcode,
            $product->category->name,
            $product->description,
            $product->price,
            $product->cost_price,
            $product->stock_quantity,
            $product->reorder_level,
            $product->is_active ? 'Yes' : 'No'
        ];
    }
}
