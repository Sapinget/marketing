@verbatim
                const inferNotificationType = (message = '') => {
                    const normalized = String(message || '').toLowerCase();

                    if (normalized.includes('gagal') || normalized.includes('error') || normalized.includes('wajib') || normalized.includes('tidak bisa')) {
                        return 'error';
                    }

                    if (normalized.includes('izinkan') || normalized.includes('dibatasi') || normalized.includes('pop-up') || normalized.includes('popup')) {
                        return 'warning';
                    }

                    return 'success';
                };

                const showNotification = (message, type = null) => {
                    const resolvedType = type || inferNotificationType(message);
                    const icon = resolvedType === 'error'
                        ? 'fa-circle-xmark'
                        : resolvedType === 'warning'
                            ? 'fa-triangle-exclamation'
                            : 'fa-circle-check';

                    notification.value = {
                        open: true,
                        message,
                        type: resolvedType,
                        icon,
                    };

                    setTimeout(() => {
                        if (notification.value.message === message) {
                            notification.value = { open: false, message: '', type: 'success', icon: 'fa-circle-check' };
                        }
                    }, 3500);
                };

                const getFriendlyErrorMessage = (error, fallback = 'Terjadi kendala saat memproses permintaan.') => {
                    const raw = typeof error === 'string'
                        ? error
                        : (error && error.message ? String(error.message) : String(error || ''));
                    const normalized = raw.replace(/^Error:\s*/i, '').trim();

                    if (!normalized) return fallback;
                    if (/^HTTP\s*\d+/i.test(normalized)) return 'Server sedang bermasalah. Coba lagi beberapa saat.';
                    if (/failed to fetch|networkerror|load failed|network request failed/i.test(normalized)) {
                        return 'Koneksi ke server terputus. Periksa jaringan lalu coba lagi.';
                    }

                    return normalized;
                };

                const notifyError = (prefix, error, fallback) => {
                    const message = getFriendlyErrorMessage(error, fallback);
                    showNotification(prefix ? `${prefix}: ${message}` : message, 'error');
                };
@endverbatim
