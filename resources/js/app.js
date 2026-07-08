const MODULES = {
    master: ['Konten', 'Master Plan', 'Rencana konten lintas platform dengan status, editor, jadwal, dan link drive.'],
    unboxing: ['Konten', 'Unboxing', 'Monitoring konten unboxing berdasarkan editor, status, upload date, dan link.'],
    ideation: ['Konten', 'Ideation', 'Ruang daftar ide konten sebelum masuk ke master plan.'],
    distribution: ['Konten', 'Distribution', 'Distribusi link publish per platform yang terhubung ke master plan.'],
    analytics: ['Konten', 'Analytics', 'Input dan review views, likes, comments, dan shares per konten.'],
    calendar: ['Konten', 'Kalender', 'Kalender event dan agenda marketing.'],
    story: ['Konten', 'Jadwal Story', 'Jadwal story harian dengan jam, catatan, link, dan status.'],
    program_promo: ['Marketing', 'Program Promo', 'Daftar program promo, rules, benefit, harga, periode, dan warna kategori.'],
    sell_out: ['Marketing', 'Sell Out Target', 'Target vendor dan realisasi unit berdasarkan brand, seri, dan periode.'],
    ads_log: ['Marketing', 'Ads Log', 'Log performa ads, biaya, sisa saldo, dan engagement.'],
    budgeting: ['Marketing', 'Budgeting', 'Rancangan biaya, top-up need, dan approval budget marketing.'],
    pos: ['Penjualan', 'Penjualan (POS)', 'Data POS sebagai sumber revenue, unit, dan forecast bulanan.'],
    forecast_bulanan: ['Forecast', 'Forecast Bulanan', 'Runrate, target, dan proyeksi bulanan dari data POS.'],
    top_content_platform: ['Analisa Konten', 'Top Konten', 'Ranking konten terbaik berdasarkan performa platform.'],
    low_content_platform: ['Analisa Konten', 'Low Konten', 'Ranking konten rendah untuk evaluasi perbaikan.'],
    orderan_online: ['Customer Service', 'Order Online', 'Order marketplace dan pengiriman online.'],
    unit_ditanya: ['Customer Service', 'Unit Ditanya', 'Produk yang sering ditanyakan beserta availability.'],
    claim_garansi_asuransi: ['Customer Service', 'Claim Garansi', 'Claim garansi dan asuransi untuk customer dan teknisi.'],
    keep_barang: ['Customer Service', 'Keep Barang', 'Monitoring barang keep, deadline, follow-up, dan status.'],
    bonus_report: ['Bonus', 'Bonus Report', 'Simulasi bonus berdasarkan konfigurasi dan performa.'],
    editor_performance: ['Bonus', 'Editor Performance', 'Performa editor berdasarkan konten dan analytics.'],
    harga_kompetitor: ['Pricing', 'Harga Kompetitor', 'Perbandingan harga distributor, kompetitor, margin, dan rencana jual.'],
    laporan_event: ['LPJK', 'Laporan Event', 'Laporan event dan detail realisasi biaya.'],
    settings: ['System', 'Pengaturan', 'Master dropdown options dan konfigurasi aplikasi.'],
    nama_stock: ['System', 'Nama Stock', 'Master kategori, brand, dan seri stock.'],
    profile: ['User', 'Profile', 'Profil user, nama lengkap, role, dan akses.'],
};

const SAMPLE_ROWS = [];

const state = {
    user: JSON.parse(localStorage.getItem('ppp_user') || 'null'),
    activeTab: localStorage.getItem('ppp_active_tab') || 'dashboard',
};

function $(selector) {
    return document.querySelector(selector);
}

function $all(selector) {
    return Array.from(document.querySelectorAll(selector));
}

function isTeknisi() {
    return /teknisi/i.test(state.user?.role || '');
}

function showToast(message) {
    const toast = $('[data-toast]');
    if (!toast) return;
    toast.textContent = message;
    toast.classList.remove('is-hidden');
    clearTimeout(showToast.timer);
    showToast.timer = setTimeout(() => toast.classList.add('is-hidden'), 2200);
}

function setSidebarState(isOpen) {
    $('[data-sidebar]')?.classList.toggle('is-open', isOpen);
    $('[data-sidebar-backdrop]')?.classList.toggle('is-open', isOpen);
    const trigger = $('[data-open-sidebar]');
    trigger?.classList.toggle('is-open', isOpen);
    trigger?.setAttribute('aria-expanded', String(isOpen));
    trigger?.setAttribute('aria-label', isOpen ? 'Tutup sidebar' : 'Buka sidebar');
}

function setScreen() {
    const loading = $('[data-loading]');
    const login = $('[data-login-screen]');
    const dashboard = $('[data-dashboard-screen]');

    loading?.classList.add('is-hidden');
    login?.classList.toggle('is-hidden', Boolean(state.user));
    dashboard?.classList.toggle('is-hidden', !state.user);

    if (state.user) {
        applyUser();
        applyRoleVisibility();
        activateTab(isTeknisi() ? 'claim_garansi_asuransi' : state.activeTab);
    }
}

function applyUser() {
    const name = state.user?.nama || state.user?.username || 'admin';
    $('[data-user-name]').textContent = name;
    $('[data-user-role]').textContent = state.user?.role || 'Super Admin';
    $('[data-user-initial]').textContent = name.charAt(0).toUpperCase();
}

function applyRoleVisibility() {
    $all('[data-roles]').forEach((item) => {
        const rule = item.dataset.roles;
        const visible = rule === 'all' || (rule === 'teknisi' && isTeknisi()) || (rule === 'non-teknisi' && !isTeknisi());
        item.classList.toggle('is-hidden', !visible);
    });
}

function activateTab(tab) {
    if (isTeknisi() && tab !== 'claim_garansi_asuransi' && tab !== 'profile') {
        tab = 'claim_garansi_asuransi';
    }

    state.activeTab = tab;
    localStorage.setItem('ppp_active_tab', tab);

    $all('[data-tab-button]').forEach((button) => {
        button.classList.toggle('is-active', button.dataset.tabButton === tab);
    });

    const isDashboard = tab === 'dashboard';
    $('[data-page="dashboard"]').classList.toggle('is-hidden', !isDashboard);
    $('[data-page="module"]').classList.toggle('is-hidden', isDashboard);

    if (!isDashboard) {
        renderModule(tab);
    }

    $('[data-page-title]').textContent = isDashboard ? 'Dashboard' : MODULES[tab]?.[1] || 'Dashboard';
    closeSidebar();
}

function renderModule(tab) {
    const module = MODULES[tab] || MODULES.master;
    $('[data-module-group]').textContent = module[0];
    $('[data-module-title]').textContent = module[1];
    $('[data-module-description]').textContent = module[2];

    const rows = $('[data-module-rows]');
    rows.innerHTML = SAMPLE_ROWS.map((row, index) => {
        const statusClass = row[2] === 'DONE' || row[2] === 'PUBLISHED' ? 'done' : row[2] === 'PROGRES' ? 'progress' : 'draft';
        const id = `${module[1].replace(/[^A-Z0-9]+/gi, '').slice(0, 3).toUpperCase()}-${String(index + 1).padStart(3, '0')}`;
        return `
            <tr>
                <td>${id}</td>
                <td>${row[1]}</td>
                <td><span class="badge ${statusClass}">${row[2]}</span></td>
                <td>${row[3]}</td>
                <td>${row[4]}</td>
                <td><button type="button" class="small-button">Detail</button></td>
            </tr>
        `;
    }).join('');
}

function closeSidebar() {
    setSidebarState(false);
}

function openSidebar() {
    setSidebarState(true);
}

function login(event) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const username = String(form.get('username') || '').trim() || 'admin';
    const pin = String(form.get('pin') || '').trim();

    state.user = {
        username,
        nama: username,
        role: username === 'teknisi' ? 'Teknisi' : 'Super Admin',
        permissions: username === 'teknisi' ? { claim_garansi_asuransi: ['view', 'update'] } : { '*': ['view', 'create', 'update', 'delete', 'export'] },
    };

    if (!pin) {
        state.user = null;
        showToast('PIN wajib diisi');
        return;
    }

    localStorage.setItem('ppp_user', JSON.stringify(state.user));
    state.activeTab = isTeknisi() ? 'claim_garansi_asuransi' : 'dashboard';
    localStorage.setItem('ppp_active_tab', state.activeTab);
    showToast('Login berhasil');
    setScreen();
}

function logout() {
    state.user = null;
    localStorage.removeItem('ppp_user');
    showToast('Logout berhasil');
    setScreen();
}

function bindEvents() {
    $('[data-login-form]')?.addEventListener('submit', login);
    $('[data-logout]')?.addEventListener('click', logout);
    $('[data-open-sidebar]')?.addEventListener('click', () => {
        const isOpen = $('[data-sidebar]')?.classList.contains('is-open') === true;
        setSidebarState(!isOpen);
    });
    $('[data-sidebar-backdrop]')?.addEventListener('click', closeSidebar);
    $('[data-dismiss-alert]')?.addEventListener('click', () => $('[data-alert]')?.classList.add('is-hidden'));

    const sidebarTrigger = $('[data-open-sidebar]');
    ['pointerdown', 'touchstart'].forEach((eventName) => {
        sidebarTrigger?.addEventListener(eventName, () => sidebarTrigger.classList.add('is-pressed'), { passive: true });
    });
    ['pointerup', 'pointercancel', 'touchend', 'touchcancel', 'blur'].forEach((eventName) => {
        sidebarTrigger?.addEventListener(eventName, () => sidebarTrigger.classList.remove('is-pressed'));
    });

    $all('[data-tab-button]').forEach((button) => {
        button.addEventListener('click', () => activateTab(button.dataset.tabButton));
    });

    $all('[data-group-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const group = button.closest('.nav-group');
            group?.classList.toggle('is-open');
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    bindEvents();
    setSidebarState(false);
    setTimeout(setScreen, 350);
});
