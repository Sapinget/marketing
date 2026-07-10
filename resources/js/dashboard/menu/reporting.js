export function createReportingOperations(deps) {
    const {
        bonusConfig,
        showBonusSettings,
        ensureRunApi,
        showNotification,
        notifyError,
    } = deps;

    const saveBonusConfig = () => {
        const cfg = JSON.parse(JSON.stringify(bonusConfig.value));
        ensureRunApi()
            .withSuccessHandler(() => {
                localStorage.setItem('ppp_bonusConfig', JSON.stringify(cfg));
                showBonusSettings.value = false;
                showNotification('Konfigurasi bonus disimpan');
            })
            .withFailureHandler((error) => {
                notifyError('Gagal menyimpan konfigurasi bonus', error, 'Konfigurasi bonus belum tersimpan ke server.');
            })
            .saveBonusConfig(cfg);
    };

    return { saveBonusConfig };
}
