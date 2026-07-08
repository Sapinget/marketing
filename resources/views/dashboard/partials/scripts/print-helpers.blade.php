// Aliases for Vite print-core.js (loaded via @@vite in head)
const getThemeVarsCSS_ = window.getThemeVarsCSS;
const getPrintBaseStyles_ = window.getPrintBaseStyles;
const getPrintOrgHeaderHTML_ = window.getPrintOrgHeaderHTML;
const getPrintHTML_ = window.getPrintHTML;
const _printNoHeaderCss = window._printNoHeaderCss;
const getPrintAutoBootstrapScript_ = window.getPrintAutoBootstrapScript;
const buildStandalonePrintHtml_ = window.buildStandalonePrintHtml;
const waitForPrintAssets_ = window.waitForPrintAssets;

// submitBrowserPrintJob_ and openPrintWindow_ stay inline
// because they depend on Vue closure scope (jsonApi, showNotification, etc.)
const submitBrowserPrintJob_ = (printHtml) => {
    return jsonApi('/print-job', {
        method: 'POST',
        body: JSON.stringify({ html: printHtml }),
    });
};
const openPrintWindow_ = (html, reportName) => {
    const printDocumentHtml = buildStandalonePrintHtml_(html);
    const autoPrintHtml = buildStandalonePrintHtml_(html, { autoPrint: true });
    const pw = window.open('', '_blank', 'width=1200,height=1000');
    if (!pw || pw.closed) { showNotification('Izinkan popup untuk print PDF'); return; }
    const _pwStatus = (msg) => { try { if (!pw.closed) { const p = pw.document.querySelector('p'); if (p) p.textContent = msg; } } catch(e) {} };
    try {
        pw.document.open();
        pw.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0"><p style="color:#64748b;font-size:14px">[1/2] Menyiapkan dokumen print...</p></body></html>');
        pw.document.close();
    } catch (e) { }
    submitBrowserPrintJob_(autoPrintHtml)
        .then((result) => {
            const token = String((result && result.token) || '').trim();
            if (!token) {
                throw new Error('Backend tidak mengembalikan token print.');
            }
            const backendUrl = new URL(resolveAppUrl('/print-job'), window.location.origin).origin.replace(/\/+$/, '');
            _pwStatus('[2/2] Dokumen siap - membuka print...');
            pw.location.href = `${backendUrl}/print-job/${encodeURIComponent(token)}`;
        })
        .catch((err) => {
            _pwStatus(`[error] ${getFriendlyErrorMessage(err, 'Dokumen print harus dibuka dari backend print-job.')}`);
            notifyError('Print gagal', err, 'Dokumen print harus dibuka dari backend print-job.');
        });
};
