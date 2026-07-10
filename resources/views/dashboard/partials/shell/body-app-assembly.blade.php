@include('dashboard.partials.shell.app-script-open')
@include('dashboard.partials.shell.app-script-date-helpers')
@include('dashboard.partials.shell.app-script-bootstrap-navigation')
@include('dashboard.partials.shell.app-script-auth-session')
@include('dashboard.partials.shell.app-script-domain-state')
@include('dashboard.partials.shell.app-script-protected-user-settings')
@include('dashboard.partials.shell.app-script-runner-factories')
@include('dashboard.partials.shell.app-script-settings-cluster')
@include('dashboard.partials.shell.app-script-nama-stock-actions')
@include('dashboard.partials.shell.app-script-meta-ig-analytics')
@include('dashboard.partials.shell.app-script-meta-ig-analytics-presentation')
@include('dashboard.partials.shell.app-script-profile-user-mutations')
@include('dashboard.partials.shell.app-script-reporting-and-budgeting')
@include('dashboard.partials.shell.app-script-ads-log-operations')
@include('dashboard.partials.shell.app-script-price-competitor-operations')
@include('dashboard.partials.shell.app-script-lpjk-operations')
@include('dashboard.partials.shell.app-script-asset-vendor-inventory-operations')
@include('dashboard.partials.shell.app-script-budgeting-operations')
@include('dashboard.partials.shell.app-script-search-select-and-options')
@include('dashboard.partials.shell.app-script-calendar-helpers')
@include('dashboard.partials.shell.app-script-summary-computed-cluster')
@include('dashboard.partials.shell.app-script-customer-service-crud')
@include('dashboard.partials.shell.app-script-bonus-talent-cluster')
@include('dashboard.partials.shell.app-script-cache-bootstrap-loaders')
@verbatim
                const getStaggerStyle = (index = 0, step = 32, maxDelay = 192) => {
                    const safeIndex = Number.isFinite(index) ? Math.max(0, index) : 0;
                    const safeStep = Number.isFinite(step) ? Math.max(0, step) : 32;
                    const safeMaxDelay = Number.isFinite(maxDelay) ? Math.max(0, maxDelay) : 192;
                    return { '--stagger-delay': `${Math.min(safeIndex * safeStep, safeMaxDelay)}ms` };
                };
@endverbatim
@include('dashboard.partials.shell.app-script-lifecycle-watchers')
@include('dashboard.partials.scripts.export-analytics-cluster')
@include('dashboard.partials.scripts.export-customer-service-pdfs')
@include('dashboard.partials.scripts.export-sales-and-promo-pdfs')
@include('dashboard.partials.scripts.export-reporting-bridge')
@include('dashboard.partials.scripts.export-ads-log-pdf')
@include('dashboard.partials.scripts.export-price-comparison-pdf')
@include('dashboard.partials.scripts.export-lpjk-detail-pdf')
@include('dashboard.partials.scripts.export-budget-pdf')
@include('dashboard.partials.shell.app-script-return-block')
@include('dashboard.partials.shell.app-script-close')
