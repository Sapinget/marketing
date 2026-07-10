// Aliases for Vite print-core.js (loaded via @@vite in head)
const getThemeVarsCSS_ = window.getThemeVarsCSS;
const getPrintBaseStyles_ = window.getPrintBaseStyles;
const getPrintOrgHeaderHTML_ = window.getPrintOrgHeaderHTML;
const getPrintHTML_ = window.getPrintHTML;
const _printNoHeaderCss = window._printNoHeaderCss;
const getPrintAutoBootstrapScript_ = window.getPrintAutoBootstrapScript;
const buildStandalonePrintHtml_ = window.buildStandalonePrintHtml;
const waitForPrintAssets_ = window.waitForPrintAssets;
const submitBrowserPrintJob_ = window.submitBrowserPrintJob;

// Vue compatibility adapter: the print module remains the source of truth,
// while this inline layer only injects closure-scoped dependencies.
const openPrintWindow_ = (html, reportName) => window.openPrintWindow(html, reportName, {
    buildStandalonePrintHtmlFn: buildStandalonePrintHtml_,
    getFriendlyErrorMessage,
    jsonApi,
    notifyError,
    resolveAppUrl,
    showNotification,
});
const openPreviewPrintWindow_ = (html, reportName) => {
    const printDocumentHtml = buildStandalonePrintHtml_(html);
    return window.openPrintWindow(printDocumentHtml, reportName, {
        buildStandalonePrintHtmlFn: (preparedHtml) => preparedHtml,
        getFriendlyErrorMessage,
        jsonApi,
        notifyError,
        resolveAppUrl,
        showNotification,
    });
};
