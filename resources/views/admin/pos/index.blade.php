@extends('layouts.admin')

@section('title', 'Point of Sale')

@push('styles')
    <style>
        body {
            overflow: hidden !important;
        }

        /* Force the main content area to not overflow */
        main.flex-1 {
            overflow: hidden !important;
            min-width: 0 !important;
            padding: 0 !important;
        }

        .main-div {
            margin: 0 !important;
            padding: 0 !important;
            height: 100%;
        }

        .main-div > * {
            margin: 0 !important;
        }

        .pos-root {
            display: flex;
            height: calc(100vh - 64px);
            max-height: calc(100vh - 64px);
            background: #f0f4f8;
            overflow: hidden;
            width: 100%;
            max-width: 100%;
        }

        /* ── LEFT: Product Panel ───────────────────────────────────── */
        .pos-products {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding: 12px 12px 12px 12px;
            gap: 10px;
        }

        /* Sticky toolbar at top of products */
        .pos-toolbar {
            flex-shrink: 0;
            background: #fff;
            border-radius: 10px;
            padding: 10px 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pos-toolbar .search-wrap {
            position: relative;
            flex: 1;
        }

        .pos-toolbar .search-wrap svg {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            color: #9ca3af;
            pointer-events: none;
        }

        .pos-toolbar input {
            width: 100%;
            padding: 8px 12px 8px 34px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13.5px;
            background: #f9fafb;
            outline: none;
            transition: border-color .15s;
        }

        .pos-toolbar input:focus {
            border-color: #3b82f6;
            background: #fff;
        }

        /* Category tabs */
        .pos-cats {
            flex-shrink: 0;
            display: flex;
            gap: 7px;
            overflow-x: auto;
            scrollbar-width: none;
            padding-bottom: 2px;
        }

        .pos-cats::-webkit-scrollbar {
            display: none;
        }

        .cat-tab {
            flex-shrink: 0;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12.5px;
            font-weight: 600;
            border: 1.5px solid #e5e7eb;
            background: #fff;
            color: #6b7280;
            cursor: pointer;
            transition: all .15s;
        }

        .cat-tab:hover {
            border-color: #3b82f6;
            color: #2563eb;
        }

        .cat-tab.active {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        /* THE KEY FIX: product grid area is flex:1 with overflow-y:auto */
        .pos-grid-wrap {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            border-radius: 10px;
        }

        .pos-grid-wrap::-webkit-scrollbar {
            width: 5px;
        }

        .pos-grid-wrap::-webkit-scrollbar-track {
            background: transparent;
        }

        .pos-grid-wrap::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .pos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(145px, 1fr));
            gap: 10px;
            padding: 2px 2px 8px;
        }

        /* Product cards */
        .product-item {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
            transition: all .15s ease;
        }

        .product-item:hover {
            border-color: #3b82f6;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, .15);
        }

        .product-item:active {
            transform: scale(.98);
        }

        .product-item .img-area {
            height: 110px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .product-item .img-area img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-item .img-area i {
            font-size: 2rem;
            color: #d1d5db;
        }

        .product-item .card-info {
            padding: 8px 10px 10px;
        }

        .product-item h3 {
            font-size: 12.5px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.3;
            margin-bottom: 4px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .product-item .price-text {
            font-size: 13px;
            font-weight: 800;
            color: #2563eb;
        }

        .product-item .barcode-text {
            font-size: 10.5px;
            color: #9ca3af;
            margin-top: 2px;
        }

        .product-item .stock-text {
            font-size: 10.5px;
            color: #9ca3af;
            margin-top: 1px;
        }

        .product-item .stock-low {
            color: #ef4444;
        }

        .product-item .stock-ok {
            color: #16a34a;
        }

        /* ── RIGHT: Cart Panel ─────────────────────────────────────── */
        .pos-cart {
            width: 300px;
            min-width: 280px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            background: #fff;
            border-left: 1px solid #e5e7eb;
        }

        /* Row 1: Cart Header - Fixed */
        .cart-head {
            flex-shrink: 0;
            background: #fff;
            color: #1e293b;
            padding: 12px 14px;
            border-bottom: 1px solid #e5e7eb;
        }

        .cart-head h2 {
            font-size: 14px;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: .3px;
            color: #1e293b;
        }

        .customer-wrap {
            position: relative;
        }

        /* Row 2: Cart Items - Takes remaining space, scrollable */
        .cart-items-wrap {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: 10px 12px;
            background: #f8fafc;
        }

        .cart-items-wrap::-webkit-scrollbar {
            width: 3px;
        }

        .cart-items-wrap::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 3px;
        }

        .empty-cart-message {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #cbd5e1;
            gap: 8px;
            padding: 20px;
        }

        .empty-cart-message i {
            font-size: 2.5rem;
        }

        .empty-cart-message p {
            font-size: 13px;
        }

        .cart-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            background: #fff;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            padding: 8px;
            margin-bottom: 6px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .cart-item-meta {
            flex: 1;
            min-width: 0;
        }

        .cart-item-meta .name {
            font-size: 12.5px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.3;
        }

        .cart-item-meta .unit {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .cart-item-total {
            font-size: 12.5px;
            font-weight: 800;
            color: #1e293b;
            white-space: nowrap;
        }

        .qty-ctrl {
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .qty-btn {
            width: 22px;
            height: 22px;
            border: 1.5px solid #e2e8f0;
            border-radius: 5px;
            background: #fff;
            font-size: 14px;
            font-weight: 800;
            color: #3b82f6;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .1s;
        }

        .qty-btn:hover {
            background: #eff6ff;
            border-color: #3b82f6;
        }

        .qty-num {
            font-size: 12.5px;
            font-weight: 800;
            min-width: 18px;
            text-align: center;
            color: #1e293b;
        }

        .remove-item-btn {
            background: none;
            border: none;
            color: #fca5a5;
            cursor: pointer;
            padding: 2px;
            font-size: 13px;
        }

        .remove-item-btn:hover {
            color: #ef4444;
        }

        /* Row 3: Cart Footer - Fixed height based on content, scrollable if needed */
        .cart-footer {
            flex-shrink: 0;
            max-height: 50%;
            overflow-y: auto;
            border-top: 1.5px solid #f1f5f9;
            background: #fff;
        }

        /* Totals */
        .totals-block {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        .trow {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12.5px;
            margin-bottom: 5px;
        }

        .trow .lbl {
            color: #6b7280;
        }

        .trow .val {
            font-weight: 700;
            color: #1e293b;
        }

        .trow.grand {
            padding-top: 7px;
            border-top: 2px dashed #e5e7eb;
            margin-top: 4px;
        }

        .trow.grand .lbl {
            font-size: 13.5px;
            font-weight: 800;
            color: #1e293b;
        }

        .trow.grand .val {
            font-size: 16px;
            font-weight: 900;
            color: #2563eb;
        }

        .inline-num {
            width: 60px;
            text-align: right;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 3px 6px;
            font-size: 12px;
            outline: none;
        }

        .inline-num:focus {
            border-color: #3b82f6;
        }

        /* Payment methods grid */
        .pay-section {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        .sec-label {
            font-size: 10.5px;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 7px;
        }

        .pm-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 5px;
        }

        .pm-btn {
            padding: 6px 4px;
            border: 1.5px solid #e5e7eb;
            border-radius: 7px;
            background: #fff;
            font-size: 11.5px;
            font-weight: 600;
            color: #6b7280;
            cursor: pointer;
            text-align: center;
            transition: all .12s;
        }

        .pm-btn:hover {
            border-color: #3b82f6;
            color: #2563eb;
        }

        .pm-btn.active {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #2563eb;
        }

        /* Partial payment box */
        .payment-box {
            background: #f0f9ff;
            border: 1.5px solid #bae6fd;
            border-radius: 10px;
            padding: 10px 12px;
            margin: 0 14px 10px;
        }

        .payment-box label {
            font-size: 11px;
            font-weight: 800;
            color: #0369a1;
            margin-bottom: 5px;
            display: block;
        }

        .payment-big-input {
            width: 100%;
            border: 2px solid #38bdf8;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 17px;
            font-weight: 900;
            color: #0c4a6e;
            outline: none;
            background: #fff;
        }

        .payment-big-input:focus {
            border-color: #0ea5e9;
        }

        .bal-summary {
            margin-top: 8px;
            font-size: 11.5px;
        }

        .bal-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }

        .bal-row.prev {
            color: #c2410c;
        }

        .bal-row.remaining {
            color: #dc2626;
        }

        .bal-row.change {
            color: #16a34a;
        }

        .bal-row.new-bal {
            font-weight: 800;
            border-top: 1px solid #bae6fd;
            padding-top: 5px;
            margin-top: 3px;
        }

        /* Dispatch */
        .dispatch-section {
            padding: 0 14px 10px;
        }

        .pos-select {
            width: 100%;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            padding: 7px 10px;
            font-size: 13px;
            color: #374151;
            outline: none;
            background: #fff;
            cursor: pointer;
        }

        .pos-select:focus {
            border-color: #3b82f6;
        }

        .action-section {
            padding: 10px 14px 24px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .btn-process {
            width: 100%;
            padding: 13px 16px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(37, 99, 235, .35);
            transition: all .15s;
            letter-spacing: .2px;
        }

        .btn-process:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(37, 99, 235, .4);
        }

        .btn-process:active {
            transform: none;
        }

        .btn-process:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-clear {
            width: 100%;
            padding: 9px;
            border: 1.5px solid #fca5a5;
            background: #fff;
            color: #ef4444;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all .15s;
        }

        .btn-clear:hover {
            background: #fef2f2;
            border-color: #ef4444;
        }

        /* Customer search icon button */
        .cust-search-ico {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, .6);
            cursor: pointer;
            font-size: 14px;
            padding: 0;
        }

        /* selected customer info block */
        #selectedCustomerInfo {
            margin-top: 8px;
            padding: 6px 10px;
            background: rgba(255, 255, 255, .12);
            border-radius: 8px;
            font-size: 12px;
            display: none;
        }

        #selectedCustomerInfo .inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* ── MOBILE CUSTOMER BAR (hidden on desktop) ────────────── */
        .mobile-customer-bar {
            display: none;
        }

        /* ── MOBILE FLOATING CART BAR ──────────────────────────────── */
        .mobile-cart-bar {
            display: none;
        }

        /* ── MOBILE CART OVERLAY ───────────────────────────────────── */
        .mobile-cart-overlay {
            display: none;
        }

        .mobile-cart-close-btn {
            display: none;
        }

        /* ── TABLET (768px - 1024px) ────────────────────────────────── */
        @media (min-width: 768px) and (max-width: 1024px) {
            .pos-cart {
                width: 260px;
                min-width: 260px;
            }

            .pos-products {
                padding: 8px;
            }

            .pos-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 8px;
            }

            .product-item .img-area {
                height: 80px;
            }

            .product-item .card-info {
                padding: 6px 8px 8px;
            }

            .product-item h3 {
                font-size: 11.5px;
            }

            .product-item .price-text {
                font-size: 12px;
            }

            .pm-grid {
                grid-template-columns: 1fr 1fr 1fr;
                gap: 4px;
            }

            .pm-btn {
                font-size: 10.5px;
                padding: 5px 3px;
            }

            .payment-box {
                margin: 0 10px 8px;
                padding: 8px 10px;
            }

            .totals-block {
                padding: 8px 10px;
            }

            .trow {
                font-size: 11.5px;
            }

            .cart-head {
                padding: 10px 10px;
            }

            .cart-head h2 {
                font-size: 13px;
                margin-bottom: 8px;
            }

            .pos-select {
                font-size: 12px;
                padding: 6px 8px;
            }
        }

        /* ── SMALL TABLET (768px - 850px) ──────────────────────────── */
        @media (min-width: 768px) and (max-width: 850px) {
            .pos-cart {
                width: 240px;
                min-width: 240px;
            }

            .pos-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }

            .product-item .img-area {
                height: 65px;
            }
        }

        /* ── MOBILE ─────────────────────────────────────────────────── */
        @media (max-width: 767px) {
            body {
                overflow: hidden !important;
            }

            /* Override parent layout padding/margin for POS */
            .main-content-mobile,
            main.flex-1 {
                padding: 0 !important;
                padding-top: 64px !important;
                margin: 0 !important;
            }

            .main-div {
                margin-top: 0 !important;
                padding: 0 !important;
            }

            .main-div.space-y-4>*+* {
                margin-top: 0 !important;
            }

            /* Hide desktop header on POS mobile */
            main header.mb-6 {
                display: none !important;
            }

            .pos-root {
                flex-direction: column;
                height: calc(100vh - 64px);
                max-height: calc(100vh - 64px);
                position: relative;
            }

            /* Products take full space minus floating bar */
            .pos-products {
                flex: 1;
                height: auto;
                min-height: 0;
                padding: 8px 8px 0 8px;
                padding-bottom: 70px;
                /* space for floating cart bar */
            }

            .pos-toolbar {
                padding: 8px 10px;
                gap: 6px;
            }

            .pos-toolbar input {
                padding: 7px 10px 7px 30px;
                font-size: 13px;
            }

            .cat-tab {
                padding: 5px 12px;
                font-size: 11.5px;
            }

            .pos-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
            }

            .product-item .img-area {
                height: 70px;
            }

            .product-item .card-info {
                padding: 5px 6px 7px;
            }

            .product-item h3 {
                font-size: 11px;
                -webkit-line-clamp: 1;
            }

            .product-item .price-text {
                font-size: 11.5px;
            }

            .product-item .barcode-text,
            .product-item .stock-text {
                font-size: 9.5px;
            }

            /* Hide desktop cart panel */
            .pos-cart {
                display: none;
            }

            /* ── Mobile customer selector ── */
            .mobile-customer-bar {
                display: flex;
                position: relative;
                align-items: center;
                gap: 8px;
                flex-shrink: 0;
                background: #fff;
                border-radius: 10px;
                padding: 8px 10px;
                box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
            }

            /* ── Floating cart bar at bottom ── */
            .mobile-cart-bar {
                display: flex;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 100;
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                color: #fff;
                padding: 12px 16px;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 -4px 20px rgba(0, 0, 0, .15);
                cursor: pointer;
                -webkit-tap-highlight-color: transparent;
                min-height: 56px;
            }

            .mobile-cart-bar .cart-bar-left {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .mobile-cart-bar .cart-bar-badge {
                background: #fff;
                color: #2563eb;
                font-size: 13px;
                font-weight: 900;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .mobile-cart-bar .cart-bar-text {
                font-size: 14px;
                font-weight: 700;
            }

            .mobile-cart-bar .cart-bar-total {
                font-size: 16px;
                font-weight: 900;
            }

            /* ── Full-screen cart overlay ── */
            .mobile-cart-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 200;
                background: #fff;
                flex-direction: column;
                overflow: hidden;
            }

            .mobile-cart-overlay.open {
                display: flex;
            }

            .mobile-cart-overlay .overlay-header {
                flex-shrink: 0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 12px 16px;
                background: #fff;
                border-bottom: 1px solid #e5e7eb;
            }

            .mobile-cart-overlay .overlay-header h2 {
                font-size: 16px;
                font-weight: 800;
                color: #1e293b;
                margin: 0;
            }

            .mobile-cart-close-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border-radius: 50%;
                border: none;
                background: #f1f5f9;
                color: #64748b;
                font-size: 18px;
                cursor: pointer;
            }

            .mobile-cart-close-btn:hover {
                background: #e2e8f0;
            }

            .mobile-cart-overlay .overlay-body {
                flex: 1;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }

            /* Inside the overlay, re-show cart sections */
            .mobile-cart-overlay .m-cart-head {
                padding: 10px 14px;
                border-bottom: 1px solid #e5e7eb;
                background: #fff;
            }

            .mobile-cart-overlay .m-cart-items-wrap {
                padding: 10px 12px;
                background: #f8fafc;
                min-height: 80px;
            }

            .mobile-cart-overlay .m-cart-footer {
                background: #fff;
                border-top: 1.5px solid #f1f5f9;
            }

            /* Reuse all cart styles inside overlay */
            .mobile-cart-overlay .cart-item {
                display: flex;
                align-items: flex-start;
                gap: 8px;
                background: #fff;
                border: 1px solid #f1f5f9;
                border-radius: 8px;
                padding: 8px;
                margin-bottom: 6px;
                box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
            }

            .mobile-cart-overlay .empty-cart-message {
                padding: 30px 20px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                color: #cbd5e1;
                gap: 8px;
            }

            .mobile-cart-overlay .action-section {
                padding: 10px 14px 20px;
                position: sticky;
                bottom: 0;
                background: #fff;
                border-top: 1px solid #e5e7eb;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, .05);
            }
        }

        @media (max-width: 400px) {
            .pos-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 6px;
            }

            .product-item .img-area {
                height: 65px;
            }
        }

        /* ── MEDIUM DESKTOP (1025px - 1279px) ── */
        @media (min-width: 1025px) and (max-width: 1279px) {
            .pos-cart {
                width: 300px;
                min-width: 280px;
            }
        }

        /* ── LARGE DESKTOP (1280px+) ── */
        @media (min-width: 1280px) {
            .pos-cart {
                width: 340px;
                min-width: 320px;
            }

            .pos-grid {
                grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
            }
        }

        /* ── Dark mode support ── */
        @media (prefers-color-scheme: dark) {
            .pos-root {
                background: #111827;
            }

            .pos-toolbar {
                background: #1f2937;
                box-shadow: 0 1px 4px rgba(0, 0, 0, .3);
            }

            .pos-toolbar input {
                background: #374151;
                border-color: #4b5563;
                color: #f3f4f6;
            }

            .pos-toolbar input:focus {
                border-color: #3b82f6;
                background: #1f2937;
            }

            .cat-tab {
                background: #1f2937;
                border-color: #4b5563;
                color: #d1d5db;
            }

            .cat-tab.active {
                background: #2563eb;
                border-color: #2563eb;
                color: #fff;
            }

            .cat-tab:hover {
                background: #374151;
            }

            .product-item {
                background: #1f2937;
                border-color: #374151;
            }

            .product-item:hover {
                border-color: #3b82f6;
            }

            .product-item .img-area {
                background: #374151;
            }

            .product-item .img-area i {
                color: #6b7280;
            }

            .product-item h3 {
                color: #f3f4f6;
            }

            .product-item .price-text {
                color: #60a5fa;
            }

            .product-item .barcode-text {
                color: #9ca3af;
            }

            .product-item .stock-ok {
                color: #4ade80;
            }

            .product-item .stock-low {
                color: #f87171;
            }

            .pos-cart {
                background: #1e293b;
            }

            .cart-head {
                background: #1e293b;
                border-color: #374151;
            }

            .cart-head h2 {
                color: #f3f4f6;
            }

            .cart-items {
                background: #111827;
            }

            .cart-item {
                background: #1f2937;
                border-color: #374151;
            }

            .cart-footer {
                background: #1e293b;
                border-color: #374151;
            }

            .trow .lbl {
                color: #d1d5db;
            }

            .trow .val {
                color: #f3f4f6;
            }

            .mobile-customer-bar {
                background: #1f2937;
                box-shadow: 0 1px 4px rgba(0, 0, 0, .3);
            }

            #mobileCustomerSearch {
                background: #374151 !important;
                border-color: #4b5563 !important;
                color: #f3f4f6 !important;
            }

            #mobileCustomerName {
                color: #f3f4f6 !important;
            }

            #mobileCustomerDropdown {
                background: #1f2937 !important;
                border-color: #4b5563 !important;
            }

            #mobileCustomerDropdown [data-value] {
                border-color: #374151 !important;
                color: #f3f4f6;
            }

            #mobileCustomerDropdown [data-value] strong {
                color: #f3f4f6;
            }

            #mobileCustomerDropdown [data-value]:hover {
                background: #374151 !important;
            }

            .mobile-cart-bar {
                background: #1e40af;
            }

            .mobile-cart-overlay {
                background: #111827;
            }

            .mobile-cart-overlay .overlay-header {
                background: #1f2937;
                border-color: #374151;
            }

            .mobile-cart-overlay .overlay-header h2 {
                color: #f3f4f6;
            }

            .mobile-cart-close-btn {
                background: #374151;
                color: #d1d5db;
            }

            .mobile-cart-overlay .m-cart-items-wrap {
                background: #111827;
            }

            .mobile-cart-overlay .m-cart-footer {
                background: #1f2937;
            }

            .mobile-cart-overlay .cart-item {
                background: #1f2937;
            }

            .mobile-cart-overlay .empty-cart-message {
                color: #6b7280;
            }

            .pm-btn {
                background: #374151;
                color: #d1d5db;
                border-color: #4b5563;
            }

            .pm-btn.active {
                background: #2563eb;
                color: #fff;
            }

            .pos-select {
                background: #374151;
                color: #f3f4f6;
                border-color: #4b5563;
            }

            .payment-big-input {
                background: #374151 !important;
                color: #f3f4f6 !important;
                border-color: #4b5563 !important;
            }

            .sec-label {
                color: #d1d5db;
            }

            .inline-num {
                background: #374151;
                color: #f3f4f6;
                border-color: #4b5563;
            }

            #productEmpty {
                color: #6b7280;
            }

            #productLoader i {
                color: #6b7280;
            }
        }
    </style>
@endpush

@section('content')
    <div class="pos-root">

        {{-- ══════════════════════════════════════════════════════
         LEFT — PRODUCTS PANEL
    ══════════════════════════════════════════════════════ --}}
        <div class="pos-products">

            {{-- Mobile-only customer selector (above product search on mobile) --}}
            <div class="mobile-customer-bar" id="mobileCustomerBar">
                <div style="position:relative;flex:1;">
                    <input type="text" id="mobileCustomerSearch" placeholder="Search customer..." autocomplete="off"
                        style="width:100%;padding:8px 10px 8px 32px;background:#fff;border:1.5px solid #e5e7eb;border-radius:8px;color:#1e293b;font-size:13px;outline:none;">
                    <i class="fas fa-user"
                        style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:12px;pointer-events:none;"></i>
                </div>
                <div id="mobileCustomerDropdown"
                    style="display:none;position:absolute;top:100%;left:0;right:0;background:#fff;border:1.5px solid #e5e7eb;border-radius:0 0 10px 10px;max-height:200px;overflow-y:auto;z-index:50;box-shadow:0 4px 12px rgba(0,0,0,.1);">
                </div>
                <div id="mobileCustomerSelected" style="display:none;align-items:center;gap:6px;flex:1;">
                    <span id="mobileCustomerName"
                        style="font-size:12px;font-weight:600;color:#1e293b;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></span>
                    <button onclick="clearMobileCustomer()"
                        style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;width:28px;height:28px;font-size:12px;cursor:pointer;flex-shrink:0;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            {{-- Search toolbar --}}
            <div class="pos-toolbar">
                <div class="search-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                            clip-rule="evenodd" />
                    </svg>
                    <input type="text" id="productSearch" placeholder="Search products by name or barcode...">
                </div>
            </div>

            {{-- Category tabs --}}
            <div class="pos-cats">
                <button class="cat-tab active" data-category="all">All Products</button>
                @foreach ($categories as $category)
                    <button class="cat-tab" data-category="{{ $category->id }}">{{ $category->name }}</button>
                @endforeach
            </div>

            {{-- Scrollable product area (loaded via AJAX) --}}
            <div class="pos-grid-wrap" id="productGridWrap">
                <div class="pos-grid" id="productGrid"></div>
                <div id="productLoader" style="text-align:center;padding:20px;display:none;">
                    <i class="fas fa-spinner fa-spin" style="font-size:24px;color:#6b7280;"></i>
                </div>
                <div id="productEmpty" style="text-align:center;padding:40px;display:none;color:#9ca3af;">
                    <i class="fas fa-box-open" style="font-size:36px;margin-bottom:8px;display:block;"></i>
                    No products found
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════════════════════════
         RIGHT — CART PANEL (Desktop only, 3-row flex column)
    ══════════════════════════════════════════════════════ --}}
        <div class="pos-cart" data-pos-route="{{ route('admin.pos.store') }}">

            {{-- ROW 1: HEADER --}}
            <div class="cart-head">
                <h2 style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                    <span><i class="fas fa-shopping-cart"></i> Current Order</span>
                    <span class="js-next-order-number" style="font-size:11px;font-weight:700;color:#2563eb;background:#eff6ff;border:1px solid #dbeafe;padding:3px 8px;border-radius:999px;letter-spacing:.2px;">#{{ $nextOrderNumber }}</span>
                </h2>

                {{-- Customer select with search --}}
                <div class="customer-wrap" style="position:relative;">
                    <input type="text" id="customerSearchInput" placeholder="Search or select customer..."
                        autocomplete="off"
                        style="width:100%;padding:8px 10px;background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:8px;color:#1e293b;font-size:13px;outline:none;"
                        onfocus="this.style.borderColor='#3b82f6';this.style.background='#fff'"
                        onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb'">

                    {{-- Hidden actual select --}}
                    <select id="customerSelect" style="display:none;">
                        <option value="">Walk-in Customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" data-type="{{ $customer->customer_type }}"
                                data-barcode="{{ $customer->barcode }}" data-name="{{ $customer->name }}"
                                data-phone="{{ $customer->phone }}"
                                data-credit-enabled="{{ $customer->credit_enabled ? '1' : '0' }}"
                                data-credit-limit="{{ $customer->credit_limit }}"
                                data-credit-balance="{{ $customer->current_balance }}"
                                data-credit-available="{{ $customer->available_credit }}">
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Dropdown results --}}
                    <div id="customerResults"
                        style="display:none;position:absolute;left:0;right:0;top:calc(100% + 4px);background:#fff;border:1px solid #e5e7eb;border-radius:8px;max-height:220px;overflow-y:auto;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,.15);">
                    </div>
                </div>

                {{-- Hidden customer type select --}}
                <select class="customer-type-select" style="display:none;">
                    <option value="walkin" selected>Walk-in</option>
                    <option value="reseller">Reseller</option>
                    <option value="wholesale">Wholesale</option>
                </select>

                {{-- Selected customer info --}}
                <div id="selectedCustomerInfo"
                    style="display:none;margin-top:8px;padding:6px 10px;background:rgba(255,255,255,.12);border-radius:8px;font-size:12px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <span id="selectedCustomerName" style="font-weight:700;font-size:13px;"></span>
                            <span id="selectedCustomerType" style="font-size:11px;opacity:.7;margin-left:5px;"></span>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span id="customerDueBadge"
                                style="display:none;background:#ef4444;color:#fff;font-size:10px;font-weight:800;padding:2px 7px;border-radius:10px;"></span>
                            <button onclick="clearCustomerSelection()"
                                style="background:none;border:none;color:rgba(0, 0, 0, 0.6);font-size:11px;cursor:pointer;">
                                Clear</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ROW 2: CART ITEMS --}}
            <div class="cart-items-wrap">
                <div class="cart-items"></div>
                <div class="empty-cart-message">
                    <i class="fas fa-shopping-cart"></i>
                    <p style="font-weight:600;">Cart is empty</p>
                    <p style="font-size:11px;opacity:.7;">Tap products to add</p>
                </div>
            </div>

            {{-- ROW 3: FOOTER --}}
            <div class="cart-footer">

                {{-- Totals --}}
                <div class="totals-block">
                    <div class="trow">
                        <span class="lbl">Subtotal</span>
                        <span class="val subtotal">Rs. 0.00</span>
                    </div>
                    <div class="trow">
                        <span class="lbl">Tax
                            <select id="tax_type" class="inline-num"
                                style="width:50px;padding:2px 4px;font-size:11px;"
                                onchange="updateCartDisplay();updateBalanceSummary()">
                                <option value="percent">%</option>
                                <option value="fixed">Rs.</option>
                            </select>
                            <input type="number" id="custom_tax" class="inline-num" value="{{ $tax_rate }}"
                                min="0" step="0.01" oninput="updateCartDisplay();updateBalanceSummary()">
                        </span>
                        <span class="val tax">Rs. 0.00</span>
                    </div>
                    {{-- Package search --}}
                    <div style="margin-bottom:4px;">
                        <div style="position:relative;">
                            <input type="text" id="packageSearch" placeholder="Apply package (type name/code)..."
                                autocomplete="off"
                                style="width:100%;padding:4px 8px;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:6px;font-size:11px;color:#166534;outline:none;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#22c55e'" onblur="this.style.borderColor='#bbf7d0'">
                            <div id="packageDropdown"
                                style="display:none;position:absolute;bottom:100%;left:0;right:0;background:#fff;border:1.5px solid #22c55e;border-radius:6px 6px 0 0;max-height:180px;overflow-y:auto;z-index:200;box-shadow:0 -4px 12px rgba(0,0,0,.1);">
                            </div>
                        </div>
                        <div id="activePackageBadge" style="display:none;margin-top:3px;font-size:10px;color:#16a34a;background:#dcfce7;padding:2px 6px;border-radius:4px;display:flex;justify-content:space-between;align-items:center;">
                            <span id="activePackageName"></span>
                            <button onclick="clearPackage()" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:11px;padding:0 2px;">✕</button>
                        </div>
                    </div>

                    <div class="trow">
                        <span class="lbl">
                            <span id="discount-label-display">Discount</span>
                            <select id="discount_type" class="inline-num"
                                style="width:50px;padding:2px 4px;font-size:11px;"
                                onchange="updateCartDisplay();updateBalanceSummary()">
                                <option value="fixed">Rs.</option>
                                <option value="percent">%</option>
                            </select>
                        </span>
                        <input type="number" id="discount" class="inline-num" value="0" min="0"
                            step="0.01" oninput="clearPackageLabel();updateCartDisplay();updateBalanceSummary()">
                    </div>
                    <input type="hidden" id="discount_label" value="">
                    <div class="trow">
                        <span class="lbl">Weight</span>
                        <span class="val total-weight" style="color:#94a3b8;font-size:11.5px;">0.00 kg</span>
                    </div>
                    <div class="trow grand">
                        <span class="lbl">Total Bill</span>
                        <span class="val total">Rs. 0.00</span>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="pay-section">
                    <div class="sec-label">Payment Method</div>
                    <div class="pm-grid">
                        @foreach($paymentMethods as $i => $pm)
                            <button class="pm-btn {{ $i === 0 ? 'active' : '' }}" data-method="{{ $pm->name }}" onclick="selectPM(this)">{{ $pm->label }}</button>
                        @endforeach
                    </div>
                    <input type="hidden" id="payment_method" value="{{ $paymentMethods->first()->name ?? 'cash' }}">
                </div>

                {{-- Partial Payment Box --}}
                <div class="payment-box" id="paymentBalanceBox">
                    <div id="previousBalanceRow"
                        style="display:none;background:#fff7ed;padding:5px 8px;border-radius:6px;margin-bottom:8px;font-size:14px;">
                        <span style="color:#c2410c;font-weight:700;">Prev. Balance:</span>
                        <span style="color:#c2410c;font-weight:800;" id="previousBalanceDisplay">Rs. 0</span>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <label>Amount Received</label>
                        <button type="button" onclick="fillExactTotal()"
                            style="font-size:11px;color:#2563eb;background:none;border:none;cursor:pointer;padding:0;text-decoration:underline;font-weight:600;">
                            = Fill Total
                        </button>
                    </div>
                    <input type="number" id="paid_amount" name="paid_amount" class="payment-big-input" min="0"
                        step="0.01" placeholder="0.00" oninput="updateBalanceSummary()">

                    <div class="bal-summary" id="balanceSummaryRows" style="display:none;">
                        <div class="bal-row" style="color:#6b7280;">
                            <span>Total Bill:</span>
                            <span id="summaryTotalBill" style="font-weight:700;">Rs. 0</span>
                        </div>
                        <div class="bal-row" style="color:#16a34a;">
                            <span>Amount Paid:</span>
                            <span id="summaryAmountPaid" style="font-weight:700;">Rs. 0</span>
                        </div>
                        <div class="bal-row change" id="changeRow" style="display:none;">
                            <span>Change:</span>
                            <strong id="changeDisplay">Rs. 0</strong>
                        </div>
                        <div class="bal-row remaining" id="balanceRow" style="display:none;">
                            <span>Remaining:</span>
                            <strong id="balanceDisplay">Rs. 0</strong>
                        </div>
                        <div class="bal-row new-bal" id="newBalanceRow">
                            <span id="newBalLabel" style="color:#6b7280;">New Account Balance:</span>
                            <strong id="newBalanceDisplay">Rs. 0</strong>
                        </div>
                    </div>
                </div>

                {{-- Dispatch --}}
                <div class="dispatch-section">
                    <div class="sec-label">Dispatch Method</div>
                    <select id="dispatch_method" name="dispatch_method" class="pos-select">
                        @foreach($dispatchMethods as $dm)
                            <option value="{{ $dm->name }}" data-id="{{ $dm->id }}" data-has-tracking="{{ $dm->has_tracking ? '1' : '0' }}">{{ $dm->name }}</option>
                        @endforeach
                    </select>
                    <div id="tracking_id_field" style="display:none;margin-top:6px;">
                        <input type="text" id="tracking_id" class="pos-select" placeholder="Tracking ID"
                            style="margin-bottom:5px;">
                    </div>
                    <div id="delivery_charges_field" style="display:none;margin-top:6px;">
                        <input type="number" id="delivery_charges" class="pos-select"
                            placeholder="Delivery Charges (Rs.)" value="0" min="0" step="0.01"
                            oninput="updateCartDisplay();updateBalanceSummary()">
                    </div>
                </div>

                {{-- Order Date (optional backdate) --}}
                <div class="dispatch-section">
                    <div class="sec-label">Order Date (تاریخ)</div>
                    <input type="date" id="order_date" class="pos-select" value="{{ now()->format('Y-m-d') }}">
                </div>

                {{-- Notes --}}
                <div style="padding:0 10px 8px;">
                    <textarea id="order_notes" placeholder="Notes / comments..." rows="2"
                        style="width:100%;padding:6px 10px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:12px;resize:vertical;background:#f9fafb;color:#1e293b;"></textarea>
                </div>

                {{-- Action buttons --}}
                <div class="action-section">
                    <button class="btn-process checkout-btn">
                        Process Payment
                    </button>
                    <button class="btn-clear clear-cart-btn">
                        Clear Cart
                    </button>
                </div>

            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════
     MOBILE — FLOATING CART BAR (bottom of screen)
    ══════════════════════════════════════════════════════ --}}
    <div class="mobile-cart-bar" id="mobileCartBar" onclick="openMobileCart()">
        <div class="cart-bar-left">
            <div class="cart-bar-badge" id="mobileCartCount">0</div>
            <div class="cart-bar-text">View Cart</div>
        </div>
        <div class="cart-bar-total" id="mobileCartTotal">Rs. 0.00</div>
    </div>

    {{-- ══════════════════════════════════════════════════════
     MOBILE — FULL SCREEN CART OVERLAY
    ══════════════════════════════════════════════════════ --}}
    <div class="mobile-cart-overlay" id="mobileCartOverlay">
        <div class="overlay-header">
            <h2 style="display:flex;align-items:center;gap:8px;flex:1;">
                <span><i class="fas fa-shopping-cart"></i> Cart</span>
                <span class="js-next-order-number" style="font-size:11px;font-weight:700;color:#2563eb;background:#eff6ff;border:1px solid #dbeafe;padding:3px 8px;border-radius:999px;letter-spacing:.2px;margin-left:auto;">#{{ $nextOrderNumber }}</span>
            </h2>
            <button class="mobile-cart-close-btn" onclick="closeMobileCart()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="overlay-body" id="mobileCartBody">
            {{-- Filled dynamically by JS --}}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.posConfig = {
            storeRoute: "{{ route('admin.pos.store') }}"
        };
        window.__paymentMethods = @json($paymentMethodsJson);
        window.__dispatchMethods = @json($dispatchMethodsJson);
        window.__deliverySlabs = @json($deliverySlabsJson);
        window.__defaultTaxRate = {{ config('pos.tax_rate', 0) }};
        window.__codTaxRate = 5;
        window.cart = window.cart || [];

        // ── Format number ──────────────────────────────────────────
        window.formatNumber = function(num) {
            return parseFloat(num || 0).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        };

        // ── Calculate tax based on type (percent or fixed) ──
        window.calcTaxAmount = function(base, taxRate, taxType) {
            if (!taxType) taxType = document.getElementById('tax_type')?.value || 'percent';
            return taxType === 'percent' ? base * (taxRate / 100) : parseFloat(taxRate || 0);
        };

        // ── Get delivery charge for a specific dispatch method and weight ──
        window.getDeliveryChargeForWeight = function(dispatchMethodId, weightKg) {
            const slabs = window.__deliverySlabs[dispatchMethodId];
            if (!slabs) return 0;
            const slab = slabs.find(s => weightKg >= s.min && weightKg <= s.max);
            return slab ? slab.charge : 0;
        };

        // ── Auto-calculate delivery charges based on cart weight + selected dispatch ──
        window.autoCalculateDelivery = function() {
            const sel = document.getElementById('dispatch_method');
            if (!sel) return;
            const opt = sel.options[sel.selectedIndex];
            if (!opt || !dispatchNeedsTracking(sel.value)) return;

            const dispatchId = opt.dataset.id;
            let totalWeight = 0;
            (window.cart || []).forEach(item => {
                totalWeight += (item.weight || 0) * item.quantity;
            });

            const charge = getDeliveryChargeForWeight(dispatchId, totalWeight);
            const delInput = document.getElementById('delivery_charges');
            if (delInput) {
                delInput.value = charge;
            }
        };

        // ── Payment method toggle ──────────────────────────────────
        window.selectPM = function(btn) {
            // Update in both desktop and mobile
            document.querySelectorAll('.pm-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            // Sync all payment_method inputs
            document.querySelectorAll('[id=payment_method], .payment-method-input').forEach(el => {
                el.value = btn.dataset.method;
            });
            document.getElementById('payment_method').value = btn.dataset.method;

            // COD: auto-apply 5% tax; otherwise reset to default
            const taxInput = document.getElementById('custom_tax');
            const mTaxInput = document.getElementById('m_custom_tax');
            if (btn.dataset.method === 'cod') {
                if (taxInput) taxInput.value = window.__codTaxRate;
                if (mTaxInput) mTaxInput.value = window.__codTaxRate;
            } else {
                if (taxInput) taxInput.value = window.__defaultTaxRate;
                if (mTaxInput) mTaxInput.value = window.__defaultTaxRate;
            }

            // Pending: hide paid amount field (no payment expected)
            const isPending = btn.dataset.method === 'pending';
            const paymentBox = document.getElementById('paymentBalanceBox');
            const mPaidAmount = document.getElementById('m_paid_amount');
            if (paymentBox) paymentBox.style.display = isPending ? 'none' : '';
            if (mPaidAmount) mPaidAmount.closest('.payment-box, div')?.style && (mPaidAmount.closest('.payment-box')? mPaidAmount.closest('.payment-box').style.display = isPending ? 'none' : '' : null);
            // Clear paid amount when pending so full balance is recorded
            if (isPending) {
                const paidInput = document.getElementById('paid_amount');
                if (paidInput) paidInput.value = '0';
                if (mPaidAmount) mPaidAmount.value = '0';
            }

            // Recalculate totals
            if (typeof updateCartDisplay === 'function') updateCartDisplay();
            if (typeof updateBalanceSummary === 'function') updateBalanceSummary();
        };

        // ── Dispatch toggle ────────────────────────────────────────
        function dispatchNeedsTracking(val) {
            const dm = window.__dispatchMethods.find(d => d.name === val);
            return dm ? dm.has_tracking : false;
        }

        function dispatchNeedsDeliveryCharges(val) {
            // Show delivery charges for all dispatch methods except "Self Pickup"
            return val && val.toLowerCase() !== 'self pickup' && val !== '';
        }

        document.addEventListener('DOMContentLoaded', function() {
            function setupDispatchToggle() {
                document.querySelectorAll('[id=dispatch_method]').forEach(sel => {
                    sel.addEventListener('change', function() {
                        const needsTracking = dispatchNeedsTracking(this.value);
                        const needsDelivery = dispatchNeedsDeliveryCharges(this.value);
                        const parent = this.closest('.dispatch-section');
                        if (parent) {
                            const trackField = parent.querySelector('[id=tracking_id_field]');
                            const delField = parent.querySelector('[id=delivery_charges_field]');
                            if (trackField) trackField.style.display = needsTracking ? 'block' :
                                'none';
                            if (delField) delField.style.display = needsDelivery ? 'block' : 'none';
                        }
                        // Auto-calculate delivery when dispatch with tracking selected
                        if (needsTracking) {
                            autoCalculateDelivery();
                        } else if (!needsDelivery) {
                            const delInput = document.getElementById('delivery_charges');
                            if (delInput) delInput.value = 0;
                        }
                        if (typeof updateCartDisplay === 'function') updateCartDisplay();
                        if (typeof updateBalanceSummary === 'function') updateBalanceSummary();
                    });
                });
            }
            setupDispatchToggle();
        });

        // ── AJAX Product Loading ──────────────────────────────────
        window.productPage = 1;
        window.productLastPage = 1;
        window.productLoading = false;
        window.productSearchTerm = '';
        window.productCategoryId = '';

        const productGrid = document.getElementById('productGrid');
        const productLoader = document.getElementById('productLoader');
        const productEmpty = document.getElementById('productEmpty');
        const productGridWrap = document.getElementById('productGridWrap');
        const productsUrl = "{{ route('admin.pos.products') }}";

        function renderProductCard(p) {
            const stockClass = p.stock_quantity <= p.reorder_level ? 'stock-low' : 'stock-ok';
            const imgHtml = p.image ?
                `<img src="${p.image}" alt="${p.name}" loading="lazy">` :
                `<i class="fas fa-box-open"></i>`;
            const unitLabel = p.unit ? ` <small style="font-weight:400;color:#6b7280;font-size:10px;">(${p.unit})</small>` :
                '';
            let barcodeHtml = '';
            if (p.barcode) {
                barcodeHtml =
                    `<div class="barcode-text"><i class="fas fa-barcode"></i> ${p.barcode}${p.rank ? ' | Box: ' + p.rank : ''}</div>`;
            } else if (p.rank) {
                barcodeHtml = `<div class="barcode-text">Box: ${p.rank}</div>`;
            }

            const card = document.createElement('div');
            card.className = 'product-item';
            card.dataset.id = p.id;
            card.dataset.name = p.name;
            card.dataset.barcode = p.barcode || '';
            card.dataset.price = p.sale_price;
            card.dataset.salePrice = p.sale_price;
            card.dataset.resalePrice = p.resale_price || p.sale_price;
            card.dataset.wholesalePrice = p.wholesale_price || p.sale_price;
            card.dataset.weight = p.weight;
            card.dataset.unit = p.unit || '';
            card.dataset.categoryId = p.category_id || '';

            card.innerHTML = `
                <div class="img-area">${imgHtml}</div>
                <div class="card-info">
                    <h3>${p.name}${unitLabel}</h3>
                    ${barcodeHtml}
                    <div class="price-text">Rs. ${Number.isInteger(parseFloat(p.sale_price)) ? parseInt(p.sale_price) : parseFloat(p.sale_price).toFixed(2)}</div>
                    <div class="stock-text ${stockClass}">Stock: ${Math.floor(p.stock_quantity)}${p.unit ? ' ' + p.unit : ''}</div>
                </div>`;

            card.addEventListener('click', function() {
                addProductToCart(this);
            });

            return card;
        }

        function addProductToCart(card) {
            const typeSelect = document.querySelector('.customer-type-select');
            const type = typeSelect?.value || 'walkin';

            let price = parseFloat(card.dataset.salePrice);
            if (type === 'reseller') price = parseFloat(card.dataset.resalePrice) || price;
            if (type === 'wholesale') price = parseFloat(card.dataset.wholesalePrice) || price;

            const existing = window.cart.find(i => i.id === card.dataset.id);
            if (existing) {
                existing.quantity++;
            } else {
                window.cart.push({
                    id: card.dataset.id,
                    name: card.dataset.name,
                    price: price,
                    weight: parseFloat(card.dataset.weight) || 0,
                    unit: card.dataset.unit || '',
                    quantity: 1,
                    salePrice: parseFloat(card.dataset.salePrice),
                    resalePrice: parseFloat(card.dataset.resalePrice) || parseFloat(card.dataset.salePrice),
                    wholesalePrice: parseFloat(card.dataset.wholesalePrice) || parseFloat(card.dataset.salePrice),
                    line_discount: 0,
                    line_discount_type: 'percent',
                });
            }

            card.style.borderColor = '#22c55e';
            setTimeout(() => {
                card.style.borderColor = '';
            }, 300);
            updateCartDisplay();
        }

        async function loadProducts(append = false) {
            if (window.productLoading) return;
            if (append && window.productPage > window.productLastPage) return;

            window.productLoading = true;
            productLoader.style.display = 'block';
            productEmpty.style.display = 'none';

            const params = new URLSearchParams({
                page: window.productPage,
                per_page: 30,
            });
            if (window.productSearchTerm) params.append('search', window.productSearchTerm);
            if (window.productCategoryId) params.append('category_id', window.productCategoryId);

            try {
                const res = await fetch(`${productsUrl}?${params}`);
                const json = await res.json();

                if (!append) productGrid.innerHTML = '';

                json.data.forEach(p => {
                    productGrid.appendChild(renderProductCard(p));
                });

                window.productLastPage = json.last_page;

                if (json.data.length === 0 && !append) {
                    productEmpty.style.display = 'block';
                }

                // Update price display if customer type is set
                const typeSelect = document.querySelector('.customer-type-select');
                if (typeSelect && typeSelect.value !== 'walkin') {
                    typeSelect.dispatchEvent(new Event('change'));
                }
            } catch (e) {
                console.error('Failed to load products:', e);
            } finally {
                window.productLoading = false;
                productLoader.style.display = 'none';
            }
        }

        // Infinite scroll
        productGridWrap.addEventListener('scroll', function() {
            if (this.scrollTop + this.clientHeight >= this.scrollHeight - 100) {
                if (window.productPage < window.productLastPage) {
                    window.productPage++;
                    loadProducts(true);
                }
            }
        });

        // Search with debounce
        let searchTimer;
        document.getElementById('productSearch')?.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                window.productSearchTerm = this.value.trim();
                window.productPage = 1;
                loadProducts(false);
            }, 300);
        });

        // Category tabs
        document.querySelectorAll('.cat-tab').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.cat-tab').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const catId = this.dataset.category;
                window.productCategoryId = catId === 'all' ? '' : catId;
                window.productPage = 1;
                loadProducts(false);
            });
        });

        // Initial load
        loadProducts(false);

        // ── Cart display ───────────────────────────────────────────
        window.updateCartDisplay = function() {
            const cartEl = document.querySelector('.cart-items');
            const emptyEl = document.querySelector('.empty-cart-message');
            const subtotalEl = document.querySelector('.subtotal');
            const totalEl = document.querySelector('.total');
            const taxEl = document.querySelector('.tax');
            const weightEl = document.querySelector('.total-weight');

            if (!cartEl) return;

            if (window.cart.length === 0) {
                cartEl.innerHTML = '';
                if (emptyEl) emptyEl.style.display = 'flex';
                if (subtotalEl) subtotalEl.textContent = 'Rs. 0.00';
                if (totalEl) totalEl.textContent = 'Rs. 0.00';
                if (taxEl) taxEl.textContent = 'Rs. 0.00';
                if (weightEl) weightEl.textContent = '0.00 kg';
                updateBalanceSummary();
                updateMobileCartBar();
                updateMobileCartOverlay();
                return;
            }
            if (emptyEl) emptyEl.style.display = 'none';

            let subtotal = 0,
                totalWeight = 0,
                html = '';
            window.cart.forEach((item, idx) => {
                const lineDiscAmt = (item.line_discount_type === 'percent')
                    ? item.price * ((item.line_discount || 0) / 100)
                    : (item.line_discount || 0);
                const effectivePrice = Math.max(0, item.price - lineDiscAmt);
                const itemTotal = effectivePrice * item.quantity;
                subtotal += itemTotal;
                totalWeight += (item.weight || 0) * item.quantity;
                html += buildCartItemHTML(item, idx, itemTotal, effectivePrice, lineDiscAmt);
            });
            cartEl.innerHTML = html;

            // Auto-calculate delivery charges based on weight (if tracking dispatch)
            autoCalculateDelivery();

            const taxRate = parseFloat(document.getElementById('custom_tax')?.value || 0);
            const discountRaw = parseFloat(document.getElementById('discount')?.value || 0);
            const discountType = document.getElementById('discount_type')?.value || 'fixed';
            const discount = discountType === 'percent' ? subtotal * (discountRaw / 100) : discountRaw;
            const afterDiscount = subtotal - discount;
            const delivery = parseFloat(document.getElementById('delivery_charges')?.value || 0);
            const taxAmt = calcTaxAmount(afterDiscount + delivery, taxRate);
            const total = afterDiscount + taxAmt + delivery;

            if (subtotalEl) subtotalEl.textContent = 'Rs. ' + formatNumber(subtotal);
            if (taxEl) taxEl.textContent = 'Rs. ' + formatNumber(taxAmt);
            if (totalEl) totalEl.textContent = 'Rs. ' + formatNumber(total);
            if (weightEl) weightEl.textContent = formatNumber(totalWeight) + ' kg';
            updateBalanceSummary();
            updateMobileCartBar();
            updateMobileCartOverlay();
        };

        function buildCartItemHTML(item, idx, itemTotal, effectivePrice, lineDiscAmt) {
            const hasDiscount = (item.line_discount || 0) > 0;
            const discType = item.line_discount_type || 'percent';
            const discVal = item.line_discount || 0;
            const discLabel = hasDiscount
                ? (discType === 'percent'
                    ? `−${discVal}% = Rs.${formatNumber(lineDiscAmt)}/unit → Rs.${formatNumber(effectivePrice)}`
                    : `−Rs.${formatNumber(discVal)}/unit → Rs.${formatNumber(effectivePrice)}`)
                : '';

            return `
            <div class="cart-item">
                <div class="cart-item-meta">
                    <div class="name">${item.name}${item.unit ? ' <small style="color:#9ca3af">('+item.unit+')</small>' : ''}</div>
                    <div class="unit" style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                        Rs.<input type="number" value="${item.price}" step="0.01" min="0"
                            style="width:65px;padding:1px 4px;border:1px solid #e5e7eb;border-radius:4px;font-size:11px;text-align:right;"
                            onchange="updateItemPrice(${idx}, this.value)">
                        x ${item.quantity}
                        <button onclick="toggleLineDiscount(${idx}, this)" title="Line discount"
                            style="background:${hasDiscount ? '#dcfce7' : '#f1f5f9'};color:${hasDiscount ? '#16a34a' : '#64748b'};border:1px solid ${hasDiscount ? '#bbf7d0' : '#e2e8f0'};border-radius:4px;padding:1px 5px;font-size:10px;font-weight:700;cursor:pointer;white-space:nowrap;">
                            ${hasDiscount ? '% ✓' : '%'}</button>
                    </div>
                    ${hasDiscount ? `<div style="font-size:10px;color:#16a34a;margin-top:2px;">${discLabel}</div>` : ''}
                    <div class="ld-row" data-ld-idx="${idx}" style="display:${hasDiscount ? 'flex' : 'none'};align-items:center;gap:4px;margin-top:4px;">
                        <select onchange="updateLineDiscType(${idx}, this.value)"
                            style="padding:2px 4px;border:1px solid #e5e7eb;border-radius:4px;font-size:11px;">
                            <option value="percent" ${discType==='percent'?'selected':''}>%</option>
                            <option value="fixed" ${discType==='fixed'?'selected':''}>Rs.</option>
                        </select>
                        <input type="number" value="${discVal > 0 ? discVal : ''}" min="0" step="0.01"
                            placeholder="0"
                            onchange="updateLineDiscount(${idx}, this.value)"
                            onkeydown="if(event.key==='Enter'){updateLineDiscount(${idx}, this.value);this.blur();}"
                            style="width:60px;padding:2px 4px;border:1px solid #e5e7eb;border-radius:4px;font-size:11px;text-align:right;">
                        <span style="font-size:10px;color:#9ca3af;">per unit</span>
                        <button onclick="clearLineDiscount(${idx})"
                            style="background:none;border:none;color:#ef4444;font-size:11px;cursor:pointer;padding:0 2px;"
                            title="Remove discount">✕</button>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:5px;">
                    <div class="qty-ctrl">
                        <button class="qty-btn" onclick="changeQty(${idx},-1)">-</button>
                        <input type="number" value="${item.quantity}" step="0.01" min="0.01"
                            style="width:45px;text-align:center;border:1px solid #e5e7eb;border-radius:4px;font-size:12px;padding:1px 2px;"
                            onchange="setQty(${idx}, this.value)">
                        <button class="qty-btn" onclick="changeQty(${idx},1)">+</button>
                    </div>
                    <span class="cart-item-total">Rs.${formatNumber(itemTotal)}</span>
                    <button class="remove-item-btn" onclick="removeFromCart(${idx})">x</button>
                </div>
            </div>`;
        }

        window.toggleLineDiscount = function(idx, btn) {
            // Use the button's parent cart-item to find the right ld-row
            // (avoids duplicate-ID problem when desktop + mobile are both in DOM)
            const container = btn ? btn.closest('.cart-item') : null;
            const row = container
                ? container.querySelector('.ld-row[data-ld-idx="' + idx + '"]')
                : document.querySelector('.ld-row[data-ld-idx="' + idx + '"]');
            if (!row) return;
            const isHidden = row.style.display === 'none';
            row.style.display = isHidden ? 'flex' : 'none';
            if (isHidden) {
                const input = row.querySelector('input[type=number]');
                if (input) { input.focus(); input.select(); }
            }
        };
        window.updateLineDiscount = function(idx, val) {
            window.cart[idx].line_discount = parseFloat(val) || 0;
            updateCartDisplay();
        };
        window.updateLineDiscType = function(idx, type) {
            window.cart[idx].line_discount_type = type;
            updateCartDisplay();
        };
        window.clearLineDiscount = function(idx) {
            window.cart[idx].line_discount = 0;
            updateCartDisplay();
        };

        window.changeQty = function(idx, delta) {
            window.cart[idx].quantity += delta;
            if (window.cart[idx].quantity <= 0) window.cart.splice(idx, 1);
            updateCartDisplay();
        };
        window.removeFromCart = function(idx) {
            window.cart.splice(idx, 1);
            updateCartDisplay();
        };
        window.updateItemPrice = function(idx, newPrice) {
            window.cart[idx].price = parseFloat(newPrice) || 0;
            updateCartDisplay();
        };
        window.setQty = function(idx, newQty) {
            const qty = parseFloat(newQty);
            if (qty > 0) {
                window.cart[idx].quantity = qty;
            } else {
                window.cart.splice(idx, 1);
            }
            updateCartDisplay();
        };
        window.clearCart = function() {
            window.cart = [];
            updateCartDisplay();
        };

        window.fillExactTotal = function() {
            const d = getBalanceData();
            const total = parseFloat(d.total.toFixed(2));
            const paidInput  = document.getElementById('paid_amount');
            const mPaidInput = document.getElementById('m_paid_amount');
            if (paidInput)  { paidInput.value  = total; }
            if (mPaidInput) { mPaidInput.value = total; }
            updateBalanceSummary();
        };

        // ── Balance summary (updates BOTH desktop & mobile) ──────
        function getBalanceData() {
            const cart = window.cart || [];
            const taxRate = parseFloat(document.getElementById('custom_tax')?.value || 0);
            const discountRaw = parseFloat(document.getElementById('discount')?.value || 0);
            const discountType = document.getElementById('discount_type')?.value || 'fixed';
            const delivery = parseFloat(document.getElementById('delivery_charges')?.value || 0);
            const subtotal = cart.reduce((s, i) => {
                const dAmt = (i.line_discount_type === 'percent')
                    ? i.price * ((i.line_discount || 0) / 100)
                    : (i.line_discount || 0);
                return s + Math.max(0, i.price - dAmt) * i.quantity;
            }, 0);
            const discount = discountType === 'percent' ? subtotal * (discountRaw / 100) : discountRaw;
            const afterDiscount = subtotal - discount;
            const total = afterDiscount + calcTaxAmount(afterDiscount + delivery, taxRate) + delivery;

            let prevBal = 0;
            const csel = document.getElementById('customerSelect');
            if (csel?.value) {
                const opt = csel.options[csel.selectedIndex];
                prevBal = parseFloat(opt?.dataset?.creditBalance || 0);
            }

            const paidRaw = document.getElementById('paid_amount')?.value;
            const paidAmt = (paidRaw !== '' && paidRaw != null) ? parseFloat(paidRaw) : total;
            const balOnOrder = Math.max(0, total - paidAmt);
            const change = Math.max(0, paidAmt - total);
            const newBalance = prevBal + total - paidAmt;

            return {
                total,
                prevBal,
                paidRaw,
                paidAmt,
                balOnOrder,
                change,
                newBalance
            };
        }

        function updateBalanceSummary() {
            const d = getBalanceData();
            const set = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.textContent = val;
            };
            const show = (id, v) => {
                const el = document.getElementById(id);
                if (el) el.style.display = v ? '' : 'none';
            };
            const showFlex = (id, v) => {
                const el = document.getElementById(id);
                if (el) el.style.display = v ? 'flex' : 'none';
            };

            // Desktop
            const summaryRows = document.getElementById('balanceSummaryRows');
            if (summaryRows) summaryRows.style.display = (d.paidRaw || d.prevBal > 0) ? 'block' : 'none';

            set('summaryTotalBill', 'Rs. ' + formatNumber(d.total));
            set('summaryAmountPaid', 'Rs. ' + formatNumber(d.paidAmt));
            show('previousBalanceRow', d.prevBal > 0);
            set('previousBalanceDisplay', 'Rs. ' + formatNumber(d.prevBal));
            show('changeRow', d.change > 0);
            set('changeDisplay', 'Rs. ' + formatNumber(d.change));
            show('balanceRow', d.balOnOrder > 0);
            set('balanceDisplay', 'Rs. ' + formatNumber(d.balOnOrder));

            // Desktop new balance
            const nbEl = document.getElementById('newBalanceDisplay');
            if (nbEl) {
                if (d.newBalance > 0) {
                    nbEl.textContent = 'Rs. ' + formatNumber(d.newBalance);
                    nbEl.style.color = '#dc2626';
                } else if (d.newBalance < 0) {
                    nbEl.textContent = 'Rs. ' + formatNumber(Math.abs(d.newBalance)) + ' (advance)';
                    nbEl.style.color = '#16a34a';
                } else {
                    nbEl.textContent = 'Settled';
                    nbEl.style.color = '#16a34a';
                }
            }
            const lbl = document.getElementById('newBalLabel');
            if (lbl) lbl.textContent = d.newBalance > 0 ? 'New Balance (Due):' : d.newBalance < 0 ? 'Advance Credit:' :
                'Account Status:';

            // Mobile
            const mWrap = document.getElementById('mobileBalanceSummary');
            if (mWrap) mWrap.style.display = (d.paidRaw || d.prevBal > 0) ? 'block' : 'none';

            set('m_summaryTotalBill', 'Rs. ' + formatNumber(d.total));
            set('m_summaryAmountPaid', 'Rs. ' + formatNumber(d.paidAmt));
            showFlex('m_changeRow', d.change > 0);
            set('m_changeDisplay', 'Rs. ' + formatNumber(d.change));
            showFlex('m_balanceRow', d.balOnOrder > 0);
            set('m_balanceDisplay', 'Rs. ' + formatNumber(d.balOnOrder));

            const mNbEl = document.getElementById('m_newBalanceDisplay');
            if (mNbEl) {
                if (d.newBalance > 0) {
                    mNbEl.textContent = 'Rs. ' + formatNumber(d.newBalance);
                    mNbEl.style.color = '#dc2626';
                } else if (d.newBalance < 0) {
                    mNbEl.textContent = 'Rs. ' + formatNumber(Math.abs(d.newBalance)) + ' (advance)';
                    mNbEl.style.color = '#16a34a';
                } else {
                    mNbEl.textContent = 'Settled';
                    mNbEl.style.color = '#16a34a';
                }
            }
            const mLbl = document.getElementById('m_newBalLabel');
            if (mLbl) mLbl.textContent = d.newBalance > 0 ? 'New Balance (Due):' : d.newBalance < 0 ? 'Advance Credit:' :
                'Account Status:';
        }

        // ── Mobile Cart Bar + Overlay ────────────────────────────
        function updateMobileCartBar() {
            const countEl = document.getElementById('mobileCartCount');
            const totalEl = document.getElementById('mobileCartTotal');
            if (!countEl || !totalEl) return;

            const itemCount = window.cart.reduce((s, i) => s + i.quantity, 0);
            countEl.textContent = Math.round(itemCount * 100) / 100;

            const subtotal = window.cart.reduce((s, i) => s + i.price * i.quantity, 0);
            const taxRate = parseFloat(document.getElementById('custom_tax')?.value || 0);
            const discountRaw = parseFloat(document.getElementById('discount')?.value || 0);
            const discountType = document.getElementById('discount_type')?.value || 'fixed';
            const discount = discountType === 'percent' ? subtotal * (discountRaw / 100) : discountRaw;
            const afterDiscount = subtotal - discount;
            const delivery = parseFloat(document.getElementById('delivery_charges')?.value || 0);
            const total = afterDiscount + calcTaxAmount(afterDiscount + delivery, taxRate) + delivery;
            totalEl.textContent = 'Rs. ' + formatNumber(total);
        }

        function updateMobileCartOverlay() {
            const body = document.getElementById('mobileCartBody');
            if (!body) return;

            // Only update if overlay is open
            const overlay = document.getElementById('mobileCartOverlay');
            if (!overlay || !overlay.classList.contains('open')) return;

            // Don't re-render if user is typing in an input inside the overlay
            // This prevents keyboard from closing on mobile
            const activeEl = document.activeElement;
            if (activeEl && overlay.contains(activeEl) && (activeEl.tagName === 'INPUT' || activeEl.tagName ===
                    'TEXTAREA' || activeEl.tagName === 'SELECT')) {
                // Just update the totals text without rebuilding HTML
                updateMobileCartTotalsOnly();
                return;
            }

            renderMobileCartContent();
        }

        function updateMobileCartTotalsOnly() {
            const subtotal = window.cart.reduce((s, i) => {
                const dAmt = (i.line_discount_type === 'percent')
                    ? i.price * ((i.line_discount || 0) / 100)
                    : (i.line_discount || 0);
                return s + Math.max(0, i.price - dAmt) * i.quantity;
            }, 0);
            const totalWeight = window.cart.reduce((s, i) => s + (i.weight || 0) * i.quantity, 0);
            const taxRate = parseFloat(document.getElementById('custom_tax')?.value || 0);
            const discountRaw = parseFloat(document.getElementById('discount')?.value || 0);
            const discountType = document.getElementById('discount_type')?.value || 'fixed';
            const discount = discountType === 'percent' ? subtotal * (discountRaw / 100) : discountRaw;
            const afterDiscount = subtotal - discount;
            const delivery = parseFloat(document.getElementById('delivery_charges')?.value || 0);
            const taxAmt = calcTaxAmount(afterDiscount + delivery, taxRate);
            const total = afterDiscount + taxAmt + delivery;

            const set = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.textContent = val;
            };

            // Update mobile cart floating bar
            set('mobileCartTotal', 'Rs. ' + formatNumber(total));
            set('mobileCartCount', window.cart.reduce((s, i) => s + i.quantity, 0));

            // Update mobile overlay totals (so they refresh while typing)
            set('m_subtotal', 'Rs. ' + formatNumber(subtotal));
            set('m_tax', 'Rs. ' + formatNumber(taxAmt));
            set('m_weight', formatNumber(totalWeight) + ' kg');
            set('m_total', 'Rs. ' + formatNumber(total));

            // Also update balance summary
            updateBalanceSummary();
        }

        function renderMobileCartContent() {
            const body = document.getElementById('mobileCartBody');
            if (!body) return;

            let cartItemsHTML = '';
            if (window.cart.length === 0) {
                cartItemsHTML = `
                    <div class="empty-cart-message">
                        <i class="fas fa-shopping-cart" style="font-size:2.5rem;"></i>
                        <p style="font-weight:600;">Cart is empty</p>
                        <p style="font-size:11px;opacity:.7;">Tap products to add</p>
                    </div>`;
            } else {
                window.cart.forEach((item, idx) => {
                    const lineDiscAmt = (item.line_discount_type === 'percent')
                        ? item.price * ((item.line_discount || 0) / 100)
                        : (item.line_discount || 0);
                    const effectivePrice = Math.max(0, item.price - lineDiscAmt);
                    const itemTotal = effectivePrice * item.quantity;
                    cartItemsHTML += buildCartItemHTML(item, idx, itemTotal, effectivePrice, lineDiscAmt);
                });
            }

            // Calculate totals (tax applies on subtotal - discount + delivery)
            const subtotal = window.cart.reduce((s, i) => {
                const dAmt = (i.line_discount_type === 'percent')
                    ? i.price * ((i.line_discount || 0) / 100)
                    : (i.line_discount || 0);
                return s + Math.max(0, i.price - dAmt) * i.quantity;
            }, 0);
            const totalWeight = window.cart.reduce((s, i) => s + (i.weight || 0) * i.quantity, 0);
            const taxRate = parseFloat(document.getElementById('custom_tax')?.value || 0);
            const taxType = document.getElementById('tax_type')?.value || 'percent';
            const discountRaw = parseFloat(document.getElementById('discount')?.value || 0);
            const discountType = document.getElementById('discount_type')?.value || 'fixed';
            const discount = discountType === 'percent' ? subtotal * (discountRaw / 100) : discountRaw;
            const afterDiscount = subtotal - discount;
            const delivery = parseFloat(document.getElementById('delivery_charges')?.value || 0);
            const taxAmt = calcTaxAmount(afterDiscount + delivery, taxRate, taxType);
            const total = afterDiscount + taxAmt + delivery;

            // Get customer info
            const csel = document.getElementById('customerSelect');
            let selectedCustomerName = '';
            let prevBal = 0;
            if (csel?.value) {
                const opt = csel.options[csel.selectedIndex];
                selectedCustomerName = opt?.dataset?.name || opt?.text || '';
                prevBal = parseFloat(opt?.dataset?.creditBalance || 0);
            }

            const paymentMethod = document.getElementById('payment_method')?.value || 'cash';
            const dispatchMethod = document.getElementById('dispatch_method')?.value || 'Self Pickup';

            body.innerHTML = `
                <!-- Customer info (if selected) -->
                ${selectedCustomerName ? `
                    <div style="padding:8px 14px;background:#eff6ff;border-bottom:1px solid #e5e7eb;font-size:13px;">
                        <strong>Customer:</strong> ${selectedCustomerName}
                        ${prevBal > 0 ? `<span style="color:#ef4444;font-size:11px;margin-left:8px;">Due: Rs.${formatNumber(prevBal)}</span>` : ''}
                    </div>` : ''}

                <!-- Cart items -->
                <div class="m-cart-items-wrap" style="padding:10px 12px;background:#f8fafc;min-height:60px;">
                    ${cartItemsHTML}
                </div>

                <!-- Footer sections -->
                <div class="m-cart-footer">
                    <!-- Totals -->
                    <div class="totals-block" style="padding:10px 14px;border-bottom:1px solid #f1f5f9;">
                        <div class="trow"><span class="lbl">Subtotal</span><span class="val" id="m_subtotal">Rs. ${formatNumber(subtotal)}</span></div>
                        <div class="trow">
                            <span class="lbl">Tax
                                <select id="m_tax_type" class="inline-num" style="width:50px;padding:2px 4px;font-size:11px;"
                                    onchange="document.getElementById('tax_type').value=this.value;updateCartDisplay();">
                                    <option value="percent" ${taxType==='percent'?'selected':''}>%</option>
                                    <option value="fixed" ${taxType==='fixed'?'selected':''}>Rs.</option>
                                </select>
                                <input type="number" id="m_custom_tax" class="inline-num" value="${taxRate}" min="0" step="0.01"
                                    oninput="document.getElementById('custom_tax').value=this.value;updateCartDisplay();">
                            </span>
                            <span class="val" id="m_tax">Rs. ${formatNumber(taxAmt)}</span>
                        </div>
                        <!-- Mobile Package Search -->
                        <div style="margin:4px 0 6px;">
                            <input type="text" id="m_packageSearch" placeholder="Apply package..."
                                autocomplete="off"
                                style="width:100%;padding:4px 8px;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:6px;font-size:11px;color:#166534;outline:none;box-sizing:border-box;"
                                oninput="handleMobilePackageSearch(this.value)"
                                onfocus="handleMobilePackageSearch(this.value)">
                            <div id="m_packageDropdown"
                                style="display:none;background:#fff;border:1.5px solid #22c55e;border-radius:6px;max-height:150px;overflow-y:auto;margin-top:2px;">
                            </div>
                            ${document.getElementById('discount_label')?.value ? `
                            <div style="font-size:10px;color:#16a34a;background:#dcfce7;padding:2px 6px;border-radius:4px;margin-top:3px;display:flex;justify-content:space-between;">
                                <span>${document.getElementById('discount_label')?.value || ''}</span>
                                <button onclick="clearPackage()" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:11px;padding:0;">✕</button>
                            </div>` : ''}
                        </div>
                        <div class="trow">
                            <span class="lbl">
                                <span>${document.getElementById('discount_label')?.value || 'Discount'}</span>
                                <select id="m_discount_type" class="inline-num" style="width:50px;padding:2px 4px;font-size:11px;"
                                    onchange="document.getElementById('discount_type').value=this.value;updateCartDisplay();">
                                    <option value="fixed" ${discountType==='fixed'?'selected':''}>Rs.</option>
                                    <option value="percent" ${discountType==='percent'?'selected':''}>%</option>
                                </select>
                            </span>
                            <input type="number" id="m_discount" class="inline-num" value="${discountRaw}" min="0" step="0.01"
                                oninput="document.getElementById('discount').value=this.value;clearPackageLabel();updateCartDisplay();">
                        </div>
                        <div class="trow"><span class="lbl">Weight</span><span class="val" id="m_weight" style="color:#94a3b8;font-size:11.5px;">${formatNumber(totalWeight)} kg</span></div>
                        <div class="trow grand"><span class="lbl">Total Bill</span><span class="val" id="m_total" style="font-size:16px;font-weight:900;color:#2563eb;">Rs. ${formatNumber(total)}</span></div>
                    </div>

                    <!-- Payment Method -->
                    <div class="pay-section" style="padding:10px 14px;border-bottom:1px solid #f1f5f9;">
                        <div class="sec-label">Payment Method</div>
                        <div class="pm-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:5px;">
                            ${window.__paymentMethods.map(pm => `<button class="pm-btn ${paymentMethod===pm.name?'active':''}" data-method="${pm.name}" onclick="selectPM(this)">${pm.label}</button>`).join('')}
                        </div>
                    </div>

                    <!-- Amount Received -->
                    <div style="padding:10px 14px;border-bottom:1px solid #f1f5f9;">
                        <div class="payment-box" style="margin:0;">
                            ${prevBal > 0 ? `
                                <div style="background:#fff7ed;padding:5px 8px;border-radius:6px;margin-bottom:8px;display:flex;justify-content:space-between;">
                                    <span style="color:#c2410c;font-weight:700;">Prev. Balance:</span>
                                    <span style="color:#c2410c;font-weight:800;">Rs. ${formatNumber(prevBal)}</span>
                                </div>` : ''}
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <label>Amount Received</label>
                                <button type="button" onclick="fillExactTotal()"
                                    style="font-size:11px;color:#2563eb;background:none;border:none;cursor:pointer;padding:0;text-decoration:underline;font-weight:600;">
                                    = Fill Total
                                </button>
                            </div>
                            <input type="number" id="m_paid_amount" class="payment-big-input" min="0" step="0.01"
                                placeholder="0.00" value="${document.getElementById('paid_amount')?.value || ''}"
                                oninput="document.getElementById('paid_amount').value=this.value;updateBalanceSummary();updateMobileCartBar();">

                            <div id="mobileBalanceSummary" style="display:none;margin-top:8px;font-size:13px;border-top:1px solid #e5e7eb;padding-top:8px;">
                                <div style="display:flex;justify-content:space-between;padding:3px 0;color:#6b7280;">
                                    <span>Total Bill:</span>
                                    <span id="m_summaryTotalBill" style="font-weight:700;">Rs. 0</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;padding:3px 0;color:#16a34a;">
                                    <span>Amount Paid:</span>
                                    <span id="m_summaryAmountPaid" style="font-weight:700;">Rs. 0</span>
                                </div>
                                <div id="m_changeRow" style="display:none;justify-content:space-between;padding:3px 0;color:#2563eb;">
                                    <span>Change:</span>
                                    <strong id="m_changeDisplay">Rs. 0</strong>
                                </div>
                                <div id="m_balanceRow" style="display:none;justify-content:space-between;padding:3px 0;color:#dc2626;">
                                    <span>Remaining:</span>
                                    <strong id="m_balanceDisplay">Rs. 0</strong>
                                </div>
                                <div style="display:flex;justify-content:space-between;padding:3px 0;border-top:1px solid #e5e7eb;margin-top:4px;padding-top:6px;">
                                    <span id="m_newBalLabel" style="color:#6b7280;">New Balance:</span>
                                    <strong id="m_newBalanceDisplay">Rs. 0</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dispatch -->
                    <div class="dispatch-section" style="padding:10px 14px;">
                        <div class="sec-label">Dispatch Method</div>
                        <select id="m_dispatch_method" class="pos-select"
                            onchange="document.getElementById('dispatch_method').value=this.value;
                                var nt=dispatchNeedsTracking(this.value);
                                var nd=dispatchNeedsDeliveryCharges(this.value);
                                document.getElementById('m_tracking_wrap').style.display=nt?'block':'none';
                                document.getElementById('m_delivery_wrap').style.display=nd?'block':'none';
                                if(!nd){document.getElementById('m_delivery_charges').value=0;document.getElementById('delivery_charges').value=0;}
                                document.getElementById('dispatch_method').dispatchEvent(new Event('change'));">
                            ${window.__dispatchMethods.map(dm => `<option value="${dm.name}" ${dispatchMethod===dm.name?'selected':''}>${dm.name}</option>`).join('')}
                        </select>
                        <div id="m_tracking_wrap" style="display:${dispatchNeedsTracking(dispatchMethod)?'block':'none'};margin-top:6px;">
                            <input type="text" id="m_tracking_id" class="pos-select" placeholder="Tracking ID"
                                value="${document.getElementById('tracking_id')?.value || ''}"
                                oninput="document.getElementById('tracking_id').value=this.value;">
                        </div>
                        <div id="m_delivery_wrap" style="display:${dispatchNeedsDeliveryCharges(dispatchMethod)?'block':'none'};margin-top:6px;">
                            <input type="number" id="m_delivery_charges" class="pos-select" placeholder="Delivery Charges (Rs.)"
                                value="${document.getElementById('delivery_charges')?.value || 0}" min="0" step="0.01"
                                oninput="document.getElementById('delivery_charges').value=this.value;updateCartDisplay();">
                        </div>
                    </div>

                    <!-- Order Date (optional backdate) -->
                    <div class="dispatch-section" style="padding:10px 14px;">
                        <div class="sec-label">Order Date (تاریخ)</div>
                        <input type="date" id="m_order_date" class="pos-select"
                            value="${document.getElementById('order_date')?.value || ''}"
                            oninput="document.getElementById('order_date').value=this.value;">
                    </div>

                    <!-- Notes -->
                    <div style="padding:0 14px 8px;">
                        <textarea id="m_order_notes" placeholder="Notes / comments..." rows="2"
                            style="width:100%;padding:6px 10px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:12px;resize:vertical;background:#f9fafb;color:#1e293b;"
                            oninput="document.getElementById('order_notes').value=this.value;">${document.getElementById('order_notes')?.value || ''}</textarea>
                    </div>

                    <!-- Actions (sticky at bottom) -->
                    <div class="action-section">
                        <button class="btn-process" onclick="processOrder()">
                            Process Payment
                        </button>
                        <button class="btn-clear" onclick="if(confirm('Clear cart?')){window.clearCart();closeMobileCart();}">
                            Clear Cart
                        </button>
                    </div>
                </div>
            `;

            // Update balance summary after rendering
            updateBalanceSummary();
        }

        window.openMobileCart = function() {
            const overlay = document.getElementById('mobileCartOverlay');
            if (overlay) {
                overlay.classList.add('open');
                renderMobileCartContent();
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeMobileCart = function() {
            const overlay = document.getElementById('mobileCartOverlay');
            if (overlay) {
                overlay.classList.remove('open');
                document.body.style.overflow = 'hidden';
            }
        };

        // ── Customer search setup ────────────────────────────────
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('customerSearchInput');
            const customerSelect = document.getElementById('customerSelect');
            const resultsEl = document.getElementById('customerResults');
            const infoBox = document.getElementById('selectedCustomerInfo');
            const nameEl = document.getElementById('selectedCustomerName');
            const typeEl = document.getElementById('selectedCustomerType');
            const dueBadge = document.getElementById('customerDueBadge');

            const allOptions = Array.from(customerSelect.querySelectorAll('option')).filter(o => o.value);

            function escapeHtml(s) {
                return (s || '').replace(/[&<>"']/g, m => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                } [m]));
            }

            function showResults(matches) {
                if (!matches.length) {
                    resultsEl.innerHTML =
                        '<div style="padding:10px 12px;color:#9ca3af;font-size:13px;">No customers found</div>';
                } else {
                    resultsEl.innerHTML = matches.slice(0, 50).map(o => {
                        const name = escapeHtml(o.getAttribute('data-name') || o.text);
                        const phone = escapeHtml(o.getAttribute('data-phone') || '');
                        const type = escapeHtml(o.getAttribute('data-type') || '');
                        const bal = parseFloat(o.getAttribute('data-credit-balance') || 0);
                        const balHtml = bal > 0 ?
                            `<span style="color:#ef4444;font-size:10px;font-weight:700;">Due: Rs.${bal.toLocaleString('en-PK')}</span>` :
                            '';
                        return `<div class="customer-result" data-value="${escapeHtml(o.value)}"
                            style="padding:9px 12px;cursor:pointer;border-bottom:1px solid #f8fafc;font-size:13px;color:#1e293b;display:flex;justify-content:space-between;align-items:center;"
                            onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background=''">
                            <div>${name}<small style="display:block;color:#9ca3af;font-size:11px;">${[type, phone].filter(Boolean).join(' - ')}</small></div>
                            ${balHtml}
                        </div>`;
                    }).join('');
                }
                resultsEl.style.display = 'block';
            }

            searchInput.addEventListener('focus', function() {
                showResults(allOptions);
            });

            searchInput.addEventListener('input', function() {
                const term = this.value.trim().toLowerCase();
                if (!term) {
                    showResults(allOptions);
                    return;
                }
                const matches = allOptions.filter(o => [(o.getAttribute('data-name') || ''), (o
                            .getAttribute('data-phone') || ''),
                        (o.getAttribute('data-barcode') || ''), o.textContent
                    ]
                    .some(v => v.toLowerCase().includes(term))
                );
                showResults(matches);
            });

            let isSelectingCustomer = false;

            resultsEl.addEventListener('mousedown', function(e) {
                isSelectingCustomer = true;
            });

            resultsEl.addEventListener('click', function(e) {
                const row = e.target.closest('.customer-result[data-value]');
                if (!row) return;
                e.preventDefault();
                const value = row.dataset.value;
                const opt = allOptions.find(o => o.value === value);
                if (!opt) return;
                customerSelect.value = value;
                searchInput.value = opt.getAttribute('data-name') || opt.text;
                resultsEl.style.display = 'none';
                isSelectingCustomer = false;
                selectCustomer(opt);
            });

            // Also handle touch for mobile
            resultsEl.addEventListener('touchstart', function(e) {
                isSelectingCustomer = true;
            });

            searchInput.addEventListener('blur', function() {
                setTimeout(() => {
                    if (!isSelectingCustomer) {
                        resultsEl.style.display = 'none';
                    }
                    isSelectingCustomer = false;
                }, 250);
            });

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    resultsEl.style.display = 'none';
                    this.blur();
                }
                if (e.key === 'Enter') {
                    const first = resultsEl.querySelector('.customer-result[data-value]');
                    if (first) first.dispatchEvent(new MouseEvent('mousedown', {
                        bubbles: true
                    }));
                }
            });

            function selectCustomer(opt) {
                const name = opt.getAttribute('data-name') || opt.text;
                const type = opt.getAttribute('data-type') || '';
                const balance = parseFloat(opt.getAttribute('data-credit-balance') || 0);

                if (nameEl) nameEl.textContent = name;
                if (typeEl) typeEl.textContent = '(' + (type.charAt(0).toUpperCase() + type.slice(1)) + ')';
                infoBox.style.display = 'block';

                if (dueBadge) {
                    dueBadge.style.display = balance > 0 ? 'inline' : 'none';
                    if (balance > 0) dueBadge.textContent = 'Due: Rs. ' + balance.toLocaleString('en-PK');
                }

                const typeSelect = document.querySelector('.customer-type-select');
                if (typeSelect) {
                    typeSelect.value = type === 'reseller' ? 'reseller' : type === 'wholesale' ? 'wholesale' :
                        'walkin';
                    typeSelect.dispatchEvent(new Event('change'));
                }
                updateBalanceSummary();
            }

            // Update prices when customer type changes
            const customerTypeSelect = document.querySelector('.customer-type-select');
            if (customerTypeSelect) {
                customerTypeSelect.addEventListener('change', function() {
                    const type = this.value;
                    document.querySelectorAll('.product-item').forEach(card => {
                        let price = parseFloat(card.dataset.salePrice);
                        if (type === 'reseller') price = parseFloat(card.dataset.resalePrice) ||
                            price;
                        if (type === 'wholesale') price = parseFloat(card.dataset.wholesalePrice) ||
                            price;
                        card.dataset.price = price;
                        const priceEl = card.querySelector('.price-text');
                        if (priceEl) priceEl.textContent = 'Rs. ' + parseFloat(price).toFixed(2);
                    });
                    window.cart.forEach(item => {
                        let price = item.salePrice;
                        if (type === 'reseller') price = item.resalePrice || price;
                        if (type === 'wholesale') price = item.wholesalePrice || price;
                        item.price = price;
                    });
                    updateCartDisplay();
                });
            }

            window.clearCustomerSelection = function() {
                customerSelect.value = '';
                searchInput.value = '';
                infoBox.style.display = 'none';
                if (dueBadge) dueBadge.style.display = 'none';
                updateBalanceSummary();
            };

            customerSelect.addEventListener('change', updateBalanceSummary);

            // ── Mobile customer selector ──────────────────────────
            const mobileCustomerSearch = document.getElementById('mobileCustomerSearch');
            const mobileCustomerDropdown = document.getElementById('mobileCustomerDropdown');
            const mobileCustomerSelected = document.getElementById('mobileCustomerSelected');
            const mobileCustomerName = document.getElementById('mobileCustomerName');

            if (mobileCustomerSearch) {
                const mobileCustomerOptions = Array.from(customerSelect.querySelectorAll('option')).filter(o => o
                    .value);

                mobileCustomerSearch.addEventListener('input', function() {
                    const term = this.value.toLowerCase().trim();
                    if (!term) {
                        mobileCustomerDropdown.style.display = 'none';
                        return;
                    }

                    const matches = mobileCustomerOptions.filter(o => {
                        const name = (o.dataset.name || o.text || '').toLowerCase();
                        const phone = (o.dataset.phone || '').toLowerCase();
                        const barcode = (o.dataset.barcode || '').toLowerCase();
                        return name.includes(term) || phone.includes(term) || barcode.includes(
                        term);
                    });

                    if (matches.length === 0) {
                        mobileCustomerDropdown.innerHTML =
                            '<div style="padding:10px 12px;font-size:12px;color:#9ca3af;">No customers found</div>';
                    } else {
                        mobileCustomerDropdown.innerHTML = matches.map(o => `
                            <div style="padding:10px 12px;font-size:13px;cursor:pointer;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;"
                                 data-value="${o.value}"
                                 onmouseover="this.style.background='#f0f4ff'"
                                 onmouseout="this.style.background=''">
                                <span><strong>${o.dataset.name || o.text}</strong>
                                    ${o.dataset.phone ? '<br><span style="font-size:11px;color:#6b7280;">' + o.dataset.phone + '</span>' : ''}
                                </span>
                                <span style="font-size:11px;color:#6b7280;">${o.dataset.type ? o.dataset.type : ''}</span>
                            </div>
                        `).join('');
                    }
                    mobileCustomerDropdown.style.display = 'block';

                    // Attach click handlers
                    mobileCustomerDropdown.querySelectorAll('[data-value]').forEach(item => {
                        item.addEventListener('click', function() {
                            selectMobileCustomer(this.dataset.value);
                        });
                    });
                });

                mobileCustomerSearch.addEventListener('focus', function() {
                    if (this.value.trim()) this.dispatchEvent(new Event('input'));
                });

                // Close dropdown on outside click
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('#mobileCustomerBar')) {
                        mobileCustomerDropdown.style.display = 'none';
                    }
                });
            }

            window.selectMobileCustomer = function(value) {
                // Sync with desktop customerSelect
                customerSelect.value = value;
                customerSelect.dispatchEvent(new Event('change'));

                const opt = customerSelect.options[customerSelect.selectedIndex];
                const name = opt?.dataset?.name || opt?.text || '';
                const type = opt?.dataset?.type || '';

                // Show selected state
                mobileCustomerSearch.parentElement.style.display = 'none';
                mobileCustomerSelected.style.display = 'flex';
                mobileCustomerName.textContent = name + (type ? ' (' + type + ')' : '');
                mobileCustomerDropdown.style.display = 'none';

                // Also trigger the type-based pricing
                const typeSelect = document.querySelector('.customer-type-select');
                if (typeSelect && type) {
                    typeSelect.value = type === 'reseller' ? 'reseller' : type === 'wholesale' ? 'wholesale' :
                        'walkin';
                    typeSelect.dispatchEvent(new Event('change'));
                }

                updateBalanceSummary();
            };

            window.clearMobileCustomer = function() {
                customerSelect.value = '';
                customerSelect.dispatchEvent(new Event('change'));

                mobileCustomerSearch.parentElement.style.display = '';
                mobileCustomerSelected.style.display = 'none';
                mobileCustomerSearch.value = '';
                mobileCustomerDropdown.style.display = 'none';

                const typeSelect = document.querySelector('.customer-type-select');
                if (typeSelect) {
                    typeSelect.value = 'walkin';
                    typeSelect.dispatchEvent(new Event('change'));
                }

                updateBalanceSummary();
            };

            // Cart buttons
            document.querySelector('.checkout-btn')?.addEventListener('click', async e => {
                e.preventDefault();
                await processOrder();
            });
            document.querySelector('.clear-cart-btn')?.addEventListener('click', () => {
                if (confirm('Clear cart?')) window.clearCart();
            });

            updateCartDisplay();
        });

        // ── Process Order ──────────────────────────────────────────
        // ── Package Search ─────────────────────────────────────────
        const packageSearchInput = document.getElementById('packageSearch');
        const packageDropdown    = document.getElementById('packageDropdown');
        const packagesApiUrl     = '{{ route("admin.packages.api") }}';
        let packagesCache        = null;

        async function loadPackages() {
            if (packagesCache) return packagesCache;
            try {
                const res = await fetch(packagesApiUrl);
                packagesCache = await res.json();
            } catch(e) { packagesCache = []; }
            return packagesCache;
        }

        async function filterPackages(term) {
            const pkgs = await loadPackages();
            const t = term.toLowerCase();
            return pkgs.filter(p =>
                p.name.toLowerCase().includes(t) || (p.code || '').toLowerCase().includes(t)
            );
        }

        packageSearchInput?.addEventListener('focus', async function() {
            const pkgs = await filterPackages('');
            renderPackageDropdown(pkgs);
        });
        packageSearchInput?.addEventListener('input', async function() {
            const pkgs = await filterPackages(this.value);
            renderPackageDropdown(pkgs);
        });

        function renderPackageDropdown(pkgs) {
            if (!pkgs.length) {
                packageDropdown.innerHTML = '<div style="padding:8px 10px;font-size:12px;color:#9ca3af;">No packages found</div>';
            } else {
                packageDropdown.innerHTML = pkgs.map(p => `
                    <div class="pkg-option" data-id="${p.id}"
                        style="padding:8px 10px;font-size:12px;cursor:pointer;border-bottom:1px solid #f0fdf4;"
                        onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background=''"
                        onclick="applyPackage(${p.id})">
                        <div style="font-weight:700;color:#166534;">${p.name}${p.code ? ' <span style="color:#9ca3af;font-weight:400;">['+p.code+']</span>' : ''}</div>
                        <div style="color:#6b7280;font-size:10px;">
                            ${p.items.length} items · Retail: Rs.${p.retail_total.toLocaleString('en',{maximumFractionDigits:0})}
                            · Sale: Rs.${p.sale_price.toLocaleString('en',{maximumFractionDigits:0})}
                            · Saves: Rs.${p.discount_amount.toLocaleString('en',{maximumFractionDigits:0})}
                        </div>
                    </div>`).join('');
            }
            packageDropdown.style.display = 'block';
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#packageSearch') && !e.target.closest('#packageDropdown')) {
                packageDropdown.style.display = 'none';
            }
        });

        window.applyPackage = async function(pkgId) {
            const pkgs = await loadPackages();
            const pkg = pkgs.find(p => p.id === pkgId);
            if (!pkg) return;

            packageDropdown.style.display = 'none';
            packageSearchInput.value = '';

            // Add each package item to cart (or increase qty if already there)
            const typeSelect = document.querySelector('.customer-type-select');
            const type = typeSelect?.value || 'walkin';

            pkg.items.forEach(item => {
                let price = item.sale_price;
                if (type === 'reseller') price = item.resale_price || price;
                if (type === 'wholesale') price = item.wholesale_price || price;

                const existing = window.cart.find(c => c.id == item.product_id);
                if (existing) {
                    existing.quantity += item.quantity;
                } else {
                    window.cart.push({
                        id: String(item.product_id),
                        name: item.name,
                        price: price,
                        weight: item.weight || 0,
                        unit: item.unit || '',
                        quantity: item.quantity,
                        salePrice: item.sale_price,
                        resalePrice: item.resale_price || item.sale_price,
                        wholesalePrice: item.wholesale_price || item.sale_price,
                        line_discount: 0,
                        line_discount_type: 'percent',
                    });
                }
            });

            // Pick package sale price based on customer type
            let pkgSalePrice = pkg.sale_price;
            if (type === 'reseller' && pkg.resale_price) pkgSalePrice = pkg.resale_price;
            if (type === 'wholesale' && pkg.wholesale_price) pkgSalePrice = pkg.wholesale_price;

            // Calculate discount: actual items total at customer-type prices minus package price
            let actualItemsTotal = 0;
            pkg.items.forEach(item => {
                let price = item.sale_price;
                if (type === 'reseller') price = item.resale_price || price;
                if (type === 'wholesale') price = item.wholesale_price || price;
                actualItemsTotal += price * item.quantity;
            });
            const actualDiscount = Math.max(0, actualItemsTotal - pkgSalePrice);

            // Set package discount (fixed amount) and label
            const discountInput = document.getElementById('discount');
            const discountType  = document.getElementById('discount_type');
            const discountLabel = document.getElementById('discount_label');
            const discountLabelDisplay = document.getElementById('discount-label-display');
            const badge = document.getElementById('activePackageBadge');
            const badgeName = document.getElementById('activePackageName');

            if (discountInput) discountInput.value = actualDiscount > 0 ? actualDiscount.toFixed(2) : 0;
            if (discountType) discountType.value = 'fixed';
            if (discountLabel) discountLabel.value = pkg.name + ' Discount';
            if (discountLabelDisplay) discountLabelDisplay.textContent = pkg.name + ' Discount';
            if (badge) badge.style.display = 'flex';
            if (badgeName) badgeName.textContent = pkg.name + ' — Rs.' + actualDiscount.toLocaleString('en', {maximumFractionDigits:0}) + ' off';

            updateCartDisplay();
            updateBalanceSummary();
        };

        window.handleMobilePackageSearch = async function(term) {
            const pkgs = await filterPackages(term);
            const dd = document.getElementById('m_packageDropdown');
            if (!dd) return;
            if (!pkgs.length) {
                dd.innerHTML = '<div style="padding:8px;font-size:12px;color:#9ca3af;">No packages found</div>';
            } else {
                dd.innerHTML = pkgs.map(p => `
                    <div style="padding:8px 10px;font-size:12px;cursor:pointer;border-bottom:1px solid #f0fdf4;"
                        onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background=''"
                        onclick="applyPackage(${p.id});document.getElementById('m_packageSearch').value='';document.getElementById('m_packageDropdown').style.display='none';">
                        <div style="font-weight:700;color:#166534;">${p.name}${p.code ? ' ['+p.code+']' : ''}</div>
                        <div style="color:#6b7280;font-size:10px;">Sale: Rs.${p.sale_price.toLocaleString('en',{maximumFractionDigits:0})} · Saves: Rs.${p.discount_amount.toLocaleString('en',{maximumFractionDigits:0})}</div>
                    </div>`).join('');
            }
            dd.style.display = 'block';
        };

        window.clearPackage = function() {
            const discountLabel = document.getElementById('discount_label');
            const discountLabelDisplay = document.getElementById('discount-label-display');
            const badge = document.getElementById('activePackageBadge');
            if (discountLabel) discountLabel.value = '';
            if (discountLabelDisplay) discountLabelDisplay.textContent = 'Discount';
            if (badge) badge.style.display = 'none';
        };

        window.clearPackageLabel = function() {
            const discountLabel = document.getElementById('discount_label');
            const discountLabelDisplay = document.getElementById('discount-label-display');
            const badge = document.getElementById('activePackageBadge');
            if (discountLabel) discountLabel.value = '';
            if (discountLabelDisplay) discountLabelDisplay.textContent = 'Discount';
            if (badge) badge.style.display = 'none';
        };

        // ── Process Order ──────────────────────────────────────────
        async function processOrder() {
            const cart = window.cart || [];
            if (cart.length === 0) {
                alert('Cart is empty. Please add items first.');
                return;
            }

            const paymentMethod = document.getElementById('payment_method')?.value || 'cash';
            const paidRaw = document.getElementById('paid_amount')?.value;
            const paidAmount = (paidRaw !== '' && paidRaw != null) ? parseFloat(paidRaw) : null;
            const discountRaw = parseFloat(document.getElementById('discount')?.value || 0);
            const discountType = document.getElementById('discount_type')?.value || 'fixed';
            const deliveryCharges = parseFloat(document.getElementById('delivery_charges')?.value || 0);
            const taxRate = parseFloat(document.getElementById('custom_tax')?.value || 0);
            const taxType = document.getElementById('tax_type')?.value || 'percent';
            const customerId = document.getElementById('customerSelect')?.value;
            const dispatchMethod = document.getElementById('dispatch_method')?.value || 'Self Pickup';
            const trackingId = document.getElementById('tracking_id')?.value || null;
            const notes = document.getElementById('order_notes')?.value || null;

            const subtotal = cart.reduce((s, i) => {
                const dAmt = (i.line_discount_type === 'percent')
                    ? i.price * ((i.line_discount || 0) / 100)
                    : (i.line_discount || 0);
                return s + Math.max(0, i.price - dAmt) * i.quantity;
            }, 0);
            const discount = discountType === 'percent' ? subtotal * (discountRaw / 100) : discountRaw;

            const discountLabel = document.getElementById('discount_label')?.value || null;

            const orderData = {
                customer_id: customerId ? parseInt(customerId) : null,
                discount_label: discountLabel || null,
                items: cart.map(i => {
                    const dAmt = (i.line_discount_type === 'percent')
                        ? i.price * ((i.line_discount || 0) / 100)
                        : (i.line_discount || 0);
                    const effectivePrice = Math.max(0, parseFloat(i.price) - dAmt);
                    return {
                        product_id: parseInt(i.id),
                        quantity: parseFloat(i.quantity),
                        unit_price: effectivePrice,
                        original_price: dAmt > 0 ? parseFloat(i.price) : null,
                        line_discount: dAmt > 0 ? parseFloat(dAmt.toFixed(2)) : 0,
                    };
                }),
                payment_method: paymentMethod,
                paid_amount: paidAmount,
                dispatch_method: dispatchMethod,
                tracking_id: trackingId,
                delivery_charges: deliveryCharges,
                tax_rate: taxRate,
                tax_type: taxType,
                discount: discount,
                order_date: document.getElementById('order_date')?.value || null,
                notes: notes,
            };

            // Disable all process buttons
            const btns = document.querySelectorAll('.btn-process, .checkout-btn');
            btns.forEach(b => {
                b.disabled = true;
                b.textContent = 'Processing...';
            });

            try {
                const res = await fetch('/admin/pos', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(orderData),
                });
                const data = await res.json();

                if (data.success) {
                    if (data.next_order_number) {
                        document.querySelectorAll('.js-next-order-number').forEach(el => {
                            el.textContent = '#' + data.next_order_number;
                        });
                    }
                    let msg =
                        `Order Processed!\n\nOrder #: ${data.order_number}\nTotal: Rs. ${formatNumber(data.total)}\nPaid: Rs. ${formatNumber(data.paid_amount)}`;
                    if (data.balance_amount > 0) msg += `\nRemaining: Rs. ${formatNumber(data.balance_amount)}`;
                    if (data.previous_balance > 0) msg += `\nPrev Balance: Rs. ${formatNumber(data.previous_balance)}`;
                    msg += data.new_balance > 0 ? `\n\nAccount Due: Rs. ${formatNumber(data.new_balance)}` :
                        data.new_balance < 0 ? `\n\nAdvance: Rs. ${formatNumber(Math.abs(data.new_balance))}` :
                        `\n\nAccount Settled`;
                    alert(msg);

                    window.clearCart();
                    document.getElementById('paid_amount').value = '';
                    document.getElementById('discount').value = '0';
                    if (document.getElementById('delivery_charges')) document.getElementById('delivery_charges').value =
                        '0';
                    if (document.getElementById('tracking_id')) document.getElementById('tracking_id').value = '';
                    if (document.getElementById('order_notes')) document.getElementById('order_notes').value = '';

                    updateBalanceSummary();
                    closeMobileCart();

                    if (confirm('View receipt?')) window.open(`/admin/pos/receipt/${data.order_id}`, '_blank');
                } else {
                    throw new Error(data.message || 'Order failed');
                }
            } catch (err) {
                alert('Error: ' + err.message);
            } finally {
                btns.forEach(b => {
                    b.disabled = false;
                    b.textContent = 'Process Payment';
                });
                const deskBtn = document.querySelector('.checkout-btn');
                if (deskBtn) deskBtn.textContent = 'Process Payment';
            }
        }
    </script>
@endpush
