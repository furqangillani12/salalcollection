<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $order->order_number }}</title>
    <style>
        /* Use system fonts only */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        /* Simple layout for PDF */
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #3b82f6;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        
        .company-info {
            text-align: center;
            margin-bottom: 20px;
            font-size: 11px;
            color: #666;
        }
        
        .section {
            margin-bottom: 20px;
        }
        
        .section-title {
            background: #f3f4f6;
            padding: 8px 12px;
            font-weight: bold;
            border-left: 4px solid #3b82f6;
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th {
            background: #f3f4f6;
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        
        td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .summary {
            background: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .summary-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 14px;
            padding-top: 10px;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #666;
        }
        
        .total-row {
            background: #eff6ff;
            font-weight: bold;
        }
        
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        
        .qr-code img {
            max-width: 120px;
            height: auto;
        }
        
        /* Print styles */
        @media print {
            body {
                padding: 0;
                font-size: 10px;
            }
            
            .container {
                border: none;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $order->branch->name ?? 'AlMufeed Saqafti Markaz' }}</h1>
            <div class="company-info">
                <p>Islamic Books & Cultural Items</p>
                <p>{{ $order->branch->address ?? 'SALAL COLLECTION Traders PanjGirain Tehsil DaryaKhan District Bhakkar' }}</p>
                <p>Phone: {{ $order->branch->phone ?? '0300-7951919' }} (WhatsApp) | Email: Amt7212@gmail.com</p>
            </div>
        </div>
        
        <!-- Receipt Info -->
        <div class="section">
            <div class="section-title">RECEIPT INFORMATION</div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <div>
                    <strong>Receipt #:</strong> {{ $order->order_number }}<br>
                    <strong>Date:</strong> {{ $order->created_at?->format('d M, Y h:i A') ?? 'N/A' }}
                </div>
                <div>
                    <strong>Customer:</strong> {{ $order->customer?->name ?? 'Walk-in Customer' }}<br>
                    @if($order->customer?->phone)
                    <strong>Phone:</strong> {{ $order->customer->phone }}
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="section">
            <div class="section-title">ORDER ITEMS</div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product?->name ?? 'Deleted Product' }}</td>
                        <td class="text-center">{{ $item->quantity ?? 0 }}@if($item->product?->unit?->abbreviation) {{ $item->product->unit->abbreviation }}@endif</td>
                        <td class="text-right">Rs. {{ number_format($item->unit_price ?? 0, 2) }}</td>
                        <td class="text-right">Rs. {{ number_format($item->total_price ?? 0, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Payment Summary -->
        <div class="section">
            <div class="section-title">PAYMENT SUMMARY</div>
            <div class="summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>Rs. {{ number_format($order->subtotal ?? 0, 2) }}</span>
                </div>
                
                @if(($order->tax ?? 0) > 0)
                <div class="summary-row">
                    <span>Tax ({{ $order->tax_rate ?? 0 }}%):</span>
                    <span>Rs. {{ number_format($order->tax ?? 0, 2) }}</span>
                </div>
                @endif
                
                @if(($order->delivery_charges ?? 0) > 0)
                <div class="summary-row">
                    <span>Delivery Charges:</span>
                    <span>Rs. {{ number_format($order->delivery_charges ?? 0, 2) }}</span>
                </div>
                @endif
                
                @if(($order->discount ?? 0) > 0)
                <div class="summary-row" style="color: #059669;">
                    <span>Discount:</span>
                    <span>- Rs. {{ number_format($order->discount ?? 0, 2) }}</span>
                </div>
                @endif
                
                <div class="summary-row total-row">
                    <span>GRAND TOTAL:</span>
                    <span>Rs. {{ number_format($order->total ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        
        <!-- Additional Information -->
        <div class="section">
            <div class="section-title">ADDITIONAL INFORMATION</div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <strong>Payment Method:</strong><br>
                    {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}
                </div>
                
                @if($order->dispatch_method)
                <div>
                    <strong>Dispatch Method:</strong><br>
                    {{ $order->dispatch_method }}
                </div>
                @endif
                
                @if($order->tracking_id)
                <div>
                    <strong>Tracking ID:</strong><br>
                    {{ $order->tracking_id }}
                </div>
                @endif
                
                @if($order->weight)
                <div>
                    <strong>Weight:</strong><br>
                    {{ $order->weight }} kg
                </div>
                @endif
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business! We appreciate your purchase.</p>
            <p>Customer Support: {{ $order->branch->phone ?? '0300-7951919' }} | WhatsApp: Available 24/7</p>
            <p>This is a computer generated receipt. No signature required.</p>
            <p>Generated on: {{ now()->format('d M, Y h:i A') }}</p>
            @if($order->receipt_url)
            <p>View online receipt: {{ $order->receipt_url }}</p>
            @endif
        </div>
    </div>
</body>
</html>
