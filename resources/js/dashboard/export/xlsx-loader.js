const DASHBOARD_XLSX_VENDOR_PATH = '/vendor/dashboard/xlsx/xlsx.full.min.js';

function buildDashboardAssetUrl(path) {
  return new URL(path, window.location.origin).toString();
}

export function ensureXLSX() {
  if (window.XLSX) {
    return Promise.resolve(window.XLSX);
  }

  const existingScript = document.querySelector(
    'script[data-dashboard-vendor="xlsx"]',
  );

  if (existingScript) {
    return new Promise((resolve, reject) => {
      existingScript.addEventListener('load', () => resolve(window.XLSX), {
        once: true,
      });
      existingScript.addEventListener(
        'error',
        () => reject(new Error('Gagal memuat library Excel')),
        { once: true },
      );
    });
  }

  return new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = buildDashboardAssetUrl(DASHBOARD_XLSX_VENDOR_PATH);
    script.dataset.dashboardVendor = 'xlsx';
    script.onload = () => resolve(window.XLSX);
    script.onerror = () => reject(new Error('Gagal memuat library Excel'));
    document.head.appendChild(script);
  });
}
