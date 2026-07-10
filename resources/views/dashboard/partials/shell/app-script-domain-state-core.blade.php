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
                    confirmModal.value = { open: true, title, message, onConfirm, type };
                };

                // Master Plan Data
                const masterPlanData = ref([]);
                const analyticsData = ref([]);
                const distributionData = ref([]);
                const storyData = ref([]);
                const calendarEventsData = ref([]);

                const unboxingData = ref([]);
                const unboxingSearch = ref('');
                const unboxingModalOpen = ref(false);
                const unboxingModalType = ref('create');
                const unboxingForm = ref({});

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
                const orderanOnlineData = ref([]);
                const orderanOnlineSearch = ref('');
                const orderanOnlineDateRange = ref(getDefaultDateRange());

                const unitDitanyaData = ref([]);
                const unitDitanyaSearch = ref('');
                const unitDitanyaDateRange = ref(getDefaultDateRange());
                const unitDitanyaAvailableFilter = ref('');
                const unitDitanyaSortBy = ref('TANGGAL');
                const unitDitanyaSortDesc = ref(true);

                const claimGaransiData = ref([]);
                const claimGaransiSearch = ref('');
                const claimGaransiLokasiFilter = ref('');
                const claimGaransiStatusFilter = ref('');
                const claimGaransiGaransiFilter = ref('');

                // CS Modals
                const csModalType = ref('create');

                const orderanOnlineModalOpen = ref(false);
                const orderanOnlineForm = ref({});
                const openOrderanOnlineModal = (type = 'create', row = null) => {
                    csModalType.value = type;
                    orderanOnlineForm.value = row ? { ...row } : { TANGGAL: todayStr(), ECOMMERCE: '', HANDLE: '', NAMA: '', HP: '', USERNAME: '', 'NO PESANAN': '', PENGIRIMAN: '', 'NO RESI': '', 'TYPE UNIT': '', 'IMEI/SN': '', 'HARGA ONLINE': '', 'NOMINAL CAIR': '', 'ADMIN %': '', 'NO NOTA': '', STATUS: orderanStatusOptions.value[0] || '' };
                    orderanOnlineModalOpen.value = true;
                    if (!namaStockLoaded.value) loadNamaStockData();
                };

                const unitDitanyaModalOpen = ref(false);
                const unitDitanyaForm = ref({});
                const openUnitDitanyaModal = (type = 'create', row = null) => {
                    csModalType.value = type;
                    unitDitanyaForm.value = row ? { ...row } : { TANGGAL: todayStr(), KATEGORI: '', BRAND: '', SERI: '', RAM: '', INTERNAL: '', SIZE: '', WARNA: '', KONDISI: unitKondisiOptions.value[0] || '', TIPE: '', DITANYA: 1, AVAILABLE: unitAvailableOptions.value[0] || '' };
                    unitDitanyaModalOpen.value = true;
                    if (!namaStockLoaded.value) loadNamaStockData();
                };

                const claimGaransiModalOpen = ref(false);
                const claimGaransiForm = ref({});
                const openClaimGaransiModal = (type = 'create', row = null) => {
                    csModalType.value = type;
                    claimGaransiForm.value = row ? { ...row } : { NAMA_CUSTOMER: '', NO_SERVICE: '', NO_TRANSAKSI: '', TANGGAL_MASUK: todayStr(), TANGGAL_ESTIMASI: '', TANGGAL_DIAMBIL: '', WA_CUSTOMER: '', WA2_CUSTOMER: '', TIPE: '', IMEI: '', SERI: '', MODEL: '', HP_PINJAMAN: '', IMEI_PINJAMAN: '', LOKASI_KLAIM: '', STATUS: claimStatusOptions.value[0] || '', GARANSI: '', KERUSAKAN: '', KETERANGAN: '' };
                    claimGaransiModalOpen.value = true;
                    if (!namaStockLoaded.value) loadNamaStockData();
                };

                const openUnboxingModal = (type = 'create', row = null) => {
                    unboxingModalType.value = type;
                    unboxingForm.value = row ? { ...row } : { Nama: '', Editor: '', Status: unboxingStatusOptions.value[0] || '', Upload_Date: todayStr(), Link: '' };
                    unboxingModalOpen.value = true;
                };
                const calendarActiveDate = ref(new Date());

                // Extended calendar state for multiple date fields
                const calendarFormContext = ref(''); // 'master', 'story', 'orderanOnline1', 'unitDitanya1', 'claimGaransi1', 'claimGaransi2', 'claimGaransi3', 'distribution', 'analytics'
                const calendarFieldName = ref(''); // field name to update

                const ideationViewMode = ref("board");
                const ideationBoardMobileTab = ref('');
                const contentTableSearch = ref("");
                const commonDateFilter = ref({ start: '', end: '' });
                const insightDateFilter = ref(getDefaultDateRange());
@endverbatim
