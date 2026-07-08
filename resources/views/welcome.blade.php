<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Marketing Dashboard | Pura Pura Ponsel</title>

        @fonts
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- Vite sources: resources/css/app.css resources/js/app.js -->
    </head>
    <body>
        <div id="marketing-dashboard" data-app="marketing-dashboard" class="app-shell">
            <div class="loading-screen" data-loading>
                <div class="loading-logo">
                    <img src="{{ asset('asset/images/logo.png') }}" alt="Pura Pura Ponsel">
                </div>
                <div class="loading-copy">
                    <strong>Pura Pura Ponsel</strong>
                    <span>Menyiapkan Dashboard...</span>
                </div>
            </div>

            <div class="system-alert is-hidden" data-alert>
                <div class="alert-icon">!</div>
                <div>
                    <div class="alert-title">Sistem Error</div>
                    <div class="alert-message" data-alert-message></div>
                </div>
                <button type="button" data-dismiss-alert aria-label="Tutup error">x</button>
            </div>

            <div class="toast is-hidden" data-toast></div>

            <section class="login-screen" data-login-screen>
                <div class="login-panel">
                    <img src="{{ asset('asset/images/logo.png') }}" alt="Pura Pura Ponsel" class="login-logo">
                    <h1>Selamat Datang</h1>
                    <p>Login untuk membuka dashboard</p>

                    <form class="login-form" data-login-form>
                        <label class="input-shell">
                            <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                            <input name="username" type="text" value="admin" placeholder="Username" autocomplete="username">
                        </label>
                        <label class="input-shell">
                            <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input name="pin" type="password" value="admin" placeholder="PIN Akses" autocomplete="current-password">
                        </label>
                        <button type="submit" class="login-button">Masuk Ke Sistem</button>
                    </form>
                </div>
            </section>

            <section class="dashboard-app is-hidden" data-dashboard-screen>
                <button class="sidebar-backdrop" type="button" data-sidebar-backdrop aria-label="Tutup sidebar"></button>

                <aside class="sidebar" id="mobile-sidebar" data-sidebar>
                    <div class="brand-row">
                        <div class="brand-mark">
                            <img src="{{ asset('asset/images/logo.png') }}" alt="Logo">
                        </div>
                        <div>
                            <div class="brand-name">Pura Pura Ponsel</div>
                            <div class="brand-subtitle">Marketing Dashboard</div>
                        </div>
                    </div>

                    <nav class="nav-list" aria-label="Menu dashboard">
                        <button class="nav-item is-active" data-tab-button="dashboard" data-roles="all">
                            <span class="nav-icon"><i class="fa-solid fa-gauge"></i></span><span>Dashboard</span>
                        </button>

                        <div class="nav-group" data-group="konten">
                            <button class="nav-group-toggle" data-group-toggle="konten" data-roles="non-teknisi">
                                <span><span class="nav-icon"><i class="fa-solid fa-folder-open"></i></span>Konten</span><span class="chevron"><i class="fa-solid fa-chevron-down"></i></span>
                            </button>
                            <div class="nav-group-items">
                                <button class="nav-subitem" data-tab-button="master" data-roles="non-teknisi"><i class="fa-solid fa-layer-group"></i>Master Plan</button>
                                <button class="nav-subitem" data-tab-button="unboxing" data-roles="non-teknisi"><i class="fa-solid fa-box-open"></i>Unboxing</button>
                                <button class="nav-subitem" data-tab-button="ideation" data-roles="non-teknisi"><i class="fa-solid fa-lightbulb"></i>Ideation</button>
                                <button class="nav-subitem" data-tab-button="distribution" data-roles="non-teknisi"><i class="fa-solid fa-share-nodes"></i>Distribution</button>
                                <button class="nav-subitem" data-tab-button="analytics" data-roles="non-teknisi"><i class="fa-solid fa-chart-line"></i>Analytics</button>
                                <button class="nav-subitem" data-tab-button="calendar" data-roles="non-teknisi"><i class="fa-solid fa-calendar-days"></i>Kalender</button>
                                <button class="nav-subitem" data-tab-button="story" data-roles="non-teknisi"><i class="fa-solid fa-clapperboard"></i>Jadwal Story</button>
                            </div>
                        </div>

                        <div class="nav-group" data-group="marketing">
                            <button class="nav-group-toggle" data-group-toggle="marketing" data-roles="non-teknisi">
                                <span><span class="nav-icon"><i class="fa-solid fa-bullseye"></i></span>Marketing</span><span class="chevron"><i class="fa-solid fa-chevron-down"></i></span>
                            </button>
                            <div class="nav-group-items">
                                <button class="nav-subitem" data-tab-button="program_promo" data-roles="non-teknisi"><i class="fa-solid fa-bullhorn"></i>Program Promo</button>
                                <button class="nav-subitem" data-tab-button="sell_out" data-roles="non-teknisi"><i class="fa-solid fa-arrow-trend-up"></i>Sell Out Target</button>
                                <button class="nav-subitem" data-tab-button="ads_log" data-roles="non-teknisi"><i class="fa-solid fa-rectangle-ad"></i>Ads Log</button>
                                <button class="nav-subitem" data-tab-button="budgeting" data-roles="non-teknisi"><i class="fa-solid fa-wallet"></i>Budgeting</button>
                            </div>
                        </div>

                        <button class="nav-item" data-tab-button="pos" data-roles="non-teknisi">
                            <span class="nav-icon"><i class="fa-solid fa-cash-register"></i></span><span>Penjualan (POS)</span>
                        </button>
                        <button class="nav-item" data-tab-button="forecast_bulanan" data-roles="all">
                            <span class="nav-icon"><i class="fa-solid fa-chart-simple"></i></span><span>Forecast Bulanan</span>
                        </button>

                        <div class="nav-group" data-group="analisa">
                            <button class="nav-group-toggle" data-group-toggle="analisa" data-roles="non-teknisi">
                                <span><span class="nav-icon"><i class="fa-solid fa-chart-bar"></i></span>Analisa Konten</span><span class="chevron"><i class="fa-solid fa-chevron-down"></i></span>
                            </button>
                            <div class="nav-group-items">
                                <button class="nav-subitem" data-tab-button="top_content_platform" data-roles="non-teknisi"><i class="fa-solid fa-trophy"></i>Top Konten</button>
                                <button class="nav-subitem" data-tab-button="low_content_platform" data-roles="non-teknisi"><i class="fa-solid fa-arrow-trend-down"></i>Low Konten</button>
                            </div>
                        </div>

                        <div class="nav-group" data-group="cs">
                            <button class="nav-group-toggle" data-group-toggle="cs" data-roles="non-teknisi">
                                <span><span class="nav-icon"><i class="fa-solid fa-headset"></i></span>Customer Service</span><span class="chevron"><i class="fa-solid fa-chevron-down"></i></span>
                            </button>
                            <div class="nav-group-items">
                                <button class="nav-subitem" data-tab-button="orderan_online" data-roles="non-teknisi"><i class="fa-solid fa-cart-shopping"></i>Order Online</button>
                                <button class="nav-subitem" data-tab-button="unit_ditanya" data-roles="non-teknisi"><i class="fa-solid fa-circle-question"></i>Unit Ditanya</button>
                                <button class="nav-subitem" data-tab-button="claim_garansi_asuransi" data-roles="all"><i class="fa-solid fa-shield-heart"></i>Claim Garansi</button>
                                <button class="nav-subitem" data-tab-button="keep_barang" data-roles="non-teknisi"><i class="fa-solid fa-box-archive"></i>Keep Barang</button>
                            </div>
                        </div>

                        <button class="nav-item teknisi-entry" data-tab-button="claim_garansi_asuransi" data-roles="teknisi">
                            <span class="nav-icon"><i class="fa-solid fa-shield-heart"></i></span><span>Claim Garansi</span>
                        </button>

                        <div class="nav-group" data-group="bonus">
                            <button class="nav-group-toggle" data-group-toggle="bonus" data-roles="non-teknisi">
                                <span><span class="nav-icon"><i class="fa-solid fa-gift"></i></span>Bonus</span><span class="chevron"><i class="fa-solid fa-chevron-down"></i></span>
                            </button>
                            <div class="nav-group-items">
                                <button class="nav-subitem" data-tab-button="bonus_report" data-roles="non-teknisi"><i class="fa-solid fa-file-invoice-dollar"></i>Bonus Report</button>
                                <button class="nav-subitem" data-tab-button="editor_performance" data-roles="non-teknisi"><i class="fa-solid fa-user-pen"></i>Editor Performance</button>
                            </div>
                        </div>

                        <button class="nav-item" data-tab-button="harga_kompetitor" data-roles="non-teknisi">
                            <span class="nav-icon"><i class="fa-solid fa-tags"></i></span><span>Harga Kompetitor</span>
                        </button>
                        <button class="nav-item" data-tab-button="laporan_event" data-roles="non-teknisi">
                            <span class="nav-icon"><i class="fa-solid fa-clipboard-list"></i></span><span>Laporan Event</span>
                        </button>

                        <div class="nav-group" data-group="settings">
                            <button class="nav-group-toggle" data-group-toggle="settings" data-roles="non-teknisi">
                                <span><span class="nav-icon"><i class="fa-solid fa-gear"></i></span>Settings</span><span class="chevron"><i class="fa-solid fa-chevron-down"></i></span>
                            </button>
                            <div class="nav-group-items">
                                <button class="nav-subitem" data-tab-button="settings" data-roles="non-teknisi"><i class="fa-solid fa-sliders"></i>Pengaturan</button>
                                <button class="nav-subitem" data-tab-button="nama_stock" data-roles="non-teknisi"><i class="fa-solid fa-boxes-stacked"></i>Nama Stock</button>
                            </div>
                        </div>
                    </nav>

                    <div class="user-card">
                        <div class="avatar" data-user-initial>A</div>
                        <div class="user-meta">
                            <div data-user-name>admin</div>
                            <span data-user-role>Super Admin</span>
                        </div>
                    </div>
                </aside>

                <main class="main-panel">
                    <header class="topbar">
                        <button
                            type="button"
                            class="icon-button menu-toggle"
                            data-open-sidebar
                            aria-label="Buka sidebar"
                            aria-controls="mobile-sidebar"
                            aria-expanded="false"
                        >
                            <span class="menu-toggle-lines" aria-hidden="true">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>
                        <div>
                            <div class="eyebrow">Pura Pura Ponsel</div>
                            <h2 data-page-title>Dashboard</h2>
                        </div>
                        <div class="topbar-actions">
                            <button type="button" class="ghost-button" data-tab-button="profile">Profile</button>
                            <button type="button" class="dark-button" data-logout>Logout</button>
                        </div>
                    </header>

                    <section class="page-view" data-page="dashboard">
                        <div class="dashboard-hero">
                            <div>
                                <div class="eyebrow">Marketing Dashboard</div>
                                <h1>Ringkasan Operasional</h1>
                                <p>Fondasi Laravel untuk menggantikan Apps Script dan spreadsheet sebagai database utama.</p>
                            </div>
                            <div class="hero-badge">
                                <span>Mode</span>
                                <strong>Laravel 13</strong>
                            </div>
                        </div>

                        <div class="kpi-grid">
                            <div class="kpi-card"><span>Konten Bulan Ini</span><strong>128</strong><small>Master Plan</small></div>
                            <div class="kpi-card"><span>Publish</span><strong>74</strong><small>Distribution</small></div>
                            <div class="kpi-card"><span>Views</span><strong>1.8M</strong><small>Analytics</small></div>
                            <div class="kpi-card"><span>POS Revenue</span><strong>Rp 2.4B</strong><small>Forecast Source</small></div>
                        </div>

                        <div class="content-grid">
                            <section class="panel">
                                <div class="panel-header">
                                    <div>
                                        <span class="eyebrow">Status</span>
                                        <h3>Pipeline Konten</h3>
                                    </div>
                                    <button type="button" class="small-button" data-tab-button="master">Lihat</button>
                                </div>
                                <div class="status-list">
                                    <div><span>NOT STARTED</span><strong>18</strong></div>
                                    <div><span>PROGRES</span><strong>27</strong></div>
                                    <div><span>DONE</span><strong>34</strong></div>
                                    <div><span>PUBLISHED</span><strong>49</strong></div>
                                </div>
                            </section>
                            <section class="panel">
                                <div class="panel-header">
                                    <div>
                                        <span class="eyebrow">Prioritas</span>
                                        <h3>Module Readiness</h3>
                                    </div>
                                    <button type="button" class="small-button" data-tab-button="forecast_bulanan">Forecast</button>
                                </div>
                                <div class="mini-table">
                                    <div><span>Import Excel</span><strong>Next</strong></div>
                                    <div><span>CRUD Master Plan</span><strong>Phase 4</strong></div>
                                    <div><span>Export PDF</span><strong>Phase 5</strong></div>
                                </div>
                            </section>
                        </div>
                    </section>

                    <section class="page-view is-hidden" data-page="module">
                        <div class="module-header">
                            <div>
                                <div class="eyebrow" data-module-group>Module</div>
                                <h1 data-module-title>Master Plan</h1>
                                <p data-module-description>Halaman placeholder dengan gaya visual legacy. Data dan CRUD akan masuk pada fase berikutnya.</p>
                            </div>
                            <div class="module-actions">
                                <button type="button" class="ghost-button">Export Excel</button>
                                <button type="button" class="dark-button">Tambah Data</button>
                            </div>
                        </div>

                        <section class="panel">
                            <div class="table-toolbar">
                                <label class="search-shell">
                                    <span>Cari</span>
                                    <input type="search" placeholder="Search data...">
                                </label>
                                <div class="filter-pills">
                                    <button type="button" class="is-active">Semua</button>
                                    <button type="button">Bulan Ini</button>
                                    <button type="button">Aktif</button>
                                </div>
                            </div>
                            <div class="data-table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama / Judul</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                            <th>Owner</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody data-module-rows></tbody>
                                </table>
                            </div>
                        </section>
                    </section>
                </main>
            </section>
        </div>
    </body>
</html>
