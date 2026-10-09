@verbatim
                // Summary cards per menu
                const _sCnt = (arr, fn) => { const m = {}; (arr || []).forEach(r => { let k = fn(r); k = (k == null ? '' : String(k)).trim(); if (!k) k = '-'; m[k] = (m[k] || 0) + 1; }); return m; };
                const _sSum = (arr, fn) => (arr || []).reduce((s, r) => s + (Number(fn(r)) || 0), 0);
                const limitSummaryCards = (cards) => (cards || []).slice(0, 5);

                const ideationSummary = computed(() => {
                    const d = filteredMasterPlanData.value || [];
                    const fmt = _sCnt(d, r => r.Format_Konten);
                    const plat = _sCnt(d, r => (r.Platforms || '').split(',')[0]);
                    const ide = d.filter(r => (r.Status || '').toUpperCase() === 'IDE').length;
                    const topFormat = Object.entries(fmt).sort((a, b) => b[1] - a[1])[0] || null;
                    return { cards: limitSummaryCards([
                        { label: 'Total Ide', value: formatNumber(d.length), unit: 'Konten', icon: 'fa-lightbulb', color: 'text-amber', unitColor: 'text-amber', subColor: 'text-amber', sub: 'Semua ide' },
                        { label: 'Status Ide', value: formatNumber(ide), icon: 'fa-seedling', color: 'text-amber', subColor: 'text-amber', sub: 'Belum diproduksi' },
                        { label: 'Format', value: formatNumber(Object.keys(fmt).length), unit: 'Jenis', icon: 'fa-shapes', color: 'text-slate-500', unitColor: 'text-slate-400', subColor: 'text-slate-600', sub: 'Variasi format' },
                        { label: 'Platform', value: formatNumber(Object.keys(plat).length), unit: 'Channel', icon: 'fa-share-nodes', color: 'text-success', unitColor: 'text-success', subColor: 'text-success', sub: 'Distribusi' },
                        { label: 'Top Format', value: topFormat ? String(topFormat[0]).toUpperCase() : '-', icon: 'fa-film', color: 'text-slate-500', sub: formatNumber(topFormat ? topFormat[1] : 0) + ' konten', subColor: 'text-slate-600' },
                    ]) };
                });
                const serviceSummary = computed(() => {
                    const d = filteredServiceData.value || [];
                    const st = _sCnt(d, r => r.STATUS || r.status);
                    const done = Object.entries(st).filter(([k]) => ['SELESAI', 'DONE', 'FINISH', 'DIAMBIL'].includes(k.toUpperCase())).reduce((s, [, n]) => s + n, 0);
                    const pending = Object.entries(st).filter(([k]) => ['PENDING', 'PROSES', 'ON PROGRESS', 'ANTRIAN'].includes(k.toUpperCase())).reduce((s, [, n]) => s + n, 0);
                    return { cards: limitSummaryCards([
                        { label: 'Total Service', value: formatNumber(d.length), unit: 'Data', icon: 'fa-screwdriver-wrench', color: 'text-amber', unitColor: 'text-amber', subColor: 'text-amber', sub: 'Semua service' },
                        { label: 'Selesai', value: formatNumber(done), icon: 'fa-circle-check', color: 'text-success', subColor: 'text-success', sub: 'Service tuntas' },
                        { label: 'Proses', value: formatNumber(pending), icon: 'fa-clock', color: 'text-amber', subColor: 'text-amber', sub: 'Dalam proses' },
                        { label: 'Status', value: formatNumber(Object.keys(st).length), unit: 'Jenis', icon: 'fa-list-check', color: 'text-slate-500', unitColor: 'text-slate-400', subColor: 'text-slate-600', sub: 'Variasi status' },
                    ]) };
                });
                const serviceClaimSummary = computed(() => {
                    const d = filteredServiceClaimData.value || [];
                    const lengkap = d.filter(r => r.NO_TRANSAKSI || r.LOKASI_KLAIM || r.GARANSI || r.TANGGAL_ESTIMASI || r.TANGGAL_DIAMBIL).length;
                    const st = _sCnt(d, r => r.STATUS);
                    return { cards: limitSummaryCards([
                        { label: 'Total Claim', value: formatNumber(d.length), unit: 'Data', icon: 'fa-file-shield', color: 'text-amber', unitColor: 'text-amber', subColor: 'text-amber', sub: 'Dari service' },
                        { label: 'Dilengkapi', value: formatNumber(lengkap), icon: 'fa-clipboard-check', color: 'text-success', subColor: 'text-success', sub: 'Field tambahan' },
                        { label: 'Belum Lengkap', value: formatNumber(Math.max(0, d.length - lengkap)), icon: 'fa-circle-exclamation', color: 'text-danger', subColor: 'text-danger', sub: 'Perlu update' },
                        { label: 'Status', value: formatNumber(Object.keys(st).length), unit: 'Jenis', icon: 'fa-list-check', color: 'text-slate-500', unitColor: 'text-slate-400', subColor: 'text-slate-600', sub: 'Dari service' },
                    ]) };
                });
@endverbatim
