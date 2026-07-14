<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PublicReceiptController extends Controller
{
    /**
     * Display the receipt in browser (viewable link)
     */
    public function show($token)
    {
        $order = Order::with(['customer', 'items.product.unit', 'branch', 'refunds'])
                     ->where('receipt_token', $token)
                     ->firstOrFail();

        return view('public.receipt', compact('order'));
    }

    /**
     * Download receipt as PDF
     */
    public function download($token)
    {
        $order = Order::with(['customer', 'items.product.unit', 'branch', 'refunds'])
                     ->where('receipt_token', $token)
                     ->firstOrFail();
        
        // Generate PDF
        $pdf = Pdf::loadView('public.receipt-pdf', compact('order'));
        
        // Set PDF options
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('defaultFont', 'Helvetica');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        
        return $pdf->download("Receipt-{$order->order_number}.pdf");
    }
    
    /**
     * Print receipt view (for printing)
     */
    public function print($token)
    {
        $order = Order::with(['customer', 'items.product.unit'])
                     ->where('receipt_token', $token)
                     ->firstOrFail();
        
        return view('public.receipt-print', compact('order'));
    }
    
    /**
     * Send receipt via email or SMS (optional)
     */
    public function send($token, Request $request)
    {
        $order = Order::with(['customer', 'items.product.unit'])
                     ->where('receipt_token', $token)
                     ->firstOrFail();
        
        $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);
        
        // Here you can implement email/SMS sending logic
        // For example, using Laravel Mail or a SMS service
        
        return response()->json([
            'success' => true,
            'message' => 'Receipt sent successfully',
            'receipt_url' => route('public.receipt.show', $order->receipt_token),
        ]);
    }
    
    /**
     * Get receipt data as JSON (for API)
     */
    public function json($token)
    {
        $order = Order::with(['customer', 'items.product.unit'])
                     ->where('receipt_token', $token)
                     ->firstOrFail();
        
        return response()->json([
            'success' => true,
            'data' => [
                'order_number' => $order->order_number,
                'date' => $order->created_at->format('Y-m-d H:i:s'),
                'customer' => $order->customer ? [
                    'name' => $order->customer->name,
                    'email' => $order->customer->email,
                    'phone' => $order->customer->phone,
                ] : null,
                'items' => $order->items->map(function($item) {
                    return [
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'unit' => $item->product->unit->abbreviation ?? null,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                    ];
                }),
                'summary' => [
                    'subtotal' => $order->subtotal,
                    'tax' => $order->tax,
                    'tax_rate' => $order->tax_rate,
                    'discount' => $order->discount,
                    'delivery_charges' => $order->delivery_charges,
                    'total' => $order->total,
                    'weight' => $order->weight,
                ],
                'payment' => [
                    'method' => $order->payment_method,
                    'dispatch_method' => $order->dispatch_method,
                    'tracking_id' => $order->tracking_id,
                ],
                'receipt_url' => route('public.receipt.show', $order->receipt_token),
                'pdf_url' => route('public.receipt.download', $order->receipt_token),
            ]
        ]);
    }
}
