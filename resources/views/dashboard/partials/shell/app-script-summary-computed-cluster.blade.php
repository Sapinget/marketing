@verbatim
                // Summary cards per menu
                const _sCnt = (arr, fn) => { const m = {}; (arr || []).forEach(r => { let k = fn(r); k = (k == null ? '' : String(k)).trim(); if (!k) k = '-'; m[k] = (m[k] || 0) + 1; }); return m; };
                const _sSum = (arr, fn) => (arr || []).reduce((s, r) => s + (Number(fn(r)) || 0), 0);

                const masterSummary = computed(() => {
                    const d = filteredMasterPlanData.value || [];
                    const st = _sCnt(d, r => (r.Status || 'IDE').toUpperCase());
                    const done = (st['DONE'] || 0) + (st['PUBLISHED'] || 0);
                    const prog = (st['EDITING'] || 0) + (st['SHOOTING'] || 0) + (st['PROGRES'] || 0);
                    const now = new Date(); const ym = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
                    const month = d.filter(r => String(r.Tanggal_Rencana || '').startsWith(ym)).length;
                    return { cards: [
                        { label: 'Total Plan', value: formatNumber(d.length), unit: 'Konten', icon: 'fa-clipboard-list', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Semua rencana' },
                        { label: 'Selesai', value: formatNumber(done), icon: 'fa-circle-check', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Published / Done' },
                        { label: 'Dalam Proses', value: formatNumber(prog), icon: 'fa-spinner', color: 'text-amber-500', subColor: 'text-amber-600', sub: 'Shooting / Editing' },
                        { label: 'Bulan Ini', value: formatNumber(month), unit: 'Plan', icon: 'fa-calendar-day', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Periode aktif' },
                    ] };
                });
                const ideationSummary = computed(() => {
                    const d = filteredMasterPlanData.value || [];
                    const fmt = _sCnt(d, r => r.Format_Konten);
                    const plat = _sCnt(d, r => (r.Platforms || '').split(',')[0]);
                    const ide = d.filter(r => (r.Status || '').toUpperCase() === 'IDE').length;
                    const topFormat = Object.entries(fmt).sort((a, b) => b[1] - a[1])[0] || null;
                    return { cards: [
                        { label: 'Total Ide', value: formatNumber(d.length), unit: 'Konten', icon: 'fa-lightbulb', color: 'text-amber-500', unitColor: 'text-amber-400', subColor: 'text-amber-600', sub: 'Semua ide' },
                        { label: 'Status Ide', value: formatNumber(ide), icon: 'fa-seedling', color: 'text-blue-500', subColor: 'text-blue-600', sub: 'Belum diproduksi' },
                        { label: 'Format', value: formatNumber(Object.keys(fmt).length), unit: 'Jenis', icon: 'fa-shapes', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Variasi format' },
                        { label: 'Platform', value: formatNumber(Object.keys(plat).length), unit: 'Channel', icon: 'fa-share-nodes', color: 'text-emerald-500', unitColor: 'text-emerald-400', subColor: 'text-emerald-600', sub: 'Distribusi' },
                        { label: 'Top Format', value: topFormat ? String(topFormat[0]).toUpperCase() : '-', icon: 'fa-film', color: 'text-violet-500', sub: formatNumber(topFormat ? topFormat[1] : 0) + ' konten', subColor: 'text-violet-600' },
                    ] };
                });
                const distributionSummary = computed(() => {
                    const d = filteredDistributionData.value || [];
                    const linked = d.filter(r => String(r.Link || '').trim()).length;
                    const plat = _sCnt(d, r => r.Platform);
                    const topPlatform = Object.entries(plat).sort((a, b) => b[1] - a[1])[0] || null;
                    return { cards: [
                        { label: 'Total Distribusi', value: formatNumber(d.length), unit: 'Konten', icon: 'fa-share-from-square', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Semua distribusi' },
                        { label: 'Ada Link', value: formatNumber(linked), icon: 'fa-link', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Terpublikasi' },
                        { label: 'Belum Link', value: formatNumber(d.length - linked), icon: 'fa-link-slash', color: 'text-rose-500', subColor: 'text-rose-600', sub: 'Belum ada link' },
                        { label: 'Platform', value: formatNumber(Object.keys(plat).length), unit: 'Channel', icon: 'fa-tower-broadcast', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Tersebar' },
                        { label: 'Top Platform', value: topPlatform ? String(topPlatform[0]).toUpperCase() : '-', icon: 'fa-share-nodes', color: 'text-blue-500', sub: formatNumber(topPlatform ? topPlatform[1] : 0) + ' konten', subColor: 'text-blue-600' },
                    ] };
                });
                const analyticsSummary = computed(() => {
                    const d = filteredAnalyticsData.value || [];
                    const views = _sSum(d, r => r.Views), likes = _sSum(d, r => r.Likes), comments = _sSum(d, r => r.Comments);
                    const platV = {}; d.forEach(r => { const k = (r.Platform || '-'); platV[k] = (platV[k] || 0) + (Number(r.Views) || 0); });
                    const topPlatformViews = Object.entries(platV).sort((a, b) => b[1] - a[1])[0] || null;
                    return { cards: [
                        { label: 'Total Views', value: formatNumber(views), icon: 'fa-eye', color: 'text-blue-500', subColor: 'text-blue-600', sub: formatNumber(d.length) + ' konten' },
                        { label: 'Total Likes', value: formatNumber(likes), icon: 'fa-heart', color: 'text-rose-500', subColor: 'text-rose-600', sub: 'Engagement' },
                        { label: 'Total Comments', value: formatNumber(comments), icon: 'fa-comment', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Interaksi' },
                        { label: 'Total Konten', value: formatNumber(d.length), unit: 'Post', icon: 'fa-photo-film', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Dianalisa' },
                        { label: 'Top Platform Views', value: topPlatformViews ? String(topPlatformViews[0]).toUpperCase() : '-', icon: 'fa-chart-bar', color: 'text-emerald-500', sub: formatNumber(topPlatformViews ? topPlatformViews[1] : 0) + ' views', subColor: 'text-emerald-600' },
                    ] };
                });
                const unboxingSummary = computed(() => {
                    const d = filteredUnboxingData.value || [];
                    const st = _sCnt(d, r => r.Status);
                    const done = Object.entries(st).filter(([k]) => ['DONE', 'SELESAI', 'PUBLISHED', 'UPLOAD', 'UPLOADED'].includes(k.toUpperCase())).reduce((s, [, n]) => s + n, 0);
                    const editors = _sCnt(d, r => r.Editor);
                    return { cards: [
                        { label: 'Total Unboxing', value: formatNumber(d.length), unit: 'Video', icon: 'fa-box-open', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Semua unboxing' },
                        { label: 'Selesai', value: formatNumber(done), icon: 'fa-circle-check', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Sudah upload' },
                        { label: 'Proses', value: formatNumber(d.length - done), icon: 'fa-spinner', color: 'text-amber-500', subColor: 'text-amber-600', sub: 'Belum selesai' },
                        { label: 'Editor', value: formatNumber(Object.keys(editors).length), unit: 'Orang', icon: 'fa-user-pen', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Terlibat' },
                    ] };
                });
                const storySummary = computed(() => {
                    const d = storyData.value || [];
                    const genap = d.filter(r => r.is_genap === true || r.is_genap === 1 || String(r.is_genap) === '1').length;
                    const st = _sCnt(d, r => r.Status);
                    const topStatus = Object.entries(st).sort((a, b) => b[1] - a[1])[0] || null;
                    return { cards: [
                        { label: 'Total Story', value: formatNumber(d.length), unit: 'Jadwal', icon: 'fa-clapperboard', color: 'text-rose-500', unitColor: 'text-rose-400', subColor: 'text-rose-600', sub: 'Semua story' },
                        { label: 'Ganjil', value: formatNumber(d.length - genap), icon: 'fa-circle-half-stroke', color: 'text-blue-500', subColor: 'text-blue-600', sub: 'Minggu ganjil' },
                        { label: 'Genap', value: formatNumber(genap), icon: 'fa-circle', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Minggu genap' },
                        { label: 'Status', value: formatNumber(Object.keys(st).length), unit: 'Tipe', icon: 'fa-list-check', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Variasi status' },
                        { label: 'Top Status', value: topStatus ? String(topStatus[0]).toUpperCase() : '-', icon: 'fa-tag', color: 'text-amber-500', sub: formatNumber(topStatus ? topStatus[1] : 0) + ' item', subColor: 'text-amber-600' },
                    ] };
                });
                const promoSummary = computed(() => {
                    const d = filteredPromoData.value || [];
                    const kat = _sCnt(d, r => r.Kategori);
                    const totalHarga = _sSum(d, r => r.Harga);
                    const topCategory = Object.entries(kat).sort((a, b) => b[1] - a[1])[0] || null;
                    return { cards: [
                        { label: 'Total Program', value: formatNumber(d.length), unit: 'Promo', icon: 'fa-tags', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Semua program' },
                        { label: 'Kategori', value: formatNumber(Object.keys(kat).length), unit: 'Jenis', icon: 'fa-layer-group', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Variasi kategori' },
                        { label: 'Total Nilai', value: formatCurrency(totalHarga), icon: 'fa-money-bill-wave', color: 'text-amber-500', subColor: 'text-amber-600', sub: 'Akumulasi harga' },
                        { label: 'Rata Harga', value: formatCurrency(d.length ? totalHarga / d.length : 0), icon: 'fa-calculator', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Per program' },
                        { label: 'Top Kategori', value: topCategory ? String(topCategory[0]).toUpperCase() : '-', icon: 'fa-tag', color: 'text-rose-500', sub: formatNumber(topCategory ? topCategory[1] : 0) + ' program', subColor: 'text-rose-600' },
                    ] };
                });
                const orderanSummary = computed(() => {
                    const d = filteredOrderanOnlineData.value || [];
                    const st = _sCnt(d, r => r.STATUS);
                    const cair = _sSum(d, r => (r['NOMINAL CAIR'] != null ? r['NOMINAL CAIR'] : r.HARGA_ONLINE));
                    const done = Object.entries(st).filter(([k]) => ['SELESAI', 'DONE', 'CAIR', 'LUNAS'].includes(k.toUpperCase())).reduce((s, [, n]) => s + n, 0);
                    const kirim = _sCnt(d, r => r.PENGIRIMAN);
                    return { cards: [
                        { label: 'Total Orderan', value: formatNumber(d.length), unit: 'Order', icon: 'fa-cart-shopping', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Semua orderan' },
                        { label: 'Total Cair', value: formatCurrency(cair), icon: 'fa-money-bill-trend-up', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Nominal masuk' },
                        { label: 'Selesai', value: formatNumber(done), icon: 'fa-circle-check', color: 'text-violet-500', subColor: 'text-violet-600', sub: 'Order tuntas' },
                        { label: 'Ekspedisi', value: formatNumber(Object.keys(kirim).length), unit: 'Jasa', icon: 'fa-truck', color: 'text-amber-500', unitColor: 'text-amber-400', subColor: 'text-amber-600', sub: 'Pengiriman' },
                    ] };
                });
                const unitDitanyaSummary = computed(() => {
                    const d = filteredUnitDitanyaData.value || [];
                    const ditanya = _sSum(d, r => r.DITANYA);
                    const avail = d.filter(r => { const v = String(r.AVAILABLE || '').toUpperCase(); return v === 'YA' || v === 'ADA' || v === 'AVAILABLE' || v === '1' || v === 'TRUE' || Number(r.AVAILABLE) > 0; }).length;
                    const brand = _sCnt(d, r => r.BRAND);
                    const topBrand = Object.entries(brand).sort((a, b) => b[1] - a[1])[0] || null;
                    return { cards: [
                        { label: 'Total Unit', value: formatNumber(d.length), unit: 'Tipe', icon: 'fa-mobile-screen', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Data unit' },
                        { label: 'Total Ditanya', value: formatNumber(ditanya), icon: 'fa-comments', color: 'text-amber-500', subColor: 'text-amber-600', sub: 'Akumulasi' },
                        { label: 'Available', value: formatNumber(avail), icon: 'fa-circle-check', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Ready stock' },
                        { label: 'Brand', value: formatNumber(Object.keys(brand).length), unit: 'Merek', icon: 'fa-tags', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Variasi brand' },
                        { label: 'Top Brand', value: topBrand ? String(topBrand[0]).toUpperCase() : '-', icon: 'fa-mobile-screen', color: 'text-blue-500', sub: formatNumber(topBrand ? topBrand[1] : 0) + ' unit', subColor: 'text-blue-600' },
                    ] };
                });
                const claimSummary = computed(() => {
                    const d = filteredClaimGaransiData.value || [];
                    const st = _sCnt(d, r => r.STATUS);
                    const done = Object.entries(st).filter(([k]) => ['SELESAI', 'CLAIM', 'DONE', 'PROCESED', 'PROCESSED'].includes(k.toUpperCase())).reduce((s, [, n]) => s + n, 0);
                    const pending = (st['NOT STARTED'] || 0) + (st['PENDING'] || 0);
                    const gar = _sCnt(d, r => r.GARANSI);
                    return { cards: [
                        { label: 'Total Klaim', value: formatNumber(d.length), unit: 'Unit', icon: 'fa-shield-halved', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Semua klaim' },
                        { label: 'Selesai', value: formatNumber(done), icon: 'fa-circle-check', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Klaim beres' },
                        { label: 'Pending', value: formatNumber(pending), icon: 'fa-hourglass-half', color: 'text-amber-500', subColor: 'text-amber-600', sub: 'Belum diproses' },
                        { label: 'Jenis Garansi', value: formatNumber(Object.keys(gar).length), unit: 'Tipe', icon: 'fa-file-shield', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Variasi garansi' },
                    ] };
                });
                const hargaSummary = computed(() => {
                    const d = filteredHargaKompetitorData.value || [];
                    const margins = d.map(r => Number(r.Margin_Profit) || 0);
                    const avg = d.length ? margins.reduce((a, b) => a + b, 0) / d.length : 0;
                    let pct20plus = 0, pct10to20 = 0, pctUnder10 = 0, pctNeg = 0;
                    margins.forEach(m => { if (m > 20) pct20plus++; else if (m >= 10) pct10to20++; else if (m >= 0) pctUnder10++; else pctNeg++; });
                    return { cards: [
                        { label: 'Total Produk', value: formatNumber(d.length), unit: 'Item', icon: 'fa-box', color: 'text-blue-500', unitColor: 'text-blue-400', subColor: 'text-blue-600', sub: 'Dipantau' },
                        { label: 'Avg Margin', value: (Math.round(avg * 10) / 10) + '%', icon: 'fa-percent', color: 'text-emerald-500', subColor: 'text-emerald-600', sub: 'Rata margin' },
                        { label: '>20% Margin', value: formatNumber(pct20plus), icon: 'fa-circle-check', color: 'text-emerald-500', sub: 'Margin sehat', subColor: 'text-emerald-600' },
                        { label: '10-20%', value: formatNumber(pct10to20), icon: 'fa-circle-half-stroke', color: 'text-blue-500', sub: 'Margin layak', subColor: 'text-blue-600' },
                        { label: '<10%', value: formatNumber(pctUnder10), icon: 'fa-triangle-exclamation', color: 'text-amber-500', sub: 'Margin tipis', subColor: 'text-amber-600' },
                        { label: 'Negatif', value: formatNumber(pctNeg), icon: 'fa-circle-xmark', color: 'text-rose-500', sub: 'Margin rugi', subColor: 'text-rose-600' },
                    ] };
                });
                const lpjkSummary = computed(() => {
                    const d = lpjkData.value || [];
                    const budget = _sSum(d, r => r.Budget_Rencana);
                    const real = _sSum(d, r => r.Realisasi_Biaya);
                    const st = _sCnt(d, r => r.Status);
                    const sisa = budget - real;
                    const topStatus = Object.entries(st).sort((a, b) => b[1] - a[1])[0] || null;
                    return { cards: [
                        { label: 'Total Event', value: formatNumber(d.length), unit: 'Event', icon: 'fa-calendar-check', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Semua event' },
                        { label: 'Total Budget', value: formatCurrency(budget), icon: 'fa-wallet', color: 'text-blue-500', subColor: 'text-blue-600', sub: 'Rencana' },
                        { label: 'Realisasi', value: formatCurrency(real), icon: 'fa-money-check-dollar', color: 'text-amber-500', subColor: 'text-amber-600', sub: 'Terpakai' },
                        { label: 'Sisa', value: formatCurrency(sisa), icon: 'fa-piggy-bank', color: sisa >= 0 ? 'text-emerald-500' : 'text-rose-500', subColor: sisa >= 0 ? 'text-emerald-600' : 'text-rose-600', sub: sisa >= 0 ? 'Hemat' : 'Over budget' },
                        { label: 'Top Status', value: topStatus ? String(topStatus[0]).toUpperCase() : '-', icon: 'fa-circle-dot', color: 'text-violet-500', sub: formatNumber(topStatus ? topStatus[1] : 0) + ' event', subColor: 'text-violet-600' },
                    ] };
                });
@endverbatim
