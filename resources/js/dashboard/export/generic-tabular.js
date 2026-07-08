import { getPrintHTML } from './print-core.js';
import { openPrintWindow } from './print-browser.js';

function esc(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

export function exportGenericTabularPdf(deps) {
  const {
    rows,
    title,
    showNotification,
    notifyError,
    jsonApi,
    getFriendlyErrorMessage,
    resolveAppUrl,
  } = deps;

  if (!rows.length) {
    showNotification('Tidak ada data untuk diekspor');
    return;
  }

  const today = new Date().toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  });
  const headers = Object.keys(rows[0]).filter((key) => !key.startsWith('_'));
  const bodyRows = rows
    .map(
      (row, index) =>
        `<tr><td style="text-align:center;color:#94a3b8">${index + 1}</td>${headers.map((header) => `<td>${esc(row[header])}</td>`).join('')}</tr>`,
    )
    .join('');
  const extraStyles =
    '<style>@page{size:A4 landscape;margin:12mm 10mm;}table{font-size:8pt;}th,td{padding:5px 8px;}td{word-break:break-word;max-width:200px;}</style>';

  const html = getPrintHTML({
    title: title.toUpperCase(),
    subtitle: 'MARKETING DIVISION',
    period: `Dicetak Pada: ${today}`,
    headers: ['#', ...headers.map((header) => header.replace(/_/g, ' '))],
    rows: bodyRows,
    extraStyles,
    showSignature: true,
  });

  openPrintWindow(html, title, {
    showNotification,
    notifyError,
    jsonApi,
    getFriendlyErrorMessage,
    resolveAppUrl,
  });
}
