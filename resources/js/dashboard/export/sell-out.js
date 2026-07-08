import { getPrintHTML } from './print-core.js';
import { openPrintWindow } from './print-browser.js';

function esc(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

export function exportSellOutToPDF(deps) {
  const {
    rows,
    getSellOutProgress,
    formatNumber,
    formatCurrency,
    sellOutMonth,
    sellOutVendorFilter,
    showNotification,
    notifyError,
    jsonApi,
    getFriendlyErrorMessage,
    resolveAppUrl,
  } = deps;

  try {
    if (!rows.length) {
      showNotification('Tidak ada data untuk diekspor');
      return;
    }

    const today = new Date().toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });
    let totalBonusValue = 0;
    const grouped = {};

    rows.forEach((row) => {
      const progress = getSellOutProgress(row);
      if (progress.status === 'TIDAK DIPAKAI') {
        return;
      }

      totalBonusValue += Number(progress.bonusTotal || 0);
      const key = String(row.Brand || 'LAINNYA').toUpperCase();
      if (!grouped[key]) {
        grouped[key] = [];
      }

      grouped[key].push({ row, progress });
    });

    const bodyRows = Object.keys(grouped)
      .sort((left, right) => left.localeCompare(right))
      .map((brand) => {
        const rowsHtml = grouped[brand]
          .map((item, index) => {
            const { row, progress } = item;

            return `<tr>
                        <td>${index + 1}</td>
                        <td>${esc(row.Vendor || '-')}</td>
                        <td>${esc(row.Nama_Produk || '-')}</td>
                        <td style="text-align:right;">${esc(formatNumber(progress.qty || row.Realisasi_Unit || 0))}</td>
                        <td style="text-align:right;">${esc(formatNumber(row.Target_Unit || 0))}</td>
                        <td style="text-align:right;">${esc(formatCurrency(row.Bonus_Nominal || 0))}</td>
                        <td style="text-align:right;">${esc(formatCurrency(progress.bonusTotal || 0))}</td>
                        <td>${esc(progress.status || '-')}</td>
                        <td>${esc(row.Periode_Start || '-')} - ${esc(row.Periode_End || '-')}</td>
                    </tr>`;
          })
          .join('');

        return `<tr><td colspan="9" style="font-weight:700;background:#f5f5f4;">BRAND: ${esc(brand)}</td></tr>${rowsHtml}`;
      })
      .join('');

    const extraStyles = '<style>@page{size:A4 landscape;margin:10mm;}table{font-size:7pt;}th,td{padding:3px;}</style>';
    const html = getPrintHTML({
      title: 'TARGET VENDOR (SELL OUT)',
      subtitle: 'PURA PURA PONSEL - MARKETING',
      period: `Dicetak Pada: ${today} | Bulan: ${sellOutMonth || '-'} | Vendor: ${sellOutVendorFilter || 'Semua'}`,
      totalLabel: 'Total Bonus',
      totalValue: formatCurrency(totalBonusValue),
      headers: ['#', 'Vendor', 'Nama Produk', 'Terjual', 'Target', 'Bonus/Unit', 'Total Bonus', 'Status', 'Periode'],
      rows: bodyRows,
      extraStyles,
      showSignature: true,
    });

    openPrintWindow(html, 'Target Vendor Sell Out', {
      showNotification,
      notifyError,
      jsonApi,
      getFriendlyErrorMessage,
      resolveAppUrl,
    });
  } catch (err) {
    notifyError('Gagal export', err, 'File belum berhasil dibuat.');
  }
}
