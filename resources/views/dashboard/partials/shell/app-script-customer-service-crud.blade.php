@verbatim
                const {
                    loadStoryData,
                    loadUnboxingData,
                    loadOrderanOnlineData,
                    loadUnitDitanyaData,
                    loadClaimGaransiData,
                    loadKeepBarangData,
                    openCreateStoryModal,
                    openEditStoryModal,
                    saveStory,
                    deleteStory,
                    saveUnboxing,
                    deleteUnboxing,
                    saveOrderanOnline,
                    deleteOrderanOnline,
                    saveUnitDitanya,
                    deleteUnitDitanya,
                    saveClaimGaransi,
                    deleteClaimGaransi,
                    saveKeepBarang,
                    deleteKeepBarang,
                } = window.MarketingDashboardRuntimeHelpers.createCustomerServiceCrud({
                    ensureRunApi,
                    storyData,
                    unboxingData,
                    orderanOnlineData,
                    unitDitanyaData,
                    claimGaransiData,
                    keepBarangData,
                    storyTab,
                    storyForm,
                    storyModalType,
                    storyModalOpen,
                    submitting,
                    showNotification,
                    handleError,
                    showConfirm,
                    unboxingForm,
                    unboxingModalOpen,
                    unboxingModalType,
                    orderanOnlineForm,
                    orderanOnlineModalOpen,
                    unitDitanyaForm,
                    unitDitanyaModalOpen,
                    claimGaransiForm,
                    claimGaransiModalOpen,
                    keepBarangForm,
                    keepBarangModalOpen,
                    computeKeepBarangDerived,
                });

                const loadServiceData = () => new Promise((resolve) => {
                    ensureRunApi().withSuccessHandler((data) => { serviceData.value = Array.isArray(data) ? data : []; resolve(); }).withFailureHandler(() => resolve()).getServiceData();
                });
                const loadServiceClaimsData = () => new Promise((resolve) => {
                    ensureRunApi().withSuccessHandler((data) => { serviceClaimsData.value = Array.isArray(data) ? data : []; resolve(); }).withFailureHandler(() => resolve()).getServiceClaimsData();
                });
                const transferServiceClaim = (row) => {
                    if (submitting.value) return;
                    submitting.value = true;
                    ensureRunApi().withSuccessHandler((res) => {
                        submitting.value = false;
                        loadServiceClaimsData();
                        showNotification(res?.transferred ? 'Data berhasil ditransfer' : 'Data sudah ada');
                    }).withFailureHandler((err) => { submitting.value = false; handleError(err); }).transferServiceClaim(row.ID || row.source_id);
                };
                const saveServiceClaim = () => {
                    if (submitting.value) return;
                    submitting.value = true;
                    ensureRunApi().withSuccessHandler(() => {
                        submitting.value = false;
                        serviceClaimModalOpen.value = false;
                        loadServiceClaimsData();
                        showNotification('Detail Claim berhasil disimpan');
                    }).withFailureHandler((err) => { submitting.value = false; handleError(err); }).saveServiceClaim(serviceClaimForm.value);
                };
                const saveService = () => {
                    if (submitting.value) return;
                    submitting.value = true;
                    ensureRunApi().withSuccessHandler(() => {
                        submitting.value = false;
                        serviceModalOpen.value = false;
                        loadServiceData();
                        showNotification('Service berhasil disimpan');
                    }).withFailureHandler((err) => { submitting.value = false; handleError(err); }).saveService(serviceForm.value);
                };
                const deleteService = (id) => {
                    showConfirm('Hapus Service?', 'Data yang dihapus dari DB Marketing tidak dapat dikembalikan.', () => {
                        ensureRunApi().withSuccessHandler(() => { loadServiceData(); showNotification('Service berhasil dihapus'); }).deleteService(id);
                    });
                };
@endverbatim
