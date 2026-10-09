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

];
