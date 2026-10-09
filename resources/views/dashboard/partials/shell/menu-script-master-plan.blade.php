@verbatim
                const toProperCase = (value) => String(value || '')
                    .toLowerCase()
                    .replace(/\b\w/g, (char) => char.toUpperCase());

                const masterSummary = computed(() => {
                    const d = filteredMasterPlanData.value || [];
                    const st = _sCnt(d, r => (r.Status || 'IDE').toUpperCase());
                    const done = (st['DONE'] || 0) + (st['PUBLISHED'] || 0);
                    const prog = (st['EDITING'] || 0) + (st['SHOOTING'] || 0) + (st['PROGRES'] || 0);
                    const now = new Date(); const ym = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
                    const month = d.filter(r => String(r.Tanggal_Rencana || '').startsWith(ym)).length;
                    return { cards: limitSummaryCards([
                        { label: 'Total Plan', value: formatNumber(d.length), unit: 'Konten', icon: 'fa-clipboard-list', color: 'text-amber', unitColor: 'text-amber', subColor: 'text-amber', sub: 'Semua rencana' },
                        { label: 'Selesai', value: formatNumber(done), icon: 'fa-circle-check', color: 'text-success', subColor: 'text-success', sub: 'Published / Done' },
                        { label: 'Dalam Proses', value: formatNumber(prog), icon: 'fa-spinner', color: 'text-amber', subColor: 'text-amber', sub: 'Shooting / Editing' },
                        { label: 'Bulan Ini', value: formatNumber(month), unit: 'Plan', icon: 'fa-calendar-day', color: 'text-slate-500', unitColor: 'text-slate-400', subColor: 'text-slate-600', sub: 'Periode aktif' },
                    ]) };
                });

                Object.assign(menuExports, {
                    toProperCase,
                    masterSummary,
                });
@endverbatim
