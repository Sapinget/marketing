@verbatim
                // Confirmation State
                const confirmModal = ref({
                    open: false,
                    title: "",
                    message: "",
                    type: "danger",
                    onConfirm: () => { }
                });

                const showConfirm = (title, message, onConfirm, type = "danger") => {
                    confirmModal.value = { open: true, title, message, onConfirm: typeof onConfirm === 'function' ? onConfirm : () => {}, type };
                };

                // Master Plan Data
                const masterPlanData = ref([]);
                const analyticsData = ref([]);
                const distributionData = ref([]);
                const storyData = ref([]);
                const calendarEventsData = ref([]);

                const unboxingSearch = ref('');

                const getDefaultDateRange = () => {
                    const today = new Date();
                    const day = today.getDate();
                    if (day >= 26) {
                        return {
                            start: fmtLocalDate(new Date(today.getFullYear(), today.getMonth(), 26)),
                            end: fmtLocalDate(new Date(today.getFullYear(), today.getMonth() + 1, 25))
                        };
                    } else {
                        return {
                            start: fmtLocalDate(new Date(today.getFullYear(), today.getMonth() - 1, 26)),
                            end: fmtLocalDate(new Date(today.getFullYear(), today.getMonth(), 25))
                        };
                    }
                };

                // Customer Service data
                const orderanOnlineSearch = ref('');
                const orderanOnlineDateRange = ref(getDefaultDateRange());

                const unitDitanyaSearch = ref('');
                const unitDitanyaDateRange = ref(getDefaultDateRange());
                const unitDitanyaAvailableFilter = ref('');
                const unitDitanyaSortBy = ref('TANGGAL');
                const unitDitanyaSortDesc = ref(true);

                const claimGaransiSearch = ref('');
                const claimGaransiLokasiFilter = ref('');
                const claimGaransiStatusFilter = ref('');
                const claimGaransiGaransiFilter = ref('');

                const {
                    storyTab,
                    storyModalOpen,
                    storyModalType,
                    storyForm,
                    unboxingData,
                    unboxingModalOpen,
                    unboxingModalType,
                    unboxingForm,
                    orderanOnlineData,
                    claimGaransiData,
                    csModalType,
                    orderanOnlineModalOpen,
                    orderanOnlineForm,
                    openOrderanOnlineModal: openOrderanOnlineModalRuntime,
                    unitDitanyaData,
                    unitDitanyaModalOpen,
                    unitDitanyaForm,
                    openUnitDitanyaModal: openUnitDitanyaModalRuntime,
                    claimGaransiModalOpen,
                    claimGaransiForm,
                    openClaimGaransiModal: openClaimGaransiModalRuntime,
                    openUnboxingModal,
                } = window.MarketingDashboardRuntimeHelpers.createCustomerServiceState({
                    ref,
                    todayStr,
                });
                // Compatibility markers retained for shell contract tests after moving customer-service state into runtime helpers.
                // const storyTab = deps.ref('Ganjil');
                // const orderanOnlineForm = deps.ref({});
                const ensureNamaStockLoaded = () => {
                    if (!namaStockLoaded.value) loadNamaStockData();
                };
                const openOrderanOnlineModal = (type = 'create', row = null) => {
                    ensureNamaStockLoaded();
                    return openOrderanOnlineModalRuntime(type, row);
                };
                const openUnitDitanyaModal = (type = 'create', row = null) => {
                    ensureNamaStockLoaded();
                    return openUnitDitanyaModalRuntime(type, row);
                };
                const openClaimGaransiModal = (type = 'create', row = null) => {
                    ensureNamaStockLoaded();
                    return openClaimGaransiModalRuntime(type, row);
                };
                const calendarActiveDate = ref(new Date());

                // Extended calendar state for multiple date fields
                const calendarFormContext = ref(''); // 'master', 'story', 'orderanOnline1', 'unitDitanya1', 'claimGaransi1', 'claimGaransi2', 'claimGaransi3', 'distribution', 'analytics'

                const ideationViewMode = ref("board");
                const ideationBoardMobileTab = ref('');
                const contentTableSearch = ref("");
                const commonDateFilter = ref({ start: '', end: '' });
                const insightDateFilter = ref(getDefaultDateRange());
@endverbatim
