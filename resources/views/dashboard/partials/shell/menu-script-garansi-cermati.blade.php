@verbatim
                const garansiCermatiSummary = computed(() => {
                    const d = filteredGaransiCermatiRows.value || [];
                    const st = _sCnt(d, r => String(r['Status perbaikan'] || '').trim());
                    const done = st['Service Done'] || 0;
                    const proses = st['Proses Perbaikan'] || 0;
                    const belum = st['Belum Service'] || 0;
                    return { cards: limitSummaryCards([
                        { label: 'Total Klaim', value: formatNumber(d.length), unit: 'Unit', icon: 'fa-shield-halved', color: 'text-amber', unitColor: 'text-amber', subColor: 'text-amber', sub: 'Semua klaim cermati' },
                        { label: 'Service Done', value: formatNumber(done), icon: 'fa-circle-check', color: 'text-success', subColor: 'text-success', sub: 'Perbaikan selesai' },
                        { label: 'Proses', value: formatNumber(proses), icon: 'fa-spinner', color: 'text-amber', subColor: 'text-amber', sub: 'Sedang diperbaiki' },
                        { label: 'Belum Service', value: formatNumber(belum), icon: 'fa-hourglass-half', color: 'text-slate-500', subColor: 'text-slate-600', sub: 'Menunggu service' },
                    ]) };
                });

                const garansiCermatiTotalPages = computed(() => Math.max(1, Math.ceil(filteredGaransiCermatiRows.value.length / PAGE_SIZE)));

                const pagedGaransiCermatiRows = computed(() => filteredGaransiCermatiRows.value.slice((garansiCermatiPage.value - 1) * PAGE_SIZE, garansiCermatiPage.value * PAGE_SIZE));

                Object.assign(menuExports, {
                    garansiCermatiSummary,
                    garansiCermatiTotalPages,
                    pagedGaransiCermatiRows,
                });
@endverbatim
