@verbatim
                // Budgeting
                const budgetCalculations = computed(() => {
                    const cfg = budgetConfig.value;
                    const safe = (v) => Number(v) || 0;
                    const metaTotal = safe(cfg.meta.costPerAd) * safe(cfg.meta.totalAds) * safe(cfg.meta.days);
                    const metaTopup = Math.max(0, metaTotal - safe(cfg.meta.balance));
                    const googleTotal = safe(cfg.google.costPerAd) * safe(cfg.google.totalAds) * safe(cfg.google.days);
                    const googleTopup = Math.max(0, googleTotal - safe(cfg.google.balance));
                    const mekariVisitorTotal = safe(cfg.mekari.visitor.targetPerDay) * safe(cfg.mekari.visitor.days);
                    const mekariVisitorNeeded = Math.max(0, mekariVisitorTotal - safe(cfg.mekari.visitor.balance));
                    const mekariBroadcastTotal = (safe(cfg.mekari.broadcast.costPerWeek) * safe(cfg.mekari.broadcast.weeks)) + safe(cfg.mekari.broadcast.specialPrice);
                    const mekariBroadcastTopup = Math.max(0, mekariBroadcastTotal - safe(cfg.mekari.broadcast.balance));
                    const mekariTopupTotal = safe(cfg.mekari.visitor.topupCost) + mekariBroadcastTopup;
                    const othersCalculated = (cfg.others || []).map(item => {
                        const total = safe(item.costPerUnit) * safe(item.quantity) * safe(item.duration);
                        const topup = Math.max(0, total - safe(item.balance));
                        return { ...item, total, topup };
                    });
                    const dStart = budgetDateFilter.value.start;
                    const dEnd = budgetDateFilter.value.end;
                    const inRange = (row) => {
                        const d = row.Tanggal_Rencana || '';
                        if (dStart && d < dStart) return false;
                        if (dEnd && d > dEnd) return false;
                        return true;
                    };
                    const colabBreakdown = (cfg.colabPartners || []).map(p => {
                        const used = masterPlanData.value.filter(row => {
                            const names = (row.Colab || '').split(',').map(c => c.trim()).filter(Boolean);
                            return names.includes(p.name) && inRange(row);
                        }).length;
                        const remaining = safe(p.slots) - used;
                        return { name: p.name, packageCost: safe(p.packageCost), slots: safe(p.slots), used, remaining };
                    });
                    const colabList = masterPlanData.value.filter(row => row.Colab && inRange(row)).flatMap(row =>
                        (row.Colab || '').split(',').map(c => c.trim()).filter(Boolean).map(partner => ({
                            colabPartner: partner, Judul: row.Judul, Tanggal_Rencana: row.Tanggal_Rencana
                        }))
                    );
                    const colabTopup = (cfg.colabPartners || []).reduce((s, p) => s + safe(p.packageCost), 0);
                    const othersTopup = othersCalculated.reduce((s, i) => s + i.topup, 0);
                    const totalTopup = metaTopup + googleTopup + mekariTopupTotal + colabTopup + othersTopup;
                    return { metaTotal, metaTopup, googleTotal, googleTopup, mekariVisitorTotal, mekariVisitorNeeded, mekariBroadcastTotal, mekariBroadcastTopup, mekariTopupTotal, othersCalculated, colabBreakdown, colabList, totalTopup };
                });

                const budgetSummary = computed(() => {
                    const c = budgetCalculations.value;
                    const cards = [
                        { label: 'Meta Ads', value: formatCurrency(c.metaTotal), icon: 'fa-facebook', iconPrefix: 'fa-brands', color: 'text-blue-500', subColor: 'text-blue-600', sub: 'Anggaran iklan' },
                        { label: 'Google Ads', value: formatCurrency(c.googleTotal), icon: 'fa-google', iconPrefix: 'fa-brands', color: 'text-blue-500', subColor: 'text-blue-600', sub: 'Anggaran iklan' },
                        { label: 'Mekari', value: formatCurrency(c.mekariVisitorTotal + c.mekariBroadcastTotal), icon: 'fa-bullhorn', color: 'text-amber-500', subColor: 'text-amber-600', sub: 'Ecosystem total' },
                        { label: 'Colab', value: formatNumber(c.colabBreakdown.length), unit: 'Partner', icon: 'fa-handshake', color: 'text-violet-500', unitColor: 'text-violet-400', subColor: 'text-violet-600', sub: 'Paid collaboration' },
                    ];
                    c.othersCalculated.forEach(o => {
                        cards.push({ label: o.name || 'Other', value: formatCurrency(o.total), icon: 'fa-layer-group', color: 'text-slate-500', subColor: 'text-slate-600', sub: 'Additional platform' });
                    });
                    return { cards, chips: [] };
                });

                const saveBudgetServer = () => {
                    const cfg = JSON.parse(JSON.stringify(budgetConfig.value));
                    localStorage.setItem('ppp_budgetConfig', JSON.stringify(cfg));
                    ensureRunApi()
                        .withSuccessHandler(() => {
                            showBudgetSettings.value = false;
                            showNotification('Konfigurasi budget disimpan');
                        })
                        .withFailureHandler((error) => {
                            notifyError('Gagal menyimpan konfigurasi budget', error, 'Konfigurasi budget belum tersimpan ke server.');
                        })
                        .saveBudgetingConfig(cfg);
                };

                const exportBudgetToExcel = () => {
                    window.MarketingDashboardReportingExports.exportBudgetToExcel({
                        config: budgetConfig.value,
                        calculations: budgetCalculations.value,
                        showNotification,
                        notifyError,
                    });
                };
@endverbatim
