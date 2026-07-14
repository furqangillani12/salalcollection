<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReceiptService
{
    public function generateReceipt(Order $order)
    {
        // Generate receipt content
        $receiptContent = view('admin.pos.receipt-template', [
            'order' => $order
        ])->render();

        // Generate PDF
        $pdf = Pdf::loadHTML($receiptContent);
        $pdfPath = 'receipts/'.$order->order_number.'.pdf';
        Storage::put('public/'.$pdfPath, $pdf->output());

        // Create receipt record
        $receipt = Receipt::create([
            'order_id' => $order->id,
            'receipt_number' => 'RCPT-'.strtoupper(uniqid()),
            'content' => $receiptContent,
            'pdf_path' => $pdfPath
        ]);

        return $receipt;
    }

    public function getReceiptPdf(Order $order)
    {
        if (!$order->receipt) {
            $this->generateReceipt($order);
            $order->refresh();
        }

        return Storage::path('public/'.$order->receipt->pdf_path);
    }
}
