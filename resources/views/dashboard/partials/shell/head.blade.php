<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Marketing Dashboard | Pura Pura Ponsel</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('asset/images/favicon.ico') }}">

    <link rel="stylesheet" href="{{ asset('vendor/dashboard/fontawesome/css/all.min.css') }}" />
    <script src="{{ asset('vendor/dashboard/vue/vue.global.prod.js') }}"></script>
    <script src="{{ asset('vendor/dashboard/papaparse/papaparse.min.js') }}"></script>
    <script src="{{ asset('vendor/dashboard/apexcharts/apexcharts.min.js') }}"></script>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <script>
    window.MARKETING_BACKEND_URL=@json($backendUrl);
    </script>

    @vite([
        'resources/css/app.css',
        'resources/js/dashboard/export/print-core.js',
        'resources/js/dashboard/export/print-browser.js',
        'resources/js/dashboard/export/analytics-export-bridge.js',
        'resources/js/dashboard/export/customer-service-bridge.js',
        'resources/js/dashboard/export/reporting-export-bridge.js',
        'resources/js/dashboard/export/sales-export-bridge.js',
    ])
</head>
