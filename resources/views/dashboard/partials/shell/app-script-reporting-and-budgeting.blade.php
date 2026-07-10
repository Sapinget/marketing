@verbatim
                const saveBonusReportingConfig = window.MarketingDashboardRuntimeHelpers.createReportingOperations({
                    bonusConfig,
                    showBonusSettings,
                    ensureRunApi,
                    showNotification,
                    notifyError,
                }).saveBonusConfig;
                const saveBonusConfig = () => {
                    try {
                        return saveBonusReportingConfig();
                    } catch (error) {
                        notifyError('Gagal menyimpan konfigurasi bonus', error, 'Konfigurasi bonus belum tersimpan ke server.');
                        throw error;
                    }
                };
@endverbatim
