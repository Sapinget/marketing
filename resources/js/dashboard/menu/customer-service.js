export function createCustomerServiceState(deps) {
    const {
        ref,
        todayStr,
        unboxingStatusOptions,
        orderanStatusOptions,
        unitKondisiOptions,
        unitAvailableOptions,
        claimStatusOptions,
        namaStockLoaded,
        loadNamaStockData,
    } = deps;

    const storyTab = ref('Ganjil');
    const storyModalOpen = ref(false);
    const storyModalType = ref('create');
    const storyForm = ref({
        ID: null,
        Tanggal: '',
        Jam: '09:00',
        Story: '',
        Catatan: '',
        Link: '',
        is_genap: 'Ganjil',
        Status: '',
    });

    const unboxingData = ref([]);
    const unboxingSearch = ref('');
    const unboxingModalOpen = ref(false);
    const unboxingModalType = ref('create');
    const unboxingForm = ref({});

    const orderanOnlineData = ref([]);
    const orderanOnlineSearch = ref('');
    const unitDitanyaData = ref([]);
    const unitDitanyaSearch = ref('');
    const unitDitanyaAvailableFilter = ref('');
    const unitDitanyaSortBy = ref('TANGGAL');
    const unitDitanyaSortDesc = ref(true);
    const claimGaransiData = ref([]);
    const claimGaransiSearch = ref('');
    const claimGaransiLokasiFilter = ref('');
    const claimGaransiStatusFilter = ref('');
    const claimGaransiGaransiFilter = ref('');
    const csModalType = ref('create');
    const orderanOnlineModalOpen = ref(false);
    const orderanOnlineForm = ref({});
    const unitDitanyaModalOpen = ref(false);
    const unitDitanyaForm = ref({});
    const claimGaransiModalOpen = ref(false);
    const claimGaransiForm = ref({});

    const ensureNamaStockLoaded = () => {
        if (!namaStockLoaded?.value) loadNamaStockData?.();
    };

    const openOrderanOnlineModal = (type = 'create', row = null) => {
        csModalType.value = type;
        orderanOnlineForm.value = row ? { ...row } : {
            TANGGAL: todayStr(),
            ECOMMERCE: '',
            HANDLE: '',
            NAMA: '',
            HP: '',
            USERNAME: '',
            'NO PESANAN': '',
            PENGIRIMAN: '',
            'NO RESI': '',
            'TYPE UNIT': '',
            'IMEI/SN': '',
            'HARGA ONLINE': '',
            'NOMINAL CAIR': '',
            'ADMIN %': '',
            'NO NOTA': '',
            STATUS: orderanStatusOptions?.value?.[0] ?? '',
        };
        orderanOnlineModalOpen.value = true;
        ensureNamaStockLoaded();
    };

    const openUnitDitanyaModal = (type = 'create', row = null) => {
        csModalType.value = type;
        unitDitanyaForm.value = row ? { ...row } : {
            TANGGAL: todayStr(),
            KATEGORI: '',
            BRAND: '',
            SERI: '',
            RAM: '',
            INTERNAL: '',
            SIZE: '',
            WARNA: '',
            KONDISI: unitKondisiOptions?.value?.[0] ?? '',
            TIPE: '',
            DITANYA: 1,
            AVAILABLE: unitAvailableOptions?.value?.[0] ?? '',
        };
        unitDitanyaModalOpen.value = true;
        ensureNamaStockLoaded();
    };

    const openClaimGaransiModal = (type = 'create', row = null) => {
        csModalType.value = type;
        claimGaransiForm.value = row ? { ...row } : {
            NAMA_CUSTOMER: '',
            NO_SERVICE: '',
            NO_TRANSAKSI: '',
            TANGGAL_MASUK: todayStr(),
            TANGGAL_ESTIMASI: '',
            TANGGAL_DIAMBIL: '',
            WA_CUSTOMER: '',
            WA2_CUSTOMER: '',
            TIPE: '',
            IMEI: '',
            SERI: '',
            MODEL: '',
            HP_PINJAMAN: '',
            IMEI_PINJAMAN: '',
            LOKASI_KLAIM: '',
            STATUS: claimStatusOptions?.value?.[0] ?? '',
            GARANSI: '',
            KERUSAKAN: '',
            KETERANGAN: '',
        };
        claimGaransiModalOpen.value = true;
        ensureNamaStockLoaded();
    };

    const openUnboxingModal = (type = 'create', row = null) => {
        unboxingModalType.value = type;
        unboxingForm.value = row ? { ...row } : {
            Nama: '',
            Editor: '',
            Status: unboxingStatusOptions?.value?.[0] ?? '',
            Upload_Date: todayStr(),
            Link: '',
        };
        unboxingModalOpen.value = true;
    };

    return {
        storyTab,
        storyModalOpen,
        storyModalType,
        storyForm,
        unboxingData,
        unboxingSearch,
        unboxingModalOpen,
        unboxingModalType,
        unboxingForm,
        orderanOnlineData,
        orderanOnlineSearch,
        unitDitanyaData,
        unitDitanyaSearch,
        unitDitanyaAvailableFilter,
        unitDitanyaSortBy,
        unitDitanyaSortDesc,
        claimGaransiData,
        claimGaransiSearch,
        claimGaransiLokasiFilter,
        claimGaransiStatusFilter,
        claimGaransiGaransiFilter,
        csModalType,
        orderanOnlineModalOpen,
        orderanOnlineForm,
        openOrderanOnlineModal,
        unitDitanyaModalOpen,
        unitDitanyaForm,
        openUnitDitanyaModal,
        claimGaransiModalOpen,
        claimGaransiForm,
        openClaimGaransiModal,
        openUnboxingModal,
    };
}

export function createCustomerServiceCrud(deps) {
    const loadStoryData = () => new Promise((resolve) => {
        deps.ensureRunApi()
            .withSuccessHandler((data) => {
                deps.storyData.value = data || [];
                resolve();
            })
            .withFailureHandler(() => resolve())
            .getStorySchedule();
    });

    const loadUnboxingData = () => new Promise((resolve) => {
        deps.ensureRunApi()
            .withSuccessHandler((data) => {
                deps.unboxingData.value = Array.isArray(data) ? data : [];
                resolve();
            })
            .withFailureHandler(() => resolve())
            .getUnboxingData();
    });

    const loadOrderanOnlineData = () => new Promise((resolve) => {
        deps.ensureRunApi()
            .withSuccessHandler((data) => {
                deps.orderanOnlineData.value = Array.isArray(data) ? data : [];
                resolve();
            })
            .withFailureHandler(() => resolve())
            .getOrderanOnlineData();
    });

    const loadUnitDitanyaData = () => new Promise((resolve) => {
        deps.ensureRunApi()
            .withSuccessHandler((data) => {
                deps.unitDitanyaData.value = Array.isArray(data) ? data : [];
                resolve();
            })
            .withFailureHandler(() => resolve())
            .getUnitDitanyaData();
    });

    const loadClaimGaransiData = () => new Promise((resolve) => {
        deps.ensureRunApi()
            .withSuccessHandler((data) => {
                deps.claimGaransiData.value = Array.isArray(data) ? data : [];
                resolve();
            })
            .withFailureHandler(() => resolve())
            .getClaimGaransiData();
    });

    const loadKeepBarangData = () => new Promise((resolve) => {
        deps.ensureRunApi()
            .withSuccessHandler((data) => {
                deps.keepBarangData.value = Array.isArray(data) ? data : [];
                resolve();
            })
            .withFailureHandler(() => resolve())
            .getKeepBarangData();
    });

    const openCreateStoryModal = () => {
        deps.storyModalType.value = 'create';
        deps.storyForm.value = {
            ID: null,
            is_genap: deps.storyTab.value === 'Genap' ? 1 : 0,
            Tanggal: '',
            Jam: '09:00',
            Story: '',
            Catatan: '',
            Link: '',
            Status: '',
        };
        deps.storyModalOpen.value = true;
    };

    const openEditStoryModal = (item) => {
        deps.storyModalType.value = 'edit';
        deps.storyForm.value = { ...item };
        deps.storyModalOpen.value = true;
    };

    const saveStory = () => {
        if (!deps.storyForm.value.Story || !deps.storyForm.value.Jam) {
            deps.showNotification('Story dan Jam wajib diisi!');
            return;
        }

        deps.submitting.value = true;
        deps.ensureRunApi()
            .withSuccessHandler((response) => {
                deps.submitting.value = false;
                if (response.status === 'success') {
                    loadStoryData();
                    deps.storyModalOpen.value = false;
                    deps.showNotification(deps.storyModalType.value === 'create' ? 'Story berhasil ditambahkan' : 'Story berhasil diupdate');
                    return;
                }

                deps.handleError(new Error(response.message || 'Gagal menyimpan story'));
            })
            .withFailureHandler(deps.handleError)
            .saveStory(deps.storyForm.value);
    };

    const deleteStory = (id) => {
        deps.showConfirm('Hapus Story', 'Apakah Anda yakin ingin menghapus jadwal story ini?', () => {
            deps.ensureRunApi()
                .withSuccessHandler(() => {
                    loadStoryData();
                    deps.showNotification('Story berhasil dihapus');
                })
                .deleteStory(id);
        });
    };

    const saveUnboxing = () => {
        if (!deps.unboxingForm.value.Nama) {
            deps.showNotification('Nama wajib diisi');
            return;
        }

        deps.submitting.value = true;
        deps.ensureRunApi()
            .withSuccessHandler(() => {
                deps.submitting.value = false;
                deps.unboxingModalOpen.value = false;
                loadUnboxingData();
                deps.showNotification(deps.unboxingModalType.value === 'create' ? 'Unboxing berhasil ditambahkan' : 'Unboxing berhasil diupdate');
            })
            .withFailureHandler((err) => {
                deps.submitting.value = false;
                deps.handleError(err);
            })
            .saveUnboxing(deps.unboxingForm.value);
    };

    const deleteUnboxing = (id) => {
        deps.showConfirm('Hapus Unboxing?', 'Data yang dihapus tidak dapat dikembalikan.', () => {
            deps.ensureRunApi()
                .withSuccessHandler(() => {
                    loadUnboxingData();
                    deps.showNotification('Unboxing berhasil dihapus');
                })
                .deleteUnboxing(id);
        });
    };

    const saveOrderanOnline = () => {
        if (deps.submitting.value) return;
        if (!deps.orderanOnlineForm.value.NAMA || !deps.orderanOnlineForm.value['TYPE UNIT']) {
            deps.showNotification('Nama customer dan type unit wajib diisi');
            return;
        }

        deps.submitting.value = true;
        deps.ensureRunApi()
            .withSuccessHandler(() => {
                deps.submitting.value = false;
                deps.orderanOnlineModalOpen.value = false;
                loadOrderanOnlineData();
                deps.showNotification('Orderan berhasil disimpan');
            })
            .withFailureHandler((err) => {
                deps.submitting.value = false;
                deps.handleError(err);
            })
            .saveOrderanOnline(deps.orderanOnlineForm.value);
    };

    const deleteOrderanOnline = (id) => {
        deps.showConfirm('Hapus Orderan?', 'Data yang dihapus tidak dapat dikembalikan.', () => {
            deps.ensureRunApi()
                .withSuccessHandler(() => {
                    loadOrderanOnlineData();
                    deps.showNotification('Orderan berhasil dihapus');
                })
                .deleteOrderanOnline(id);
        });
    };

    const saveUnitDitanya = () => {
        if (deps.submitting.value) return;
        if (!deps.unitDitanyaForm.value.KATEGORI || !deps.unitDitanyaForm.value.BRAND || !deps.unitDitanyaForm.value.SERI) {
            deps.showNotification('Kategori, brand, dan seri wajib diisi');
            return;
        }

        deps.submitting.value = true;
        deps.ensureRunApi()
            .withSuccessHandler(() => {
                deps.submitting.value = false;
                deps.unitDitanyaModalOpen.value = false;
                loadUnitDitanyaData();
                deps.showNotification('Unit berhasil disimpan');
            })
            .withFailureHandler((err) => {
                deps.submitting.value = false;
                deps.handleError(err);
            })
            .saveUnitDitanya(deps.unitDitanyaForm.value);
    };

    const deleteUnitDitanya = (id) => {
        deps.showConfirm('Hapus Unit?', 'Data yang dihapus tidak dapat dikembalikan.', () => {
            deps.ensureRunApi()
                .withSuccessHandler(() => {
                    loadUnitDitanyaData();
                    deps.showNotification('Unit berhasil dihapus');
                })
                .deleteUnitDitanya(id);
        });
    };

    const saveClaimGaransi = () => {
        if (deps.submitting.value) return;
        if (!deps.claimGaransiForm.value.NAMA_CUSTOMER || !deps.claimGaransiForm.value.TIPE) {
            deps.showNotification('Nama customer dan tipe wajib diisi');
            return;
        }

        deps.submitting.value = true;
        deps.ensureRunApi()
            .withSuccessHandler(() => {
                deps.submitting.value = false;
                deps.claimGaransiModalOpen.value = false;
                loadClaimGaransiData();
                deps.showNotification('Claim berhasil disimpan');
            })
            .withFailureHandler((err) => {
                deps.submitting.value = false;
                deps.handleError(err);
            })
            .saveClaimGaransi(deps.claimGaransiForm.value);
    };

    const deleteClaimGaransi = (id) => {
        deps.showConfirm('Hapus Claim?', 'Data yang dihapus tidak dapat dikembalikan.', () => {
            deps.ensureRunApi()
                .withSuccessHandler(() => {
                    loadClaimGaransiData();
                    deps.showNotification('Claim berhasil dihapus');
                })
                .deleteClaimGaransi(id);
        });
    };

    const saveKeepBarang = () => {
        if (deps.submitting.value) return;
        const form = { ...deps.keepBarangForm.value };
        if (!form.NAMA || !form.NOMOR_HP || !form.TYPE_HP) {
            deps.showNotification('Nama, nomor HP, dan Type HP wajib diisi');
            return;
        }

        deps.submitting.value = true;
        deps.computeKeepBarangDerived(form);
        deps.ensureRunApi()
            .withSuccessHandler(() => {
                deps.submitting.value = false;
                deps.keepBarangModalOpen.value = false;
                loadKeepBarangData();
                deps.showNotification('Data berhasil disimpan');
            })
            .withFailureHandler((err) => {
                deps.submitting.value = false;
                deps.handleError(err);
            })
            .saveKeepBarang(form);
    };

    const deleteKeepBarang = (id) => {
        deps.showConfirm('Hapus Data?', 'Data yang dihapus tidak dapat dikembalikan.', () => {
            deps.ensureRunApi()
                .withSuccessHandler(() => {
                    loadKeepBarangData();
                    deps.showNotification('Data berhasil dihapus');
                })
                .deleteKeepBarang(id);
        });
    };

    return {
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
    };
}
