@verbatim
                const inputClaimSummary = computed(() => {
                    const d = filteredInputClaimRows.value || [];
                    const st = _sCnt(d, r => String(r['Status'] || '').trim());
                    const selesai = st['Selesai'] || 0;
                    const proses = st['Proses'] || 0;
                    const toko = _sCnt(d, r => String(r['Toko'] || '').trim());
                    return { cards: limitSummaryCards([
                        { label: 'Total Complain', value: formatNumber(d.length), unit: 'Kasus', icon: 'fa-file-pen', color: 'text-amber', unitColor: 'text-amber', subColor: 'text-amber', sub: 'Semua komplain masuk' },
                        { label: 'Selesai', value: formatNumber(selesai), icon: 'fa-circle-check', color: 'text-success', subColor: 'text-success', sub: 'Komplain tuntas' },
                        { label: 'Proses', value: formatNumber(proses), icon: 'fa-spinner', color: 'text-amber', subColor: 'text-amber', sub: 'Sedang ditangani' },
                        { label: 'Toko', value: formatNumber(Object.keys(toko).filter(k => k !== '-').length), unit: 'Lokasi', icon: 'fa-store', color: 'text-slate-500', unitColor: 'text-slate-400', subColor: 'text-slate-600', sub: 'Sumber complain' },
                    ]) };
                });

                const inputClaimTotalPages = computed(() => Math.max(1, Math.ceil(filteredInputClaimRows.value.length / PAGE_SIZE)));

                const pagedInputClaimRows = computed(() => filteredInputClaimRows.value.slice((inputClaimPage.value - 1) * PAGE_SIZE, inputClaimPage.value * PAGE_SIZE));

                Object.assign(menuExports, {
                    inputClaimSummary,
                    inputClaimTotalPages,
                    pagedInputClaimRows,
                });
@endverbatim
