<?php

/**
 * Online-order lifecycle used by the storefront + admin online-orders module.
 * (POS sales use their own status values and are unaffected.)
 *
 * Legacy 'shipped' is treated as 'dispatched' and 'completed' as 'delivered'
 * for display, so older orders keep rendering correctly.
 */
return [
    'statuses' => [
        'pending'    => ['label' => 'Pending',    'icon' => 'fa-clock',        'bg' => '#fef3c7', 'text' => '#92400e'],
        'confirmed'  => ['label' => 'Confirmed',  'icon' => 'fa-check',        'bg' => '#cffafe', 'text' => '#0e7490'],
        'packed'     => ['label' => 'Packed',     'icon' => 'fa-box',          'bg' => '#ede9fe', 'text' => '#6d28d9'],
        'dispatched' => ['label' => 'Dispatched', 'icon' => 'fa-truck',        'bg' => '#dbeafe', 'text' => '#1d4ed8'],
        'delivered'  => ['label' => 'Delivered',  'icon' => 'fa-circle-check', 'bg' => '#d1fae5', 'text' => '#047857'],
        'returned'   => ['label' => 'Returned',   'icon' => 'fa-rotate-left',  'bg' => '#fef3c7', 'text' => '#b45309'],
        'cancelled'  => ['label' => 'Cancelled',  'icon' => 'fa-ban',          'bg' => '#fee2e2', 'text' => '#991b1b'],
    ],

    // Linear progress shown as a timeline; returned/cancelled are terminal.
    'timeline' => ['pending', 'confirmed', 'packed', 'dispatched', 'delivered'],
    'terminal' => ['returned', 'cancelled'],

    // Statuses that should put stock back / reverse khata when set.
    'restock'  => ['returned', 'cancelled'],
];
