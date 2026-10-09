<?php

return [

    /*
    |--------------------------------------------------------------------------
    | URL routing per menu
    |--------------------------------------------------------------------------
    |
    | true  : menu yang sudah dimigrasi dibuka lewat URL sendiri (/cs/service dst).
    | false : sidebar kembali memakai navigasi hash (#tab). Route baru tetap ada.
    | Lihat docs/hash-to-url-routing-plan.md. Dibaca sidebar mulai Batch A.
    |
    */
    'url_routing' => (bool) env('DASHBOARD_URL_ROUTING', true),

    /*
    |--------------------------------------------------------------------------
    | Peta tab -> URL (satu sumber kebenaran)
    |--------------------------------------------------------------------------
    |
    | Dipakai JS (switchTab, redirect link `#tab` lama, sidebar) dan dicocokkan
    | dengan route lewat DashboardPageRoutesTest. Tambah baris saat menu dimigrasi.
    |
    */
    'tab_urls' => [
        'pricelist_katalog' => '/katalog/android',
        'apple_katalog' => '/katalog/apple',
        'template_background' => '/katalog/template-background',
        'img_repo' => '/repository-gambar',
        'tiktok_template' => '/ecommerce/tiktok-template',
        'asset_vendor_inventory' => '/inventory/asset-vendor',
        'promo_pamflet' => '/promo-pamflet',
        // Batch A
        'harga_kompetitor' => '/tools/harga-kompetitor',
        'laporan_event' => '/tools/laporan-event',
        'settings' => '/settings',
        'nama_stock' => '/settings/nama-stock',
        'auth_users' => '/settings/users',
        'activity_logs' => '/settings/activity-logs',
    ],

];
