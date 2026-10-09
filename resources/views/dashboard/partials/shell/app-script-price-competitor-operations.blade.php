@verbatim
                const hargaKompetitorSuggestion = computed(() => {
                    const form = hargaKompetitorForm.value;
                    const distributorCost = Math.max(Number(form.Harga_Distributor_1) || 0, Number(form.Harga_Distributor_2) || 0);
                    const competitorPrice = Number(form.Harga_Kompetitor) || 0;
                    const competitorProfit = competitorPrice - distributorCost;
                    const useCompetitiveSuggestion = competitorProfit > 200000;
                    const suggestedPrice = useCompetitiveSuggestion
                        ? Math.max(0, competitorPrice - 100000)
                        : distributorCost + 100000;
                    const suggestedProfit = suggestedPrice - distributorCost;
                    return {
                        distributorCost,
                        competitorPrice,
                        competitorProfit,
                        useCompetitiveSuggestion,
                        suggestedPrice,
                        suggestedProfit,
                        canSuggest: competitorPrice > 0 && distributorCost > 0,
                    };
                });
                const hargaKompetitorCalculatedMargin = computed(() => {
                    const form = hargaKompetitorForm.value;
                    const distributorCost = Math.max(Number(form.Harga_Distributor_1) || 0, Number(form.Harga_Distributor_2) || 0);
                    const plannedPrice = Number(form.Harga_Rencana_Jual) || 0;
                    return plannedPrice > 0 && distributorCost > 0 ? plannedPrice - distributorCost : 0;
                });
                const hargaKompetitorLastAutoSuggestion = ref(0);
                const buildHargaKompetitorProductName = (form) => [
                    form.BRAND,
                    form.SERI,
                    form.RAM,
                    form.INTERNAL,
                    form.SIZE,
                    form.WARNA,
                ].map(value => String(value || '').trim()).filter(Boolean).join(' ');
                watch(hargaKompetitorSuggestion, suggestion => {
                    const currentPlannedPrice = Number(hargaKompetitorForm.value.Harga_Rencana_Jual) || 0;
                    const canReplace = currentPlannedPrice === 0 || currentPlannedPrice === hargaKompetitorLastAutoSuggestion.value;
                    if (suggestion.canSuggest && canReplace) {
                        hargaKompetitorForm.value.Harga_Rencana_Jual = suggestion.suggestedPrice;
                        hargaKompetitorLastAutoSuggestion.value = suggestion.suggestedPrice;
                    }
                    if (!suggestion.canSuggest && currentPlannedPrice === hargaKompetitorLastAutoSuggestion.value) {
                        hargaKompetitorForm.value.Harga_Rencana_Jual = 0;
                        hargaKompetitorLastAutoSuggestion.value = 0;
                    }
                });
                watch([hargaKompetitorSearch, hargaKompetitorDateFilter], () => { hargaKompetitorPage.value = 1; }, { deep: true });

                const saveHargaKompetitor = () => {
                    const form = hargaKompetitorForm.value;
                    form.Nama_Produk = buildHargaKompetitorProductName(form) || form.Nama_Produk || '';
                    form.Selisih = (form.Harga_Rencana_Jual || 0) - (form.Harga_Kompetitor || 0);
                    form.Margin_Profit = hargaKompetitorCalculatedMargin.value;
                    submitting.value = true;
                    ensureRunApi()
                        .withSuccessHandler(res => {
                            submitting.value = false;
                            if (!form.ID) {
                                hargaKompetitorData.value.unshift({ ...form, ID: (res && res.data && res.data.source_id) || (res && res.id) || ('HK' + Date.now()) });
                            } else {
                                const idx = hargaKompetitorData.value.findIndex(r => String(r.ID) === String(form.ID));
                                if (idx !== -1) hargaKompetitorData.value.splice(idx, 1, { ...form });
                            }
                            hargaKompetitorModalOpen.value = false;
                            showNotification('Data harga disimpan');
                        })
                        .withFailureHandler(err => { submitting.value = false; handleError(err); })
                        .saveHargaKompetitor(form);
                };

                const deleteHargaKompetitor = (id) => {
                    if (!confirm('Hapus data ini?')) return;
                    ensureRunApi()
                        .withSuccessHandler(() => {
                            hargaKompetitorData.value = hargaKompetitorData.value.filter(r => String(r.ID) !== String(id));
                            showNotification('Data dihapus');
                        })
                        .withFailureHandler(err => handleError(err))
                        .deleteHargaKompetitor(id);
                };
@endverbatim
