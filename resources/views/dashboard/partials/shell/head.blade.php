<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Marketing Dashboard | Pura Pura Ponsel</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('asset/images/favicon.ico') }}">
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('asset/fonts/lexend/lexend-400.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('asset/fonts/lexend/lexend-700.woff2') }}" crossorigin>

    @php($fontAwesomeCssPath = public_path('vendor/dashboard/fontawesome/css/all.min.css'))
    <link rel="stylesheet" href="{{ asset('vendor/dashboard/fontawesome/css/all.min.css') }}?v={{ file_exists($fontAwesomeCssPath) ? filemtime($fontAwesomeCssPath) : time() }}" />
    <script src="{{ asset('vendor/dashboard/vue/vue.global.prod.js') }}"></script>
    <script src="{{ asset('vendor/dashboard/papaparse/papaparse.min.js') }}"></script>
    <script src="{{ asset('vendor/dashboard/apexcharts/apexcharts.min.js') }}"></script>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <script>
    window.MARKETING_BACKEND_URL=@json($backendUrl);
    window.DASHBOARD_FONTAWESOME_URL=@json(asset('vendor/dashboard/fontawesome/css/all.min.css') . '?v=' . (file_exists($fontAwesomeCssPath) ? filemtime($fontAwesomeCssPath) : time()));
    </script>

    @vite([
        'resources/css/app.css',
        'resources/js/dashboard/shared/runtime-helpers.js',
        'resources/js/dashboard/export/print-core.js',
        'resources/js/dashboard/export/print-browser.js',
        'resources/js/dashboard/export/analytics-export-bridge.js',
        'resources/js/dashboard/export/customer-service-bridge.js',
        'resources/js/dashboard/export/reporting-export-bridge.js',
        'resources/js/dashboard/export/sales-export-bridge.js',
    ])
</head>
