<body class="text-ppp-text antialiased">
<div id="ppp-boot-screen" class="app-boot-screen">
    <div class="app-boot-card">
        <img src="/asset/images/logo.png" alt="Pura Pura Ponsel" class="app-boot-logo" />
        <div class="app-boot-title">Marketing Dashboard</div>
        <div class="app-boot-spinner"></div>
    </div>
</div>
<script>
    setTimeout(function () {
        var boot = document.getElementById('ppp-boot-screen');
        if (boot) boot.classList.add('is-hidden');
    }, 8000);
</script>
@include('dashboard.partials.shell.app-frame')
@include('dashboard.partials.shell.body-app-assembly')
</body>
