import { getPrintHTML } from './print-core.js';
import { openPrintWindow } from './print-browser.js';

function esc(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

function escNl(value) {
  return esc(value).replace(/\n/g, '<br>');
}

export function exportPromoToPDF(deps) {
  const {
    rows,
    kategoriPromoOptions,
    showNotification,
    notifyError,
    jsonApi,
    getFriendlyErrorMessage,
    resolveAppUrl,
  } = deps;

  try {
    if (!rows.length) {
      showNotification('Tidak ada data promo untuk dicetak');
      return;
    }

    const today = new Date().toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });
    const order = kategoriPromoOptions;
    const groups = new Map();

    order.forEach((kategori) => groups.set(kategori, []));
    rows.forEach((row) => {
      const key = row.Kategori || 'Lainnya';
      if (!groups.has(key)) {
        groups.set(key, []);
      }

      groups.get(key).push(row);
    });

    const palette = {
      Brand: { bg: '#ec4899', fg: '#fff' },
      Vendor: { bg: '#8b5cf6', fg: '#fff' },
      Internal: { bg: '#10b981', fg: '#fff' },
      Platform: { bg: '#3b82f6', fg: '#fff' },
      Bundle: { bg: '#f59e0b', fg: '#fff' },
      Event: { bg: '#ef4444', fg: '#fff' },
      Lainnya: { bg: '#64748b', fg: '#fff' },
    };

    let bodyHtml = '';

    order
      .concat([...groups.keys()].filter((kategori) => !order.includes(kategori)))
      .forEach((kategori) => {
        const items = groups.get(kategori) || [];
        if (!items.length) {
          return;
        }

        const colors = palette[kategori] || palette.Lainnya;
        bodyHtml += `<tr class="cat-header"><td colspan="7" style="background:${colors.bg};color:${colors.fg};font-weight:700;letter-spacing:.04em">${esc(kategori)} (${items.length})</td></tr>`;

        items.forEach((row, index) => {
          bodyHtml += `<tr>
                    <td style="text-align:center;color:#94a3b8">${index + 1}</td>
                    <td style="font-weight:700;color:#0f172a">${esc(row.Program) || '-'}</td>
                    <td style="font-size:10px;color:#64748b">${esc(row.Warna) || '-'}</td>
                    <td style="font-size:10px;color:#334155">${escNl(row.Benefit) || '-'}</td>
                    <td style="font-size:10px;color:#64748b">${escNl(row.Rules) || '-'}</td>
                    <td style="text-align:right;font-weight:700;color:${colors.bg};font-size:11px">${row.Harga ? Number(row.Harga).toLocaleString('id-ID') : '-'}</td>
                    <td style="font-size:10px;color:#64748b;font-style:italic;text-align:center">${esc(row.Periode) || '-'}</td>
                </tr>`;
        });
      });

    const totalPromo = rows.length;
    const categoryCount = order.filter((kategori) => (groups.get(kategori) || []).length).length;
    const extraStyles = `<style>
            @page { size: A4 landscape; margin: 16mm 14mm; }
            body { font-size: 11px; color: #0f172a; }
            table { font-size: 9pt; }
            thead tr { background: #0f172a; }
            thead th { color: white; padding: 8px 10px; font-size: 9px; letter-spacing: .07em; text-transform: uppercase; }
            thead th:first-child { width: 30px; text-align: center; }
            thead th:nth-child(6) { text-align: right; }
            thead th:nth-child(7) { text-align: center; }
            tbody td { padding: 6px 10px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
            .promo-category-row td { color: white; font-weight: 800; padding: 7px 10px; font-size: 10px; letter-spacing: .08em; text-transform: uppercase; }
        </style>`;
    const extraHtml = `<table style="width:auto;border-collapse:collapse;margin:0 0 14px;border:none;float:right;">
            <tr>
                <td style="background:#f8fafc;border:1px solid #e2e8f0;padding:8px 14px;text-align:center;white-space:nowrap;border-right:none;">
                    <div style="font-size:16px;font-weight:800;color:#f97316;">${totalPromo}</div>
                    <div style="font-size:8px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-top:2px;">Total Promo</div>
                </td>
                <td style="background:#f8fafc;border:1px solid #e2e8f0;padding:8px 14px;text-align:center;white-space:nowrap;">
                    <div style="font-size:16px;font-weight:800;color:#f97316;">${categoryCount}</div>
                    <div style="font-size:8px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-top:2px;">Kategori</div>
                </td>
            </tr>
        </table><div style="clear:both"></div>`;

    const html = getPrintHTML({
      title: 'PROGRAM PROMO',
      subtitle: 'MARKETING DIVISION',
      period: `Dicetak Pada: ${today}`,
      headers: ['#', 'Program', 'Varian / Warna', 'Benefit', 'Rules', 'Harga', 'Periode'],
      rows: bodyHtml.replace(/<tr class="cat-header"/g, '<tr class="promo-category-row"'),
      extraStyles,
      extraHtml,
      showSignature: false,
    });

    openPrintWindow(html, 'Program Promo', {
      showNotification,
      notifyError,
      jsonApi,
      getFriendlyErrorMessage,
      resolveAppUrl,
    });
  } catch (err) {
    notifyError('Gagal export PDF', err, 'Dokumen PDF belum berhasil dibuat.');
  }
}
