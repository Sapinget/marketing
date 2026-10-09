<?php

return [

    /*
    |--------------------------------------------------------------------------
    | URL routing per menu
    |--------------------------------------------------------------------------
    |
    | true  : menu yang sudah dimigrasi dibuka lewat URL sendiri (/cs/order-online dst).
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
        // Batch B
        'orderan_online' => '/cs/order-online',
        'unit_ditanya' => '/cs/unit-ditanya',
        'claim_garansi_asuransi' => '/cs/claim-garansi',
        'keep_barang' => '/cs/keep-barang',

        // Batch C: Complain Tracker
        // (entri batch C ditambahkan di bawah baris ini)
        'input_claim' => '/complain/input-claim',
        'garansi_cermati' => '/complain/garansi-cermati',
        'garansi_resmi' => '/complain/garansi-resmi',

        // Batch D: Marketing
        'program_promo' => '/marketing/program-promo',
        'sell_out' => '/marketing/sell-out',
        'ads_log' => '/marketing/ads-log',
        'budgeting' => '/marketing/budgeting',
        // (entri batch D ditambahkan di bawah baris ini)

        // Batch E: Analisa Konten
        // (entri batch E ditambahkan di bawah baris ini)
        'meta_story' => '/analisa/story-ig',
        'meta_feed' => '/analisa/feed-konten',
        'meta_followers' => '/analisa/followers-ig',

        // Batch F: Dashboard & Konten
        // (entri batch F ditambahkan di bawah baris ini)
        'master' => '/konten/master-plan',
        'unboxing' => '/konten/unboxing',
        'ideation' => '/konten/ideation',
        'distribution' => '/konten/distribution',
        'analytics' => '/konten/analytics',
        'calendar' => '/konten/calendar',
        'story' => '/konten/story',
    ],

];
