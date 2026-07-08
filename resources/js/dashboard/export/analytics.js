/**
 * analytics.js - Analytics Export Module
 *
 * Extracted from public/marketing-dashboard.html (Fase 2b - Pilot)
 * Handles PDF export for the analytics tab.
 *
 * @module analytics
 */

import { getPrintHTML } from './print-core.js';
import { openPrintWindow } from './print-browser.js';

function esc(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

export function exportAnalyticsToPDF(deps) {
  const {
    rows,
    dateFilter,
    formatShortDate,
    formatNumber,
    calculateScore,
    getVelocity,
    showNotification,
    notifyError,
    jsonApi,
    getFriendlyErrorMessage,
    resolveAppUrl,
  } = deps;

  try {
    if (!rows.length) {
      showNotification('Tidak ada data analytics untuk dicetak');
      return;
    }

    const today = new Date().toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });
    const periodStart = dateFilter.start
      ? formatShortDate(dateFilter.start)
      : 'Semua Waktu';
    const periodEnd = dateFilter.end
      ? formatShortDate(dateFilter.end)
      : 'Sekarang';
    const totalViews = rows.reduce(
      (sum, row) => sum + (Number(row.Views) || 0),
      0,
    );
    const totalLikes = rows.reduce(
      (sum, row) => sum + (Number(row.Likes) || 0),
      0,
    );

    const bodyRows = rows
      .map(
        (row, idx) => `
            <tr>
                <td>${idx + 1}</td>
                <td>${esc(row.Judul || '-')}</td>
                <td>${esc(row.Platform || '-')}</td>
                <td>${esc(row.Tanggal_Publish ? formatShortDate(row.Tanggal_Publish) : '-')}</td>
                <td class="text-right">${esc(formatNumber(row.Views || 0))}</td>
                <td class="text-right">${esc(formatNumber(row.Likes || 0))}</td>
                <td class="text-right">${esc(formatNumber(row.Comments || 0))}</td>
                <td class="text-right">${esc(formatNumber(row.Shares || 0))}</td>
                <td class="text-right">${esc(formatNumber(calculateScore(row) || 0))}</td>
                <td>${esc((getVelocity(row) || {}).label || '-')}</td>
            </tr>`,
      )
      .join('');

    const extraStyles =
      '<style>@page{size:A4 landscape;margin:12mm;}table{font-size:8pt;}th,td{padding:5px 6px;}</style>';
    const extraHtml = `<table style="width:auto;border-collapse:collapse;margin:8px 0 10px;border:none;">
            <tr>
                <td style="border:1px solid #d6d3d1;background:#fafaf9;padding:8px 12px;font-size:8pt;white-space:nowrap;border-right:none;">Total Data<strong style="display:block;font-size:10pt;margin-top:2px;">${esc(rows.length)}</strong></td>
                <td style="border:1px solid #d6d3d1;background:#fafaf9;padding:8px 12px;font-size:8pt;white-space:nowrap;border-right:none;">Total Views<strong style="display:block;font-size:10pt;margin-top:2px;">${esc(formatNumber(totalViews))}</strong></td>
                <td style="border:1px solid #d6d3d1;background:#fafaf9;padding:8px 12px;font-size:8pt;white-space:nowrap;">Total Likes<strong style="display:block;font-size:10pt;margin-top:2px;">${esc(formatNumber(totalLikes))}</strong></td>
            </tr>
        </table>`;

    const html = getPrintHTML({
      title: 'ANALYTICS PERFORMANCE REPORT',
      subtitle: 'MARKETING DIVISION',
      period: `Dicetak Pada: ${today} | Periode: ${periodStart} - ${periodEnd}`,
      headers: [
        'No',
        'Judul',
        'Platform',
        'Tanggal Upload',
        'Views',
        'Likes',
        'Comments',
        'Shares',
        'Skor',
        'Velocity',
      ],
      rows: bodyRows,
      extraStyles,
      extraHtml,
      showSignature: true,
    });

    openPrintWindow(html, 'Analytics Performance', {
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
