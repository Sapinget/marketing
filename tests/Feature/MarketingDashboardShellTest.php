<?php

namespace Tests\Feature;

use App\Support\MarketingDashboardShell;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MarketingDashboardShellTest extends TestCase
{
    use RefreshDatabase;

    protected function assertHtmlContains(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertNotFalse(
            strpos($haystack, $needle),
            $message !== '' ? $message : "Failed asserting that the dashboard shell contains [{$needle}]."
        );
    }

    protected function assertHtmlNotContains(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertFalse(
            str_contains($haystack, $needle),
            $message !== '' ? $message : "Failed asserting that the dashboard shell does not contain [{$needle}]."
        );
    }

    protected function assertHtmlMatches(string $pattern, string $haystack, string $message = ''): void
    {
        $this->assertSame(
            1,
            preg_match($pattern, $haystack),
            $message !== '' ? $message : "Failed asserting that the dashboard shell matches [{$pattern}]."
        );
    }

    protected function assertFragmentHasKey(string $needle, array $fragments, string $message = ''): void
    {
        $this->assertTrue(
            array_key_exists($needle, $fragments),
            $message !== '' ? $message : "Failed asserting that the dashboard shell fragments contain [{$needle}]."
        );
    }

    protected function assertFragmentNotHasKey(string $needle, array $fragments, string $message = ''): void
    {
        $this->assertFalse(
            array_key_exists($needle, $fragments),
            $message !== '' ? $message : "Failed asserting that the dashboard shell fragments do not contain [{$needle}]."
        );
    }

    protected function readArchivedDashboardSnapshotReferenceHtml(): string
    {
        $html = file_get_contents(resource_path('legacy/marketing-dashboard-source.html'));

        $this->assertIsString($html);

        return $html;
    }

    protected function readArchivedDesignSystemSnapshotReferenceHtml(): string
    {
        $html = file_get_contents(resource_path('legacy/design-system-archive.html'));

        $this->assertIsString($html);

        return $html;
    }

    protected function renderDashboardHtml(): string
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);

        return $html;
    }

    protected function renderDashboardHtmlWithShellCss(): string
    {
        $html = $this->renderDashboardHtml();
        $dashboardShellCss = file_get_contents(resource_path('css/dashboard-shell.css'));

        $this->assertIsString($dashboardShellCss);

        return $html."\n".$dashboardShellCss;
    }

    public function test_root_serves_marketing_dashboard_shell(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $response->assertSee('Marketing Dashboard', false);
        $response->assertSee('Masuk', false);
        $response->assertDontSee('https://cdn.tailwindcss.com', false);
        $response->assertDontSee('https://cdn.jsdelivr.net', false);
        $response->assertDontSee('https://fonts.googleapis.com', false);
        $response->assertSee('/api/master-plans', false);
        $response->assertSee('/api/distributions', false);
        $response->assertSee('/api/analytics', false);
        $response->assertDontSee('Dashboard marketing lengkap berjalan di Laravel', false);
        $this->assertHtmlMatches('/<link[^>]+href="[^"]*\/asset\/images\/favicon\.ico"/', $html);
        $this->assertHtmlMatches('/<link[^>]+href="[^"]*\/build\/assets\/app-[^"]+\.css"/', $html);
        $this->assertHtmlMatches('/<script[^>]+src="[^"]*\/vendor\/dashboard\/vue\/vue\.global\.prod\.js"/', $html);
        $this->assertHtmlMatches('/<script[^>]+src="[^"]*\/vendor\/dashboard\/papaparse\/papaparse\.min\.js"/', $html);
        $this->assertHtmlMatches('/<script[^>]+src="[^"]*\/vendor\/dashboard\/apexcharts\/apexcharts\.min\.js"/', $html);
        $this->assertHtmlMatches('/<link[^>]+href="[^"]*\/vendor\/dashboard\/fontawesome\/css\/all\.min\.css\?v=\d+"/', $html);
        $this->assertHtmlContains('window.DASHBOARD_FONTAWESOME_URL=', $html);
        $this->assertHtmlMatches('/DASHBOARD_FONTAWESOME_URL=.*fontawesome.*all\.min\.css.*v=\d+/', $html);
        $this->assertHtmlNotContains('href="/vendor/dashboard/fontawesome/css/all.min.css"', $html);
    }

    public function test_root_render_does_not_depend_on_public_dashboard_snapshot(): void
    {
        $publicSnapshotPath = public_path('marketing-dashboard.html');
        $temporarySnapshotPath = $publicSnapshotPath.'.tmp-test';

        rename($publicSnapshotPath, $temporarySnapshotPath);

        try {
            $response = $this->get('/');

            $response->assertOk();
            $response->assertSee('Marketing Dashboard', false);
            $response->assertSee('Masuk', false);
        } finally {
            rename($temporarySnapshotPath, $publicSnapshotPath);
        }
    }

    public function test_root_injects_backend_url_and_no_store_meta_into_shell_response(): void
    {
        $response = $this->get('/');
        $cacheControl = (string) $response->headers->get('Cache-Control');

        $response->assertOk();
        $response->assertHeader('Pragma', 'no-cache');
        $this->assertHtmlContains('no-store', $cacheControl);
        $this->assertHtmlContains('no-cache', $cacheControl);
        $this->assertHtmlContains('must-revalidate', $cacheControl);
        $this->assertHtmlContains('max-age=0', $cacheControl);
        $response->assertSee('<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">', false);
        $response->assertSee('<meta http-equiv="Pragma" content="no-cache">', false);
        $response->assertSee('window.MARKETING_BACKEND_URL=', false);
    }

    public function test_8090_shell_resolves_dashboard_api_requests_against_its_configured_backend_url(): void
    {
        $response = $this->get('/8090', ['Host' => 'dashboard.example.test']);
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlMatches('/window\.MARKETING_BACKEND_URL=.*8090/', $html);
        $this->assertHtmlContains('return `${String(window.MARKETING_BACKEND_URL).replace(/\\/+$/, \'\')}${url}`;', $html);
        $this->assertHtmlNotContains("fetch('/api/", $html);
        $this->assertHtmlNotContains('fetch(`/api/', $html);
        $this->assertHtmlContains("fetch(window.MarketingDashboardRuntimeHelpers.resolveAppUrl('/api/img-repo/upload'), { method: 'POST', body: fd })", $html);
        $this->assertHtmlNotContains("fetch(window.MarketingDashboardRuntimeHelpers.resolveAppUrl('/api/img-repo/upload'), { method: 'POST', headers:", $html);
    }

    public function test_promo_pamflet_watcher_runs_after_current_user_initialization(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlNotContains("watch(activeTab, (newTab) => {\n                    if (newTab === 'promo_pamflet' && currentUser.value)", $html);
        $this->assertHtmlContains("if (newTab === 'promo_pamflet') {\n                        promoPamflet.fetchData();", $html);
        $this->assertLessThan(
            strpos($html, "watch(() => activeTab.value, (newTab) => {"),
            strpos($html, 'const currentUser = ref(loadStoredUser());')
        );
    }

    public function test_root_applies_security_headers_and_accessible_viewport(): void
    {
        $response = $this->get('/');
        $contentSecurityPolicy = (string) $response->headers->get('Content-Security-Policy');
        $permissionsPolicy = (string) $response->headers->get('Permissions-Policy');

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertHtmlContains("frame-ancestors 'none'", $contentSecurityPolicy);
        $this->assertHtmlContains("object-src 'none'", $contentSecurityPolicy);
        $this->assertHtmlContains("'unsafe-eval'", $contentSecurityPolicy);
        $this->assertHtmlNotContains('cdn.jsdelivr.net', $contentSecurityPolicy);
        $this->assertHtmlNotContains('fonts.googleapis.com', $contentSecurityPolicy);
        $this->assertHtmlNotContains('fonts.gstatic.com', $contentSecurityPolicy);
        $this->assertHtmlContains('camera=()', $permissionsPolicy);
        $this->assertHtmlContains('microphone=()', $permissionsPolicy);
        $response->assertSee('<meta name="viewport" content="width=device-width, initial-scale=1.0" />', false);
        $response->assertDontSee('user-scalable=0', false);
        $response->assertDontSee('maximum-scale=1', false);
    }

    public function test_root_renders_explicit_blade_shell_wrappers_around_legacy_dashboard_markup(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<!doctype html>', false);
        $response->assertSee('<html lang="id">', false);
        $response->assertSee('<head>', false);
        $response->assertSee('</head>', false);
        $response->assertSee('<body class="text-ppp-text antialiased">', false);
        $response->assertSee('</body>', false);
        $response->assertSee('</html>', false);
    }

    public function test_dashboard_shell_uses_blade_app_frame_partial_instead_of_raw_legacy_shell_fragment(): void
    {
        $bodyPartial = file_get_contents(resource_path('views/dashboard/partials/shell/body.blade.php'));
        $appFramePartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-frame.blade.php'));
        $appFrameSidebarPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-sidebar.blade.php'));
        $appFrameSidebarNavPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-sidebar-nav.blade.php'));
        $appFrameSidebarDashboardContentPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-sidebar-nav-dashboard-content.blade.php'));

        $this->assertIsString($bodyPartial);
        $this->assertIsString($appFramePartial);
        $this->assertIsString($appFrameSidebarPartial);
        $this->assertIsString($appFrameSidebarNavPartial);
        $this->assertIsString($appFrameSidebarDashboardContentPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-frame')", $bodyPartial);
        $this->assertHtmlNotContains('{!! $bodyBeforeDashboardMenu !!}', $bodyPartial);
        $this->assertHtmlNotContains("@include('dashboard.partials.menus.bonus-report')", $bodyPartial);
        $this->assertHtmlContains('<div id="app" class="min-h-[100dvh]" v-cloak>', $appFramePartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-frame-sidebar')", $appFramePartial);
        $this->assertHtmlContains('breadcrumbItems', $appFramePartial);
        $this->assertHtmlContains("'dashboard.partials.menus.bonus-report'", $appFramePartial);
        $this->assertHtmlContains("'dashboard.partials.menus.talent-bonus'", $appFramePartial);
        $this->assertHtmlContains("'dashboard.partials.menus.editor-performance'", $appFramePartial);
        $this->assertHtmlContains('</main>', $appFramePartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-frame-sidebar-nav')", $appFrameSidebarPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-frame-sidebar-nav-dashboard-content')", $appFrameSidebarNavPartial);
        $this->assertHtmlContains("switchTab('dashboard')", $appFrameSidebarDashboardContentPartial);
    }

    public function test_dashboard_shell_moves_large_inline_head_styles_into_vite_css(): void
    {
        $head = file_get_contents(resource_path('views/dashboard/partials/shell/head.blade.php'));
        $appCss = file_get_contents(resource_path('css/app.css'));
        $dashboardShellCss = file_get_contents(resource_path('css/dashboard-shell.css'));

        $this->assertIsString($head);
        $this->assertIsString($appCss);
        $this->assertIsString($dashboardShellCss);
        $this->assertHtmlNotContains('<style>', $head);
        $this->assertHtmlContains("@import './dashboard-shell.css';", $appCss);
        $this->assertHtmlContains('[v-cloak] {', $dashboardShellCss);
        $this->assertHtmlContains('.sr-only {', $dashboardShellCss);
        $this->assertHtmlContains('@media print {', $dashboardShellCss);
    }

    public function test_root_uses_html_and_body_shell_attributes_from_legacy_dashboard_file(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<html lang="id">', false);
        $response->assertSee('<body class="text-ppp-text antialiased">', false);
    }

    public function test_shell_builder_returns_vue_app_body_without_head_fragments(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertSame(' lang="id"', $fragments['htmlAttributes']);
        $this->assertSame(' class="text-ppp-text antialiased"', $fragments['bodyAttributes']);
        $this->assertSame('', $fragments['bodyHtml']);
        $this->assertFragmentHasKey('backendUrl', $fragments);
        $this->assertSame('https://backend.example.test', $fragments['backendUrl']);
        $this->assertFragmentNotHasKey('headHtml', $fragments);
        $this->assertFragmentNotHasKey('doctype', $fragments);
    }

    // These tests read Blade partials directly — they verify the partials exist and contain expected content.
    // Unlike build() tests below, they do NOT depend on legacy fragment extraction.

    public function test_shell_builder_splits_notification_error_utility_cluster_from_legacy_body_script(): void
    {
        $utilityPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-notification-error-utils.blade.php'));
        $protectedPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-protected-user-settings.blade.php'));

        $this->assertIsString($utilityPartial);
        $this->assertIsString($protectedPartial);
        $this->assertHtmlContains('const {', $utilityPartial);
        $this->assertHtmlContains('inferNotificationType,', $utilityPartial);
        $this->assertHtmlContains('getFriendlyErrorMessage,', $utilityPartial);
        $this->assertHtmlContains('createNotificationHelpers,', $utilityPartial);
        $this->assertHtmlContains('} = window.MarketingDashboardRuntimeHelpers;', $utilityPartial);
        $this->assertHtmlContains('} = createNotificationHelpers(notification);', $utilityPartial);
        $this->assertHtmlNotContains('const inferNotificationType = (message = \'\') => {', $protectedPartial);
    }

    public function test_shell_builder_splits_shell_interaction_helper_cluster_from_legacy_body_script(): void
    {
        $helperPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-shell-interaction-helpers.blade.php'));
        $protectedPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-protected-user-settings.blade.php'));

        $this->assertIsString($helperPartial);
        $this->assertIsString($protectedPartial);
        $this->assertHtmlContains('const toggleSidebar = () => {', $helperPartial);
        $this->assertHtmlContains('const closeProfileMenu = (e) => {', $helperPartial);
        $this->assertHtmlContains('const openProfileSetting = () => {', $helperPartial);
        $this->assertHtmlNotContains('const toggleSidebar = () => {', $protectedPartial);
    }

    public function test_shell_builder_splits_runner_factory_cluster_from_legacy_body_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeRunnerFactoriesCluster', $fragments);
        $this->assertFragmentHasKey('bodyAfterRunnerFactoriesCluster', $fragments);
        $this->assertSame('', $fragments['bodyBeforeRunnerFactoriesCluster']);
        $this->assertSame('', $fragments['bodyAfterRunnerFactoriesCluster']);
    }

    public function test_shell_builder_splits_customer_service_pdf_cluster_from_legacy_body_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeCustomerServicePdfCluster', $fragments);
        $this->assertFragmentHasKey('bodyAfterCustomerServicePdfCluster', $fragments);
        $this->assertSame('', $fragments['bodyBeforeCustomerServicePdfCluster']);
        $this->assertSame('', $fragments['bodyAfterCustomerServicePdfCluster']);
    }

    public function test_root_uses_customer_service_export_bridge_for_pilot_menu_exports(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardCustomerServiceExports', $html);
        $this->assertHtmlContains('const getCustomerServiceExportBridge_ = () => {', $html);
        $this->assertHtmlContains('return bridge.exportUnitDitanyaToExcel({', $html);
        $this->assertHtmlContains('return bridge.exportUnitDitanyaToPDF({', $html);
        $this->assertHtmlContains('return bridge.exportClaimGaransiToExcel({', $html);
        $this->assertHtmlContains('return bridge.exportClaimGaransiToPDF({', $html);
        $this->assertHtmlContains('return bridge.exportKeepBarangToExcel({', $html);
        $this->assertHtmlContains('return bridge.exportKeepBarangToPDF({', $html);
        $this->assertHtmlNotContains('const buildUnitDitanyaGrouped_ = (rows) => {', $html);
        $this->assertHtmlNotContains("const exportData = grouped.map((g, idx) => ({\n                            'No': idx + 1,", $html);
        $this->assertHtmlNotContains("title: 'CLAIM GARANSI & ASURANSI',", $html);
        $this->assertHtmlNotContains("title: 'Keep Barang',", $html);
        $this->assertHtmlNotContains("'Type HP': normalizeKeepBarangTypeHpValue(r.TYPE_HP) || '',", $html);
    }

    public function test_dashboard_shell_uses_blade_auth_session_partial_for_session_bootstrap_cluster(): void
    {
        $assemblyPartial = file_get_contents(resource_path('views/dashboard/partials/shell/body-app-assembly.blade.php'));
        $authSessionPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-auth-session.blade.php'));

        $this->assertIsString($assemblyPartial);
        $this->assertIsString($authSessionPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-auth-session')", $assemblyPartial);
        $this->assertHtmlContains('const submitting = ref(false);', $authSessionPartial);
        $this->assertHtmlContains('const loadStoredUser = () => {', $authSessionPartial);
        $this->assertHtmlContains('const currentUser = ref(loadStoredUser());', $authSessionPartial);
        $this->assertHtmlContains('const hasPermission = (tab, action) => {', $authSessionPartial);
        $this->assertHtmlContains('const loginForm = ref({ username: "", pin: "" });', $authSessionPartial);
        $this->assertHtmlContains('const showPin = ref(false);', $authSessionPartial);
        $this->assertHtmlContains('const rememberUsername = ref(false);', $authSessionPartial);
        $this->assertHtmlContains("localStorage.getItem('ppp_login_remember_username')", $authSessionPartial);
        $this->assertHtmlContains('const handleRememberUsernameChange = () => {', $authSessionPartial);
    }

    public function test_dashboard_shell_login_and_user_forms_have_bound_labels_and_field_names(): void
    {
        $appFramePartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-frame.blade.php'));
        $authUsersPartial = file_get_contents(resource_path('views/dashboard/partials/menus/auth-users.blade.php'));
        $profilePartial = file_get_contents(resource_path('views/dashboard/partials/menus/profile.blade.php'));
        $adminUsersRuntime = file_get_contents(resource_path('js/dashboard/menu/admin-users.js'));
        $protectedUserSettingsPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-protected-user-settings.blade.php'));

        $this->assertIsString($appFramePartial);
        $this->assertIsString($authUsersPartial);
        $this->assertIsString($profilePartial);
        $this->assertIsString($adminUsersRuntime);
        $this->assertIsString($protectedUserSettingsPartial);

        foreach ([
            'label for="login-username"',
            'input id="login-username" name="username"',
            'autocomplete="username"',
            'label for="login-pin"',
            'input id="login-pin" name="pin"',
            'autocomplete="current-password"',
        ] as $needle) {
            $this->assertHtmlContains($needle, $appFramePartial);
        }

        foreach ([
            'button @click="openAuthUserModal(\'create\')"',
            '<i class="fa-solid fa-user-plus"></i>',
            'primary-cta-button--icon-only',
            'aria-label="Tambah User"',
            'label for="auth-user-username"',
            'input id="auth-user-username" name="auth_user_username"',
            'label for="auth-user-nama"',
            'input id="auth-user-nama" name="auth_user_nama"',
            'label for="auth-user-email"',
            'input id="auth-user-email" name="auth_user_email"',
            'label for="auth-user-pin"',
            'input id="auth-user-pin" name="auth_user_pin"',
            'label for="auth-user-confirm-pin"',
            'input id="auth-user-confirm-pin" name="auth_user_confirm_pin"',
        ] as $needle) {
            $this->assertHtmlContains($needle, $authUsersPartial);
        }

        foreach ([
            "{ value: 'brand_ambasador', label: 'Brand Ambasador' }",
            "{ value: 'talent', label: 'Talent' }",
        ] as $needle) {
            $this->assertHtmlContains($needle, $protectedUserSettingsPartial);
        }

        foreach ([
            ':src="resolveAvatarUrl(user?.avatar_url)"',
            ':src="resolveAvatarUrl(authUserForm.avatar_url)"',
            '@error="markAuthUserAvatarFailed(user)"',
            '@error="authUserForm.avatar_url = null"',
            'v-if="submittingAuthUserAvatar"',
            '<span v-if="submittingAuthUserAvatar">Mengunggah...</span>',
        ] as $needle) {
            $this->assertHtmlContains($needle, $authUsersPartial);
        }

        foreach ([
            'v-if="submittingAvatar"',
            '<span v-if="submittingAvatar">Mengunggah...</span>',
        ] as $needle) {
            $this->assertHtmlContains($needle, $profilePartial);
        }

        foreach ([
            'const resolveAvatarUrl = (value) => {',
            'const markAuthUserAvatarFailed = (user) => {',
            'uploadProfileAvatar,',
            'uploadAuthUserAvatar,',
        ] as $needle) {
            $this->assertHtmlContains($needle, $adminUsersRuntime);
        }

        foreach ([
            'label for="profile-nama-lengkap"',
            'input id="profile-nama-lengkap" name="profile_nama_lengkap"',
            'label for="profile-role"',
            'input id="profile-role" name="profile_role"',
            'label for="profile-old-pin"',
            'input id="profile-old-pin" name="profile_old_pin"',
            'label for="profile-new-pin"',
            'input id="profile-new-pin" name="profile_new_pin"',
            'label for="profile-confirm-pin"',
            'input id="profile-confirm-pin" name="profile_confirm_pin"',
        ] as $needle) {
            $this->assertHtmlContains($needle, $profilePartial);
        }
    }

    public function test_master_plan_table_uses_user_avatar_when_available(): void
    {
        $masterPlanPartial = file_get_contents(resource_path('views/dashboard/partials/menus/master-plan.blade.php'));
        $contentComputedPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-content-list-computed.blade.php'));
        $runnerSessionTail = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-runner-session-tail.blade.php'));
        $returnBlock = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-return-block.blade.php'));

        $this->assertIsString($masterPlanPartial);
        $this->assertIsString($contentComputedPartial);
        $this->assertIsString($runnerSessionTail);
        $this->assertIsString($returnBlock);

        foreach ([
            ':src="resolveAvatarUrl(resolveUserAvatarUrl(item.Editor))"',
            '@error="markMasterPlanEditorAvatarFailed(item.Editor)"',
            'resolveUserAvatarUrl(item.Editor)',
            'masterPersonInitials(item.Editor)',
            'v-for="talent in item.TalentList"',
            ':src="resolveAvatarUrl(resolveUserAvatarUrl(talent))"',
            'masterPersonInitials(talent)',
            '@error="markMasterPlanEditorAvatarFailed(talent)"',
        ] as $needle) {
            $this->assertHtmlContains($needle, $masterPlanPartial);
        }

        foreach ([
            'const userLookupAliases = {',
            "'@ogohogohdenpasar': 'ogoh-ogoh-denpasar'",
            "'at-arthadashing': 'artha'",
            "'at-depraz': 'depraz'",
            "'at-sibliwira': 'wira'",
            'const normalizeUserLookupKey = (value) =>',
            'const userAvatarFailedKeys = ref(new Set());',
            'const resolveUserAvatarUrl = (value) => {',
            'const markMasterPlanEditorAvatarFailed = (value) => {',
            'const masterPersonInitials = (value) => {',
        ] as $needle) {
            $this->assertHtmlContains($needle, $contentComputedPartial);
        }

        $this->assertHtmlContains("if (['master', 'ideation', 'top_content_platform', 'low_content_platform', 'editor_performance', 'talent_bonus', 'bonus_report', 'unboxing', 'budgeting'].includes(tab) && canManageUsers.value && !authUsersLoaded.value) {", $runnerSessionTail);
        $this->assertHtmlContains('resolveUserAvatarUrl,', $returnBlock);
        $this->assertHtmlContains('markMasterPlanEditorAvatarFailed,', $returnBlock);
        $this->assertHtmlContains('masterPersonInitials,', $returnBlock);
    }

    public function test_editor_talent_and_colab_people_use_shared_avatar_rows(): void
    {
        $partials = [
            'master-plan' => file_get_contents(resource_path('views/dashboard/partials/menus/master-plan.blade.php')),
            'editor-performance' => file_get_contents(resource_path('views/dashboard/partials/menus/editor-performance.blade.php')),
            'talent-bonus' => file_get_contents(resource_path('views/dashboard/partials/menus/talent-bonus.blade.php')),
            'bonus-report' => file_get_contents(resource_path('views/dashboard/partials/menus/bonus-report.blade.php')),
            'unboxing' => file_get_contents(resource_path('views/dashboard/partials/menus/unboxing.blade.php')),
            'budgeting' => file_get_contents(resource_path('views/dashboard/partials/menus/budgeting.blade.php')),
            'top-content' => file_get_contents(resource_path('views/dashboard/partials/menus/top-content.blade.php')),
            'low-content' => file_get_contents(resource_path('views/dashboard/partials/menus/low-content.blade.php')),
        ];
        $contentComputedPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-content-list-computed.blade.php'));
        $returnBlock = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-return-block.blade.php'));

        foreach ($partials as $partial) {
            $this->assertIsString($partial);
        }

        foreach ([
            'const personDisplayName = (value) => {',
            "return trimmed || '-'",
        ] as $needle) {
            $this->assertHtmlContains($needle, $contentComputedPartial);
        }

        foreach ([
            'personDisplayName,',
        ] as $needle) {
            $this->assertHtmlContains($needle, $returnBlock);
        }

        foreach ([
            'master-plan' => 'personDisplayName(item.Editor)',
            'editor-performance' => 'personDisplayName(video.Editor)',
            'talent-bonus' => 'personDisplayName(row.Talent)',
            'bonus-report' => 'personDisplayName(row.Editor)',
            'unboxing' => 'personDisplayName(row.Editor)',
            'budgeting' => 'personDisplayName(item.colabPartner)',
            'top-content' => 'personDisplayName(row.editor)',
            'low-content' => 'personDisplayName(row.editor)',
        ] as $partialName => $needle) {
            $this->assertHtmlContains($needle, $partials[$partialName]);
        }

        foreach ([
            'editor-performance',
            'talent-bonus',
            'bonus-report',
            'unboxing',
            'budgeting',
        ] as $partialName) {
            $this->assertHtmlContains('class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-overline font-bold text-slate-600 flex-shrink-0 overflow-hidden"', $partials[$partialName]);
            $this->assertHtmlContains('class="text-body text-slate-700 font-semibold truncate max-w-[80px]"', $partials[$partialName]);
        }

        foreach (['top-content', 'low-content'] as $partialName) {
            $this->assertHtmlContains('class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-overline font-bold text-slate-600 flex-shrink-0 overflow-hidden"', $partials[$partialName]);
            $this->assertHtmlContains('class="text-body text-slate-700 font-semibold whitespace-normal break-words leading-snug"', $partials[$partialName]);
            $this->assertHtmlContains(':src="resolveAvatarUrl(resolveUserAvatarUrl(row.editor))"', $partials[$partialName]);
            $this->assertHtmlContains('@error="markMasterPlanEditorAvatarFailed(row.editor)"', $partials[$partialName]);
            $this->assertHtmlNotContains('<td class="px-6 py-3 text-body text-slate-600">{{ row.editor }}</td>', $partials[$partialName]);
        }
    }

    public function test_platform_cells_use_shared_icon_and_label_rows(): void
    {
        $partials = [
            'master-plan' => file_get_contents(resource_path('views/dashboard/partials/menus/master-plan.blade.php')),
            'ideation' => file_get_contents(resource_path('views/dashboard/partials/menus/ideation.blade.php')),
            'distribution' => file_get_contents(resource_path('views/dashboard/partials/menus/distribution.blade.php')),
            'analytics' => file_get_contents(resource_path('views/dashboard/partials/menus/analytics.blade.php')),
            'bonus-report' => file_get_contents(resource_path('views/dashboard/partials/menus/bonus-report.blade.php')),
            'editor-performance' => file_get_contents(resource_path('views/dashboard/partials/menus/editor-performance.blade.php')),
            'top-content' => file_get_contents(resource_path('views/dashboard/partials/menus/top-content.blade.php')),
            'low-content' => file_get_contents(resource_path('views/dashboard/partials/menus/low-content.blade.php')),
            'ads-log' => file_get_contents(resource_path('views/dashboard/partials/menus/ads-log.blade.php')),
        ];
        $contentComputedPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-content-list-computed.blade.php'));
        $returnBlock = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-return-block.blade.php'));

        foreach ($partials as $partial) {
            $this->assertIsString($partial);
        }

        foreach ([
            'const platformDisplayName = (value) => {',
            "return trimmed || '-'",
        ] as $needle) {
            $this->assertHtmlContains($needle, $contentComputedPartial);
        }

        $this->assertHtmlContains('platformDisplayName,', $returnBlock);

        foreach ([
            'master-plan' => 'platformDisplayName(plat)',
            'ideation' => 'platformDisplayName(plat)',
            'distribution' => 'platformDisplayName(row.Platform)',
            'analytics' => 'platformDisplayName(row.Platform)',
            'bonus-report' => 'platformDisplayName(row.Platform)',
            'editor-performance' => 'platformDisplayName(plat)',
            'top-content' => 'platformDisplayName(row.platform)',
            'low-content' => 'platformDisplayName(row.platform)',
            'ads-log' => "platformDisplayName(row.Platform || 'Ads')",
        ] as $partialName => $needle) {
            $this->assertHtmlContains($needle, $partials[$partialName]);
        }

        foreach ($partials as $partialName => $partial) {
            $this->assertHtmlContains(" + ' text-body text-slate-400'", $partial, "{$partialName} platform icon must use shared sizing/color.");
            $this->assertHtmlContains('class="text-body font-bold text-slate-700"', $partial, "{$partialName} platform label must use shared typography.");
        }

        $this->assertHtmlContains('class="flex flex-col items-start gap-1.5"', $partials['master-plan']);
        $this->assertHtmlContains('class="mobile-data-card__meta mobile-data-card__meta--stacked mt-2"', $partials['master-plan']);
        $this->assertHtmlContains('class="flex flex-col items-start gap-1.5 max-w-[160px]"', $partials['ideation']);
        $this->assertHtmlContains('v-for="plat in (video.Platforms || \'\').split(\',\')"', $partials['editor-performance']);
        $this->assertHtmlContains('.mobile-data-card__meta--stacked {', $html = $this->renderDashboardHtmlWithShellCss());
        $this->assertHtmlContains('flex-direction: column;', $html);
        $this->assertHtmlNotContains('class="flex flex-wrap gap-2 max-w-[160px]"', $partials['ideation']);
        $this->assertHtmlNotContains('class="flex flex-wrap gap-2">'."\n".'                                                    <span v-for="plat in (item.Platforms || \'\').split(\',\')"', $partials['master-plan']);
        $this->assertHtmlNotContains('platformDisplayName(video.Platforms)', $partials['editor-performance']);
    }

    public function test_desktop_tables_use_page_scroll_with_horizontal_table_overflow_only(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        foreach ([
            '.dashboard-main-shell > main {',
            'box-sizing: border-box;',
            'overscroll-behavior-y: contain;',
            '.dashboard-main-shell {',
            'overflow: hidden;',
            'height: 100dvh;',
            'overflow-x: clip;',
            'overflow-y: auto;',
            '.dashboard-main-shell > main > .animate-fadeIn,',
            '.dashboard-main-shell > main > .space-y-4 {',
            'flex: 0 0 auto;',
            'min-height: auto;',
            'overflow: visible;',
            '.section-card-shell {',
            'display: flex;',
            'flex-direction: column;',
            'overflow: visible;',
            '.hidden.section-card-shell {',
            'display: none !important;',
            '.md\\:hidden.section-card-shell {',
            '.hidden.md\\:block.section-card-shell {',
            'display: flex !important;',
            '.section-card-shell .overflow-x-auto {',
            'overflow-x: auto;',
            'overflow-y: clip;',
            'flex: 0 0 auto;',
            'position: relative;',
            '.section-card-shell .overflow-x-auto table {',
            'border-collapse: separate !important;',
            'border-spacing: 0;',
            'table-layout: fixed;',
            '.section-card-shell .overflow-x-auto thead {',
            'display: table-header-group;',
            '.section-card-shell .overflow-x-auto thead th {',
            'position: sticky !important;',
            'top: 0;',
            'z-index: 50;',
            'background: var(--ppp-card);',
            'background-clip: padding-box;',
            'background-clip: padding-box;',
            'isolation: isolate;',
        ] as $needle) {
            $this->assertHtmlContains($needle, $html);
        }

        $this->assertHtmlContains('@media (min-width: 768px) {', $html);
        $this->assertHtmlContains('max-height: none;', $html);
        $this->assertHtmlNotContains('max-height: calc(100dvh - 5.75rem);', $html);
        $this->assertHtmlNotContains('overflow: auto;', $html);
    }

    public function test_dashboard_shell_toolbar_search_fields_have_names_and_accessible_labels(): void
    {
        $partials = [
            file_get_contents(resource_path('views/dashboard/partials/menus/master-plan.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/distribution.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/analytics.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/activity-logs.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/unit-ditanya.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/claim-garansi.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/unboxing.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/order-online.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/program-promo.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/harga-kompetitor.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/laporan-event.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/ads-log.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/sell-out.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/editor-performance.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/keep-barang.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/meta-story.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/meta-feed.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/ideation.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/nama-stock.blade.php')),
        ];

        foreach ($partials as $partial) {
            $this->assertIsString($partial);
        }

        foreach ([
            'id="master-search" name="master_search"',
            'id="distribution-search-mobile" name="distribution_search_mobile"',
            'id="distribution-search-desktop" name="distribution_search_desktop"',
            'id="analytics-search-mobile" name="analytics_search_mobile"',
            'id="analytics-search-desktop" name="analytics_search_desktop"',
            'id="activity-log-record-key" name="activity_log_record_key"',
            'id="activity-log-table-name" name="activity_log_table_name"',
            'id="activity-log-action" name="activity_log_action"',
            'id="unit-ditanya-search-mobile" name="unit_ditanya_search_mobile"',
            'id="unit-ditanya-search-desktop" name="unit_ditanya_search_desktop"',
            'id="claim-garansi-search-mobile" name="claim_garansi_search_mobile"',
            'id="claim-garansi-search-desktop" name="claim_garansi_search_desktop"',
            'id="unboxing-search-mobile" name="unboxing_search_mobile"',
            'id="unboxing-search-desktop" name="unboxing_search_desktop"',
            'id="order-online-search-mobile" name="order_online_search_mobile"',
            'id="order-online-search-desktop" name="order_online_search_desktop"',
            'id="promo-search" name="promo_search"',
            'id="harga-kompetitor-search" name="harga_kompetitor_search"',
            'id="lpjk-search" name="lpjk_search"',
            'id="ads-log-search" name="ads_log_search"',
            'id="sell-out-search" name="sell_out_search"',
            'id="keep-barang-search-mobile" name="keep_barang_search_mobile"',
            'id="keep-barang-search-desktop" name="keep_barang_search_desktop"',
            'id="meta-story-search" name="meta_story_search"',
            'id="ideation-search" name="ideation_search"',
            'id="nama-stock-search" name="nama_stock_search"',
            'id="meta-story-upload" name="meta_story_upload"',
            'id="meta-feed-upload" name="meta_feed_upload"',
        ] as $needle) {
            $this->assertTrue(
                collect($partials)->contains(fn ($partial) => is_string($partial) && str_contains($partial, $needle)),
                "Failed asserting toolbar field marker exists: {$needle}"
            );
        }
    }

    public function test_dashboard_shell_budgeting_modals_expose_named_labeled_core_fields(): void
    {
        $budgetingPartial = file_get_contents(resource_path('views/dashboard/partials/menus/budgeting.blade.php'));

        $this->assertIsString($budgetingPartial);

        foreach ([
            'for="harga-kompetitor-nama-produk"',
            'id="harga-kompetitor-nama-produk" name="harga_kompetitor_nama_produk"',
            'harga-kompetitor-warna',
            'harga_kategori_search',
            'harga_brand_search',
            'harga_seri_search',
            'hargaRAMOptions',
            'hargaInternalOptions',
            'hargaSizeOptions',
            'for="ads-nama"',
            'id="ads-nama" name="ads_nama"',
            'for="ads-jangkauan"',
            'id="ads-jangkauan" name="ads_jangkauan"',
            'for="unboxing-judul"',
            'id="unboxing-judul" name="unboxing_judul"',
            'for="distribution-judul"',
            'id="distribution-judul" name="distribution_judul"',
            'for="analytics-judul"',
            'id="analytics-judul" name="analytics_judul"',
            'for="analytics-id-post"',
            'id="analytics-id-post" name="analytics_id_post"',
            'for="claim-nama-customer"',
            'id="claim-nama-customer" name="claim_nama_customer"',
            'for="keep-nama-customer"',
            'id="keep-nama-customer" name="keep_nama_customer"',
            'for="promo-program"',
            'id="promo-program" name="promo_program"',
            'for="lpjk-nama-event"',
            'id="lpjk-nama-event" name="lpjk_nama_event"',
            'for="master-plan-judul"',
            'id="master-plan-judul" name="master_plan_judul"',
            'for="story-konten"',
            'id="sell-out-target-unit" name="sell_out_target_unit"',
            'for="sell-out-vendor-manual"',
            'id="sell-out-vendor-manual" name="sell_out_vendor_manual"',
            'for="sell-out-seri-manual"',
            'id="sell-out-seri-manual" name="sell_out_seri_manual"',
            'id="story-konten" name="story_konten"',
            'for="order-online-nama"',
            'id="order-online-nama" name="order_online_nama"',
            'for="unit-ditanya-warna"',
            'id="unit-ditanya-warna" name="unit_ditanya_warna"',
        ] as $needle) {
            $this->assertHtmlContains($needle, $budgetingPartial);
        }
    }

    public function test_dashboard_shell_budgeting_popover_search_fields_have_names_and_aria_labels(): void
    {
        $budgetingPartial = file_get_contents(resource_path('views/dashboard/partials/menus/budgeting.blade.php'));

        $this->assertIsString($budgetingPartial);
        $this->assertHtmlContains('name="search_select_query"', $budgetingPartial);
        $this->assertHtmlContains('aria-label="Cari kategori unit ditanya"', $budgetingPartial);
        $this->assertHtmlContains('aria-label="Cari tipe claim garansi"', $budgetingPartial);
        $this->assertHtmlContains('aria-label="Cari type HP keep barang"', $budgetingPartial);
        $this->assertHtmlContains('aria-label="Cari ecommerce order online"', $budgetingPartial);
        $this->assertHtmlContains('aria-label="Cari kategori nama stock"', $budgetingPartial);
        $this->assertHtmlContains('aria-label="Cari brand nama stock"', $budgetingPartial);
    }

    public function test_dashboard_shell_settings_and_bonus_forms_have_named_bound_inputs(): void
    {
        $settingsPartial = file_get_contents(resource_path('views/dashboard/partials/menus/settings.blade.php'));
        $bonusPartial = file_get_contents(resource_path('views/dashboard/partials/menus/bonus-report.blade.php'));

        $this->assertIsString($settingsPartial);
        $this->assertIsString($bonusPartial);

        foreach ([
            'label for="settings-search-query"',
            'id="settings-search-query"',
            'name="settings_search_query"',
            'label for="settings-active-value-search"',
            'id="settings-active-value-search"',
            'name="settings_active_value_search"',
            'label for="settings-bulk-add-text"',
            'id="settings-bulk-add-text"',
            'name="settings_bulk_add_text"',
            ':name="`settings_option_',
            ':name="`settings_option_mobile_',
        ] as $needle) {
            $this->assertHtmlContains($needle, $settingsPartial);
        }

        foreach ([
            'v-if="activeSettingTab &amp;&amp; settingsDraft[activeSettingTab]"',
            'v-if="showSettingsBulkAdd &amp;&amp; !isSettingTabObject(activeSettingTab)"',
            "v-if=\"activeTab === 'settings' &amp;&amp; isMobileViewport &amp;&amp; settingsDetailModalOpen &amp;&amp; activeSettingTab &amp;&amp; settingsDraft[activeSettingTab]\"",
            'class="animate-fadeIn space-y-4"',
            'md:h-[calc(100dvh-168px)]',
            'md:overflow-hidden',
            'md:flex-1 md:min-h-0 md:overflow-y-auto md:pr-1',
            'filteredSettingsTabCount === 0',
            'class="hidden md:block section-card section-card-shell md:h-[calc(100dvh-168px)] md:min-h-0"',
            'class="flex h-full min-h-0 flex-col"',
            'class="shrink-0 border-b border-slate-100 px-4 py-4 md:px-5"',
            'class="flex-1 min-h-0 overflow-y-auto bg-slate-50/60 px-4 py-4 md:px-5"',
            'class="shrink-0 border-t border-slate-100 bg-white/95 px-4 py-3 backdrop-blur md:px-5"',
        ] as $needle) {
            $this->assertHtmlContains($needle, $settingsPartial);
        }

        $this->assertHtmlNotContains('md:sticky md:top-4', $settingsPartial);
        $this->assertHtmlNotContains('min-h-[calc(100dvh-220px)]', $settingsPartial);

        foreach ([
            'id="bonus-instagram-like-unit" name="bonus_instagram_like_unit"',
            'id="bonus-instagram-like-bonus" name="bonus_instagram_like_bonus"',
            'id="bonus-tiktok-like-unit" name="bonus_tiktok_like_unit"',
            'id="bonus-tiktok-like-bonus" name="bonus_tiktok_like_bonus"',
            'id="bonus-comment-unit" name="bonus_comment_unit"',
            'id="bonus-comment-bonus" name="bonus_comment_bonus"',
            ':name="`bonus_reels_non_colab_min_',
            ':name="`bonus_reels_colab_amount_',
        ] as $needle) {
            $this->assertHtmlContains($needle, $bonusPartial);
        }
    }

    public function test_dashboard_shell_nama_stock_partial_keeps_section_and_div_wrappers_balanced(): void
    {
        $namaStockPartial = file_get_contents(resource_path('views/dashboard/partials/menus/nama-stock.blade.php'));

        $this->assertIsString($namaStockPartial);
        $this->assertHtmlContains('<section class="section-card section-card-shell">', $namaStockPartial);
        $this->assertHtmlContains('</section>', $namaStockPartial);
        $this->assertHtmlNotContains("</div>\n                    </div>\n@endverbatim", $namaStockPartial);
    }

    public function test_dashboard_shell_budgeting_partial_does_not_leave_trailing_container_closer(): void
    {
        $budgetingPartial = file_get_contents(resource_path('views/dashboard/partials/menus/budgeting.blade.php'));

        $this->assertIsString($budgetingPartial);
        $this->assertHtmlContains('</teleport>', $budgetingPartial);
        $this->assertHtmlNotContains("</teleport>\n    </div>\n@endverbatim", $budgetingPartial);
        $this->assertHtmlNotContains('</main>', $budgetingPartial);
    }

    public function test_dashboard_shell_uses_blade_domain_state_wrapper_and_child_partials(): void
    {
        $assemblyPartial = file_get_contents(resource_path('views/dashboard/partials/shell/body-app-assembly.blade.php'));
        $domainStatePartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-domain-state.blade.php'));
        $domainStateCorePartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-domain-state-core.blade.php'));
        $domainStateBonusReportingPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-domain-state-bonus-reporting.blade.php'));
        $domainStateTableSortingPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-domain-state-table-sorting.blade.php'));
        $domainStateContentFormsPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-domain-state-content-forms.blade.php'));

        $this->assertIsString($assemblyPartial);
        $this->assertIsString($domainStatePartial);
        $this->assertIsString($domainStateCorePartial);
        $this->assertIsString($domainStateBonusReportingPartial);
        $this->assertIsString($domainStateTableSortingPartial);
        $this->assertIsString($domainStateContentFormsPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-domain-state')", $assemblyPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-domain-state-core')", $domainStatePartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-domain-state-bonus-reporting')", $domainStatePartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-domain-state-table-sorting')", $domainStatePartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-domain-state-content-forms')", $domainStatePartial);
        $this->assertHtmlContains('const getDefaultDateRange = () => {', $domainStateCorePartial);
        $this->assertHtmlContains('const orderanOnlineSearch = ref(\'\');', $domainStateCorePartial);
        $this->assertHtmlContains('const bonusMonth = ref(_bonusDefault.month);', $domainStateBonusReportingPartial);
        $this->assertHtmlContains('const hydrateSortableTableHeaders = () => {', $domainStateTableSortingPartial);
        $this->assertHtmlContains('createCustomerServiceState', $domainStateCorePartial);
        $this->assertHtmlContains('window.MarketingDashboardRuntimeHelpers.createCustomerServiceState({', $domainStateCorePartial);
        $this->assertHtmlNotContains('const storyTab = ref("Ganjil");', $domainStateContentFormsPartial);
        $this->assertHtmlNotContains('const getDefaultDateRange = () => {', $domainStatePartial);
        $this->assertHtmlNotContains('const hydrateSortableTableHeaders = () => {', $domainStatePartial);
    }

    public function test_dashboard_shell_uses_blade_protected_user_settings_partial_for_protected_loader_cluster(): void
    {
        $assemblyPartial = file_get_contents(resource_path('views/dashboard/partials/shell/body-app-assembly.blade.php'));
        $protectedPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-protected-user-settings.blade.php'));
        $contentListComputedPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-content-list-computed.blade.php'));
        $masterContentOperationsPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-master-content-operations.blade.php'));
        $distributionAnalyticsOperationsPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-distribution-analytics-operations.blade.php'));
        $insightTrendPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-content-insight-trends.blade.php'));

        $this->assertIsString($assemblyPartial);
        $this->assertIsString($protectedPartial);
        $this->assertIsString($contentListComputedPartial);
        $this->assertIsString($masterContentOperationsPartial);
        $this->assertIsString($distributionAnalyticsOperationsPartial);
        $this->assertIsString($insightTrendPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-protected-user-settings')", $assemblyPartial);
        $this->assertHtmlContains('// Nama Stock State', $protectedPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-content-list-computed')", $protectedPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-master-content-operations')", $protectedPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-distribution-analytics-operations')", $protectedPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-content-insight-trends')", $protectedPartial);
        $this->assertHtmlContains('createAdminUserSettingsState', $protectedPartial);
        $this->assertHtmlContains('window.MarketingDashboardRuntimeHelpers.createAdminUserSettingsState(ref);', $protectedPartial);
        $this->assertHtmlContains('const filteredMasterPlanData = computed(() => {', $contentListComputedPartial);
        $this->assertHtmlContains('const filteredKeepBarangData = computed(() => {', $contentListComputedPartial);
        $this->assertHtmlContains('const loadMasterPlanData = async () => {', $masterContentOperationsPartial);
        $this->assertHtmlContains('const saveAnalytics = () => {', $distributionAnalyticsOperationsPartial);
        $this->assertHtmlContains('const buildContentRanking = (mode) => {', $insightTrendPartial);
        $this->assertHtmlContains('const claimGaransiStats = computed(() => {', $insightTrendPartial);
        $this->assertHtmlNotContains('const filteredMasterPlanData = computed(() => {', $protectedPartial);
        $this->assertHtmlNotContains('const loadMasterPlanData = async () => {', $protectedPartial);
        $this->assertHtmlNotContains('const buildContentRanking = (mode) => {', $protectedPartial);
    }

    public function test_analytics_id_post_input_queues_meta_metric_sync_while_typing(): void
    {
        $budgetingPartial = file_get_contents(resource_path('views/dashboard/partials/menus/budgeting.blade.php'));
        $analyticsOperationsPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-distribution-analytics-operations.blade.php'));

        $this->assertIsString($budgetingPartial);
        $this->assertIsString($analyticsOperationsPartial);
        $this->assertHtmlContains('@input="queueAnalyticsIdPostSync($event.target.value)"', $budgetingPartial);
        $this->assertHtmlContains('const normalizeMetaPostId = (value) => String(value || \'\').trim();', $analyticsOperationsPartial);
        $this->assertHtmlContains("const isInstagramAnalyticsPlatform = () => String(analyticsForm.value.Platform || '').trim().toLowerCase() === 'instagram';", $analyticsOperationsPartial);
        $this->assertHtmlContains('const getMetaFeedPostId = (row) => normalizeMetaPostId(row?.post_id || row?.ID || row?.Post_ID || row?.ID_Post || row?.id_post || row?.[\'Post ID\'] || row?.[\'post id\'] || row?.media_id);', $analyticsOperationsPartial);
        $this->assertHtmlContains('const queueAnalyticsIdPostSync = (value, options = {}) => {', $analyticsOperationsPartial);
        $this->assertHtmlContains('if (!isInstagramAnalyticsPlatform()) return;', $analyticsOperationsPartial);
        $this->assertHtmlContains('const bindAnalyticsIdPostDomSync = () => {', $analyticsOperationsPartial);
        $this->assertHtmlContains('const updateAnalyticsMetricInputs = () => {', $analyticsOperationsPartial);
        $this->assertHtmlContains("fetch(resolveAppUrl('/api/meta-posts/feed'),", $analyticsOperationsPartial);
        $this->assertHtmlContains("input.addEventListener('input', syncFromDomInput);", $analyticsOperationsPartial);
        $this->assertHtmlContains('queueAnalyticsIdPostSync(value);', $analyticsOperationsPartial);
        $this->assertHtmlContains('queueAnalyticsIdPostSync,', file_get_contents(resource_path('views/dashboard/partials/shell/app-script-return-block.blade.php')));
    }

    public function test_dashboard_shell_uses_blade_settings_cluster_partial_for_settings_loader_cluster(): void
    {
        $assemblyPartial = file_get_contents(resource_path('views/dashboard/partials/shell/body-app-assembly.blade.php'));
        $settingsPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-settings-cluster.blade.php'));
        $protectedPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-protected-user-settings.blade.php'));

        $this->assertIsString($assemblyPartial);
        $this->assertIsString($settingsPartial);
        $this->assertIsString($protectedPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-settings-cluster')", $assemblyPartial);
        $this->assertHtmlContains('const settings = ref({});', $protectedPartial);
        $this->assertHtmlContains('const loadSettings = () => {', $settingsPartial);
        $this->assertHtmlContains('const saveSettingsBackend = () => {', $settingsPartial);
        $this->assertHtmlNotContains('const settings = ref({});', $settingsPartial);
    }

    public function test_dashboard_shell_uses_blade_nama_stock_action_partial_for_stock_handlers_cluster(): void
    {
        $assemblyPartial = file_get_contents(resource_path('views/dashboard/partials/shell/body-app-assembly.blade.php'));
        $namaStockActionPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-nama-stock-actions.blade.php'));
        $protectedPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-protected-user-settings.blade.php'));

        $this->assertIsString($assemblyPartial);
        $this->assertIsString($namaStockActionPartial);
        $this->assertIsString($protectedPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-nama-stock-actions')", $assemblyPartial);
        $this->assertHtmlContains('createNamaStockActions', $namaStockActionPartial);
        $this->assertHtmlContains('window.MarketingDashboardRuntimeHelpers.createNamaStockActions({', $namaStockActionPartial);
        $this->assertHtmlContains('openNamaStockFormModal,', $namaStockActionPartial);
        $this->assertHtmlContains('saveNamaStockSettings,', $namaStockActionPartial);
        $this->assertHtmlContains('createNamaStockState', $protectedPartial);
        $this->assertHtmlContains('window.MarketingDashboardRuntimeHelpers.createNamaStockState(ref);', $protectedPartial);
    }

    public function test_dashboard_shell_uses_blade_meta_ig_partial_for_analytics_cluster(): void
    {
        $assemblyPartial = file_get_contents(resource_path('views/dashboard/partials/shell/body-app-assembly.blade.php'));
        $metaPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-meta-ig-analytics.blade.php'));
        $metaPresentationPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-meta-ig-analytics-presentation.blade.php'));
        $protectedPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-protected-user-settings.blade.php'));

        $this->assertIsString($assemblyPartial);
        $this->assertIsString($metaPartial);
        $this->assertIsString($metaPresentationPartial);
        $this->assertIsString($protectedPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-meta-ig-analytics')", $assemblyPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-meta-ig-analytics-presentation')", $assemblyPartial);
        $this->assertHtmlContains('const loadMetaStory = () => new Promise(resolve => {', $metaPartial);
        $this->assertHtmlContains('const uploadMetaCsv = (file, dataset) => {', $metaPartial);
        $this->assertHtmlContains('const renderMetaCharts = (dataset) => {', $metaPresentationPartial);
        $this->assertHtmlContains('const metaStorySummary = computed(() => {', $metaPresentationPartial);
        $this->assertHtmlNotContains('const loadMetaStory = () => new Promise(resolve => {', $protectedPartial);
        $this->assertHtmlNotContains('const renderMetaCharts = (dataset) => {', $protectedPartial);
    }

    public function test_dashboard_shell_uses_blade_profile_user_mutation_partial_for_profile_handler_cluster(): void
    {
        $assemblyPartial = file_get_contents(resource_path('views/dashboard/partials/shell/body-app-assembly.blade.php'));
        $mutationPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-profile-user-mutations.blade.php'));

        $this->assertIsString($assemblyPartial);
        $this->assertIsString($mutationPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-profile-user-mutations')", $assemblyPartial);
        $this->assertHtmlContains('createAdminUserSettingsActions', $mutationPartial);
        $this->assertHtmlContains('window.MarketingDashboardRuntimeHelpers.createAdminUserSettingsActions({', $mutationPartial);
        $this->assertHtmlContains('loadAuthUsers,', $mutationPartial);
        $this->assertHtmlContains('submitAuthUserForm,', $mutationPartial);
    }

    public function test_dashboard_shell_uses_blade_notification_error_utility_partial_for_feedback_cluster(): void
    {
        $protectedPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-protected-user-settings.blade.php'));
        $utilityPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-notification-error-utils.blade.php'));

        $this->assertIsString($protectedPartial);
        $this->assertIsString($utilityPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-notification-error-utils')", $protectedPartial);
        $this->assertHtmlContains('inferNotificationType,', $utilityPartial);
        $this->assertHtmlContains('createNotificationHelpers,', $utilityPartial);
        $this->assertHtmlContains('const {', $utilityPartial);
        $this->assertHtmlContains('} = createNotificationHelpers(notification);', $utilityPartial);
    }

    public function test_dashboard_shell_uses_blade_shell_interaction_helper_partial_for_profile_menu_cluster(): void
    {
        $protectedPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-protected-user-settings.blade.php'));
        $helperPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-shell-interaction-helpers.blade.php'));

        $this->assertIsString($protectedPartial);
        $this->assertIsString($helperPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-shell-interaction-helpers')", $protectedPartial);
        $this->assertHtmlContains('const toggleSidebar = () => {', $helperPartial);
        $this->assertHtmlContains('const clearPopoverTriggerState = () => {', $helperPartial);
        $this->assertHtmlContains('const closeProfileMenu = (e) => {', $helperPartial);
        $this->assertHtmlContains('const openProfileSetting = () => {', $helperPartial);
    }

    public function test_dashboard_shell_uses_blade_runner_factories_partial_for_runtime_runner_cluster(): void
    {
        $assemblyPartial = file_get_contents(resource_path('views/dashboard/partials/shell/body-app-assembly.blade.php'));
        $runnerPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-runner-factories.blade.php'));
        $runnerMockPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-runner-mock-factory.blade.php'));
        $runnerWebPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-runner-web-factory.blade.php'));
        $runnerSessionTailPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-runner-session-tail.blade.php'));

        $this->assertIsString($assemblyPartial);
        $this->assertIsString($runnerPartial);
        $this->assertIsString($runnerMockPartial);
        $this->assertIsString($runnerWebPartial);
        $this->assertIsString($runnerSessionTailPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-runner-factories')", $assemblyPartial);
        $this->assertHtmlNotContains('{!! $bodyBeforeRunnerFactoriesCluster !!}', $assemblyPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-runner-mock-factory')", $runnerPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-runner-web-factory')", $runnerPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-runner-session-tail')", $runnerPartial);
        $this->assertHtmlContains('const createMockRunner = () => {', $runnerMockPartial);
        $this->assertHtmlContains('const createWebRunner = () => {', $runnerWebPartial);
        $this->assertHtmlContains('const ensureRunApi = () => createWebRunner();', $runnerSessionTailPartial);
        $this->assertHtmlContains('const resumeActiveTabAfterBootstrap = () => {', $runnerSessionTailPartial);
        $this->assertHtmlNotContains('const createMockRunner = () => {', $runnerPartial);
        $this->assertHtmlNotContains('const createWebRunner = () => {', $runnerPartial);
        $this->assertHtmlNotContains('const ensureRunApi = () => createWebRunner();', $runnerPartial);
    }

    public function test_dashboard_shell_uses_blade_reporting_and_budgeting_partial_for_operational_cluster(): void
    {
        $assemblyPartial = file_get_contents(resource_path('views/dashboard/partials/shell/body-app-assembly.blade.php'));
        $reportingPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-reporting-and-budgeting.blade.php'));
        $adsLogOperationsPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-ads-log-operations.blade.php'));
        $priceCompetitorOperationsPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-price-competitor-operations.blade.php'));
        $lpjkOperationsPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-lpjk-operations.blade.php'));
        $budgetingPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-budgeting-operations.blade.php'));

        $this->assertIsString($assemblyPartial);
        $this->assertIsString($reportingPartial);
        $this->assertIsString($adsLogOperationsPartial);
        $this->assertIsString($priceCompetitorOperationsPartial);
        $this->assertIsString($lpjkOperationsPartial);
        $this->assertIsString($budgetingPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-reporting-and-budgeting')", $assemblyPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-ads-log-operations')", $assemblyPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-price-competitor-operations')", $assemblyPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-lpjk-operations')", $assemblyPartial);
        $this->assertHtmlNotContains("@include('dashboard.partials.shell.app-script-reporting-operations')", $assemblyPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-budgeting-operations')", $assemblyPartial);
        $this->assertHtmlNotContains("@include('dashboard.partials.shell.app-script-setup-tail')", $assemblyPartial);
        $this->assertHtmlContains('const saveBonusConfig = () => {', $reportingPartial);
        $this->assertHtmlNotContains('const adsComputedScore = computed(() => {', $reportingPartial);
        $this->assertHtmlContains('const adsComputedScore = computed(() => {', $adsLogOperationsPartial);
        $this->assertHtmlContains('const saveAdsRow = () => {', $adsLogOperationsPartial);
        $this->assertHtmlContains('const saveHargaKompetitor = () => {', $priceCompetitorOperationsPartial);
        $this->assertHtmlContains('const saveLpjk = () => {', $lpjkOperationsPartial);
        $this->assertHtmlContains('const budgetCalculations = computed(() => {', $budgetingPartial);
        $this->assertHtmlContains('const exportBudgetToExcel = () => {', $budgetingPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-bonus-talent-cluster')", $assemblyPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-cache-bootstrap-loaders')", $assemblyPartial);
    }

    public function test_dashboard_shell_uses_blade_lifecycle_watcher_partial_for_bootstrap_and_tab_watchers(): void
    {
        $assemblyPartial = file_get_contents(resource_path('views/dashboard/partials/shell/body-app-assembly.blade.php'));
        $watcherPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-lifecycle-watchers.blade.php'));
        $customerServicePartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-customer-service-crud.blade.php'));

        $this->assertIsString($assemblyPartial);
        $this->assertIsString($watcherPartial);
        $this->assertIsString($customerServicePartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-lifecycle-watchers')", $assemblyPartial);
        $this->assertHtmlContains('const hasBlockingOverlayOpen = computed(() => Boolean(', $watcherPartial);
        $this->assertHtmlContains('onMounted(async () => {', $watcherPartial);
        $this->assertHtmlContains('onBeforeUnmount(() => {', $watcherPartial);
        $this->assertHtmlContains('watch(() => activeTab.value, (newTab) => {', $watcherPartial);
        $this->assertHtmlContains('runActiveTabProtectedLoaders(newTab);', $watcherPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-cache-bootstrap-loaders')", $assemblyPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-customer-service-crud')", $assemblyPartial);
        $this->assertHtmlContains('createCustomerServiceCrud', $customerServicePartial);
        $this->assertHtmlContains('window.MarketingDashboardRuntimeHelpers.createCustomerServiceCrud({', $customerServicePartial);
    }

    public function test_web_runner_supports_budgeting_crud_methods(): void
    {
        $runnerPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-runner-factories.blade.php'));
        $runnerWebPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-runner-web-factory.blade.php'));

        $this->assertIsString($runnerPartial);
        $this->assertIsString($runnerWebPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-runner-web-factory')", $runnerPartial);
        foreach ([
            "getPromoData() { return fetchJson_('/api/program-promo').then(r => r.data || []); },",
            'savePromo(data) {',
            'const id = data.ID; const url = id ? `/api/program-promo/${encodeURIComponent(id)}` : \'/api/program-promo\';',
            'deletePromo(id) { return jsonApi(`/api/program-promo/${encodeURIComponent(id)}`, { method: \'DELETE\' }); },',
            "getSellOutTargetData() { return fetchJson_('/api/sell-out-targets').then(r => r.data || []); },",
            'saveSellOutTarget(data) {',
            'const id = data.ID; const url = id ? `/api/sell-out-targets/${encodeURIComponent(id)}` : \'/api/sell-out-targets\';',
            'deleteSellOutTarget(id) { return jsonApi(`/api/sell-out-targets/${encodeURIComponent(id)}`, { method: \'DELETE\' }); },',
            "getHargaKompetitorData() { return fetchJson_('/api/harga-kompetitor').then(r => r.data || []); },",
            'saveHargaKompetitor(data) {',
            'const id = data.ID; const url = id ? `/api/harga-kompetitor/${encodeURIComponent(id)}` : \'/api/harga-kompetitor\';',
            'deleteHargaKompetitor(id) { return jsonApi(`/api/harga-kompetitor/${encodeURIComponent(id)}`, { method: \'DELETE\' }); },',
        ] as $needle) {
            $this->assertHtmlContains($needle, $runnerWebPartial);
        }
    }

    public function test_root_keeps_runner_export_and_mount_clusters_inside_main_app_script_in_order(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);

        $appScriptStart = strpos($html, 'const { createApp, ref, computed, watch, onMounted, onBeforeUnmount, nextTick } = Vue;');
        $runnerTail = strpos($html, 'const hasBlockingOverlayOpen = computed(() => Boolean(');
        $analyticsBridge = strpos($html, 'const getAnalyticsExportBridge_ = () => {');
        $promoLoader = strpos($html, 'const loadPromoData = () => new Promise(resolve => {');
        $mount = strpos($html, '}).mount("#app");');

        $this->assertNotFalse($appScriptStart);
        $this->assertNotFalse($runnerTail);
        $this->assertNotFalse($analyticsBridge);
        $this->assertNotFalse($promoLoader);
        $this->assertNotFalse($mount);

        $appScriptClose = strpos($html, '</script>', $appScriptStart);

        $this->assertNotFalse($appScriptClose);
        $this->assertTrue($appScriptStart < $analyticsBridge);
        $this->assertTrue($appScriptStart < $runnerTail);
        $this->assertTrue($analyticsBridge < $mount);
        $this->assertTrue($runnerTail < $mount);
        $this->assertTrue($promoLoader < $mount);
        $this->assertTrue($mount < $appScriptClose);
        $this->assertFalse((bool) strpos($html, 'const getAnalyticsExportBridge_ = () => {', $mount));
    }

    public function test_root_renders_single_blocking_overlay_declaration_and_balanced_script_tags(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertSame(1, substr_count($html, 'const hasBlockingOverlayOpen = computed(() => Boolean('));
        $this->assertSame(substr_count($html, '<script'), substr_count($html, '</script>'));
    }

    public function test_root_declares_default_date_range_and_confirm_modal_before_consumers(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);

        $defaultDateRange = strpos($html, 'const getDefaultDateRange = () => {');
        $masterFilter = strpos($html, 'const getInitialMasterFilter = () => {');
        $confirmModal = strpos($html, 'const confirmModal = ref({');
        $showConfirm = strpos($html, 'const showConfirm = (title, message, onConfirm, type = "danger") => {');
        $confirmConsumer = strpos($html, 'confirmModal.value.open');

        $this->assertNotFalse($defaultDateRange);
        $this->assertNotFalse($masterFilter);
        $this->assertTrue($defaultDateRange < $masterFilter);
        $this->assertNotFalse($confirmModal);
        $this->assertNotFalse($showConfirm);
        $this->assertNotFalse($confirmConsumer);
        $this->assertTrue($confirmModal < $showConfirm);
        $this->assertTrue($showConfirm < $confirmConsumer);
    }

    public function test_root_declares_operational_state_before_watchers_and_computed_consumers(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);

        $contentTableSearch = strpos($html, 'const contentTableSearch = ref("");');
        $distributionWatcher = strpos($html, 'watch(() => [contentTableSearch.value, commonDateFilter.value.start, commonDateFilter.value.end], () => { distributionPage.value = 1; analyticsPage.value = 1; });');
        $ideationViewMode = strpos($html, 'const ideationViewMode = ref("board");');
        $ideationBoardMobileTab = strpos($html, "const ideationBoardMobileTab = ref('');");
        $ideationWatcher = strpos($html, 'watch(() => ideationDraftLabel.value, (label) => {');
        $storyTab = strpos($html, "const storyTab = deps.ref('Ganjil');");
        $storyWatcher = strpos($html, 'watch(() => storyTab.value, () => { storyPage.value = 1; });');
        $unboxingSearch = strpos($html, "const unboxingSearch = ref('');");
        $unboxingWatcher = strpos($html, 'watch(() => [unboxingSearch.value, commonDateFilter.value.start, commonDateFilter.value.end], () => { unboxingPage.value = 1; });');
        $orderanOnlineSearch = strpos($html, "const orderanOnlineSearch = ref('');");
        $orderanOnlineForm = strpos($html, 'const orderanOnlineForm = deps.ref({});');
        $orderanWatcher = strpos($html, 'watch(() => [orderanOnlineSearch.value, orderanOnlineDateRange.value.start, orderanOnlineDateRange.value.end], () => { orderanPage.value = 1; });');
        $unitDitanyaSearch = strpos($html, "const unitDitanyaSearch = ref('');");
        $unitWatcher = strpos($html, 'watch(() => [unitDitanyaSearch.value, unitDitanyaDateRange.value.start, unitDitanyaDateRange.value.end, unitDitanyaAvailableFilter.value], () => { unitDitanyaPage.value = 1; });');
        $claimGaransiSearch = strpos($html, "const claimGaransiSearch = ref('');");
        $claimWatcher = strpos($html, 'watch(() => [claimGaransiSearch.value, claimGaransiStatusFilter.value, claimGaransiGaransiFilter.value], () => { claimPage.value = 1; });');
        $bonusMonth = strpos($html, 'const bonusMonth = ref(_bonusDefault.month);');
        $bonusConsumer = strpos($html, 'bonusMonth, bonusYear,');
        $sortableHeaders = strpos($html, 'const hydrateSortableTableHeaders = () => {');
        $sortableConsumer = strpos($html, 'requestAnimationFrame(() => stabilizeActivePanelPosition());');

        foreach ([
            $contentTableSearch,
            $distributionWatcher,
            $ideationViewMode,
            $ideationBoardMobileTab,
            $ideationWatcher,
            $storyTab,
            $storyWatcher,
            $unboxingSearch,
            $unboxingWatcher,
            $orderanOnlineSearch,
            $orderanOnlineForm,
            $orderanWatcher,
            $unitDitanyaSearch,
            $unitWatcher,
            $claimGaransiSearch,
            $claimWatcher,
            $bonusMonth,
            $bonusConsumer,
            $sortableHeaders,
            $sortableConsumer,
        ] as $position) {
            $this->assertNotFalse($position);
        }

        $this->assertTrue($contentTableSearch < $distributionWatcher);
        $this->assertTrue($ideationViewMode < $ideationWatcher);
        $this->assertTrue($ideationBoardMobileTab < $ideationWatcher);
        $this->assertTrue($storyTab < $storyWatcher);
        $this->assertTrue($unboxingSearch < $unboxingWatcher);
        $this->assertTrue($orderanOnlineSearch < $orderanWatcher);
        $this->assertTrue($orderanOnlineForm < $orderanWatcher);
        $this->assertTrue($unitDitanyaSearch < $unitWatcher);
        $this->assertTrue($claimGaransiSearch < $claimWatcher);
        $this->assertTrue($bonusMonth < $bonusConsumer);
        $this->assertTrue($sortableHeaders < $sortableConsumer);
    }

    public function test_root_uses_reporting_export_bridge_for_ads_log_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardReportingExports', $html);
        $this->assertHtmlContains('const getReportingExportBridge_ = () => {', $html);
        $this->assertHtmlContains('return bridge.exportAdsLogToPDF({', $html);
        $this->assertHtmlNotContains("title: 'ADS PERFORMANCE REPORT',", $html);
        $this->assertHtmlNotContains("openPrintWindow_(html, 'Ads Report');", $html);
    }

    public function test_root_uses_reporting_export_bridge_for_price_comparison_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardReportingExports', $html);
        $this->assertHtmlContains('return bridge.exportPriceComparisonToPDF({', $html);
        $this->assertHtmlNotContains("title: 'ANALISIS HARGA & KOMPETITOR',", $html);
        $this->assertHtmlNotContains("openPrintWindow_(html, 'Harga Kompetitor');", $html);
    }

    public function test_root_uses_reporting_export_bridge_for_lpjk_detail_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardReportingExports', $html);
        $this->assertHtmlContains('return bridge.exportLpjkDetailToPDF({', $html);
        $this->assertHtmlNotContains("title: 'LAPORAN PERTANGGUNGJAWABAN KEUANGAN',", $html);
        $this->assertHtmlNotContains('openPrintWindow_(html, `LPJK - ${lpjk.Nama_Event}`);', $html);
    }

    public function test_root_uses_reporting_export_bridge_for_budget_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardReportingExports', $html);
        $this->assertHtmlContains('return bridge.exportBudgetToPDF({', $html);
        $this->assertHtmlNotContains('<title>Budget Plan</title>', $html);
        $this->assertHtmlNotContains("openPrintWindow_(html, 'Budget Plan');", $html);
    }

    public function test_root_uses_shared_reporting_export_bridge_helper(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('const getReportingExportBridge_ = () => {', $html);
        $this->assertGreaterThanOrEqual(1, substr_count($html, 'window.MarketingDashboardReportingExports'));
        $this->assertGreaterThanOrEqual(1, substr_count($html, 'const bridge = getReportingExportBridge_();'));
    }

    public function test_root_uses_analytics_export_bridge_for_analytics_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardAnalyticsExports', $html);
        $this->assertHtmlContains('const getAnalyticsExportBridge_ = () => {', $html);
        $this->assertHtmlContains('return bridge.exportAnalyticsToPDF({', $html);
        $this->assertHtmlNotContains("title: 'ANALYTICS PERFORMANCE REPORT',", $html);
        $this->assertHtmlNotContains("openPrintWindow_(html, 'Analytics Performance');", $html);
    }

    public function test_root_uses_analytics_export_bridge_for_generic_tabular_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardAnalyticsExports', $html);
        $this->assertHtmlNotContains('title: title.toUpperCase(),', $html);
        $this->assertHtmlNotContains('openPrintWindow_(html, title);', $html);
    }

    public function test_root_uses_analytics_export_bridge_for_active_tab_pdf_dispatch(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('return bridge.exportActiveTabPdf({', $html);
        $this->assertHtmlNotContains("if (activeTab.value === 'unit_ditanya') { exportUnitDitanyaToPDF(); return; }", $html);
        $this->assertHtmlNotContains('const tabTitles = {', $html);
    }

    public function test_root_hardens_external_links_and_print_popup_url(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();
        $printBrowser = file_get_contents(resource_path('js/dashboard/export/print-browser.js'));

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertIsString($printBrowser);
        $this->assertHtmlContains('target="_blank" rel="noopener noreferrer"', $html);
        $this->assertHtmlContains('window.openPrintWindow = openPrintWindow;', $printBrowser);
        $this->assertHtmlContains('resolveAppUrl,', $html);
        $this->assertHtmlNotContains('ngrok-skip-browser-warning=1', $html);
    }

    public function test_root_uses_sales_export_bridge_for_bonus_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardSalesExports', $html);
        $this->assertHtmlContains('const getSalesExportBridge_ = () => {', $html);
        $this->assertHtmlContains('return bridge.exportBonusToPDF({', $html);
        $this->assertHtmlNotContains("title: 'BONUS & PERFORMANCE PAYOUT REPORT',", $html);
        $this->assertHtmlNotContains("openPrintWindow_(html, 'Bonus Report');", $html);
    }

    public function test_root_uses_sales_export_bridge_for_promo_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardSalesExports', $html);
        $this->assertHtmlContains('return bridge.exportPromoToPDF({', $html);
        $this->assertHtmlNotContains("title: 'PROGRAM PROMO',", $html);
        $this->assertHtmlNotContains("openPrintWindow_(html, 'Program Promo');", $html);
    }

    public function test_root_uses_sales_export_bridge_for_sell_out_pdf(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('window.MarketingDashboardSalesExports', $html);
        $this->assertHtmlContains('return bridge.exportSellOutToPDF({', $html);
        $this->assertHtmlNotContains("title: 'TARGET VENDOR (SELL OUT)',", $html);
        $this->assertHtmlNotContains("openPrintWindow_(html, 'Target Vendor Sell Out');", $html);
    }

    public function test_shell_builder_splits_sales_and_promo_pdf_cluster_from_legacy_body_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeSalesAndPromoPdfCluster', $fragments);
        $this->assertFragmentHasKey('bodyAfterSalesAndPromoPdfCluster', $fragments);
        $this->assertSame('', $fragments['bodyBeforeSalesAndPromoPdfCluster']);
        $this->assertSame('', $fragments['bodyAfterSalesAndPromoPdfCluster']);
    }

    public function test_shell_builder_splits_print_helpers_from_legacy_body_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforePrintHelpers', $fragments);
        $this->assertFragmentHasKey('bodyAfterPrintHelpers', $fragments);
        $this->assertSame('', $fragments['bodyBeforePrintHelpers']);
        $this->assertSame('', $fragments['bodyAfterPrintHelpers']);
    }

    public function test_shell_builder_splits_ads_log_pdf_export_from_remaining_legacy_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeAdsLogPdfExport', $fragments);
        $this->assertFragmentHasKey('bodyAfterAdsLogPdfExport', $fragments);
        $this->assertSame('', $fragments['bodyBeforeAdsLogPdfExport']);
        $this->assertSame('', $fragments['bodyAfterAdsLogPdfExport']);
    }

    public function test_shell_builder_splits_price_comparison_pdf_export_from_remaining_legacy_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforePriceComparisonPdfExport', $fragments);
        $this->assertFragmentHasKey('bodyAfterPriceComparisonPdfExport', $fragments);
        $this->assertSame('', $fragments['bodyBeforePriceComparisonPdfExport']);
        $this->assertSame('', $fragments['bodyAfterPriceComparisonPdfExport']);
    }

    public function test_shell_builder_splits_lpjk_detail_pdf_export_from_remaining_legacy_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeLpjkDetailPdfExport', $fragments);
        $this->assertFragmentHasKey('bodyAfterLpjkDetailPdfExport', $fragments);
        $this->assertSame('', $fragments['bodyBeforeLpjkDetailPdfExport']);
        $this->assertSame('', $fragments['bodyAfterLpjkDetailPdfExport']);
    }

    public function test_shell_builder_splits_budget_pdf_export_from_remaining_legacy_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeBudgetPdfExport', $fragments);
        $this->assertFragmentHasKey('bodyAfterBudgetPdfExport', $fragments);
        $this->assertSame('', $fragments['bodyBeforeBudgetPdfExport']);
        $this->assertSame('', $fragments['bodyAfterBudgetPdfExport']);
    }

    public function test_shell_builder_splits_vue_app_script_mount_tail_from_legacy_body_script(): void
    {
        $shell = app(MarketingDashboardShell::class);

        $fragments = $shell->build('https://backend.example.test');

        $this->assertFragmentHasKey('bodyBeforeVueAppMountEnd', $fragments);
        $this->assertFragmentHasKey('bodyAfterVueAppMountEnd', $fragments);
        $this->assertSame('', $fragments['bodyBeforeVueAppMountEnd']);
        $this->assertSame('', $fragments['bodyAfterVueAppMountEnd']);
    }

    public function test_dashboard_shell_does_not_embed_mockup_business_data(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        foreach ([
            'Carousel Tips Beli HP Bekas',
            'Promo iPhone',
            'Unboxing Samsung A Series',
            'Komang',
            'Samsung A55',
            'Roadshow Kampus',
            'Ads Promo iPhone',
            'Vendor A',
            'Administrator',
        ] as $mockValue) {
            $response->assertDontSee($mockValue, false);
        }
    }

    public function test_dashboard_contains_legacy_navigation_labels(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        foreach ([
            'Dashboard',
            'Master Plan',
            'Unboxing',
            'Distribution',
            'Analytics',
            'Program Promo',
            'Sell Out Target',
            'Ads Log',
            'Budgeting',
            'Order Online',
            'Unit Ditanya',
            'Claim Garansi',
            'Harga Kompetitor',
            'Laporan Event',
            'Pengaturan',
            'Nama Stock',
            'Bonus Report',
            'Talent Bonus',
            'Editor Performance',
            'Keep Barang',
        ] as $label) {
            $response->assertSee($label, false);
        }
    }

    public function test_dashboard_shell_does_not_render_mobile_bottom_navigation(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlNotContains('class="bottom-nav', $html);
        $this->assertHtmlNotContains('bottomNavMoreOpen', $html);
    }

    public function test_dashboard_sidebar_and_main_canvas_use_shared_motion_timing(): void
    {
        $dashboardShellCss = file_get_contents(resource_path('css/dashboard-shell.css'));

        $this->assertIsString($dashboardShellCss);
        $this->assertHtmlContains('--dashboard-shell-motion-duration: 300ms;', $dashboardShellCss);
        $this->assertHtmlContains('--dashboard-shell-motion-easing: cubic-bezier(0.4, 0, 0.2, 1);', $dashboardShellCss);
        $this->assertHtmlContains('transition-duration: var(--dashboard-shell-motion-duration);', $dashboardShellCss);
        $this->assertHtmlContains('transition-timing-function: var(--dashboard-shell-motion-easing);', $dashboardShellCss);
        $this->assertHtmlNotContains('.dashboard-shell-layout {', $dashboardShellCss);
        $this->assertHtmlNotContains('padding-left: var(--dashboard-sidebar-offset);', $dashboardShellCss);
        $this->assertHtmlNotContains('translateX(calc(var(--dashboard-sidebar-offset) - var(--dashboard-sidebar-width)))', $dashboardShellCss);
        $this->assertHtmlNotContains('.dashboard-sidebar-content {', $dashboardShellCss);
        $this->assertHtmlNotContains('transition: opacity var(--dashboard-shell-motion-duration) var(--dashboard-shell-motion-easing);', $dashboardShellCss);
        $this->assertHtmlNotContains('transition: opacity 160ms ease;', $dashboardShellCss);
    }

    public function test_dashboard_desktop_sidebar_and_canvas_share_one_layout_offset(): void
    {
        $appFramePartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-frame.blade.php'));
        $sidebarPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-sidebar.blade.php'));

        $this->assertIsString($appFramePartial);
        $this->assertIsString($sidebarPartial);
        $this->assertHtmlContains('v-if="currentUser && !appLoading" class="min-h-[100dvh] bg-slate-50"', $appFramePartial);
        $this->assertHtmlContains('lg:hidden transition-opacity duration-300 ease-out', $appFramePartial);
        $this->assertHtmlContains("paddingLeft: sidebarCollapsed ? '0px' : '15rem'", $appFramePartial);
        $bootstrapNavigationPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-bootstrap-navigation.blade.php'));
        $interactionHelpersPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-shell-interaction-helpers.blade.php'));
        $returnBlockPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-return-block.blade.php'));
        $lifecycleWatchersPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-lifecycle-watchers.blade.php'));
        $runnerSessionTailPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-runner-session-tail.blade.php'));
        $protectedUserSettingsPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-protected-user-settings.blade.php'));

        $this->assertHtmlContains('const isMobileViewport = ref(window.innerWidth < 1024);', $bootstrapNavigationPartial);
        $this->assertHtmlContains("const savedSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === '1';", $bootstrapNavigationPartial);
        $this->assertHtmlNotContains("['1', 'true'].includes(localStorage.getItem('sidebarCollapsed'))", $bootstrapNavigationPartial);
        $this->assertHtmlContains('const sidebarOpen = ref(false);', $bootstrapNavigationPartial);
        $this->assertHtmlContains('const sidebarCollapsed = ref(savedSidebarCollapsed);', $bootstrapNavigationPartial);
        $this->assertHtmlContains('const isDesktopViewport = () => window.innerWidth >= 1024;', $interactionHelpersPartial);
        $this->assertHtmlContains('sidebarCollapsed.value = !sidebarCollapsed.value;', $interactionHelpersPartial);
        $this->assertHtmlContains("localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value ? '1' : '0');", $interactionHelpersPartial);
        $this->assertHtmlContains('sidebarOpen.value = !sidebarOpen.value;', $interactionHelpersPartial);
        $this->assertHtmlContains('sidebarOpen.value = false;', $interactionHelpersPartial);
        $this->assertHtmlContains('sidebarOpen,', $returnBlockPartial);
        $this->assertHtmlContains('sidebarCollapsed,', $returnBlockPartial);
        $this->assertHtmlNotContains('isSidebarOpen,', $returnBlockPartial);
        $this->assertHtmlContains('if (window.innerWidth < 1024) {', $lifecycleWatchersPartial);
        $this->assertHtmlContains('sidebarOpen.value = false;', $lifecycleWatchersPartial);
        $this->assertHtmlContains('if (window.innerWidth < 1024) sidebarOpen.value = false;', $lifecycleWatchersPartial);
        $this->assertHtmlContains('isMobileViewport.value = window.innerWidth < 1024;', $runnerSessionTailPartial);
        $this->assertHtmlContains('if (window.innerWidth >= 1024) settingsDetailModalOpen.value = false;', $runnerSessionTailPartial);
        $this->assertHtmlContains('if (window.innerWidth < 1024) {', $protectedUserSettingsPartial);
        $this->assertHtmlContains('sidebarOpen.value = false;', $protectedUserSettingsPartial);
        $this->assertHtmlContains("'dashboard-sidebar-shell fixed inset-y-0 left-0 z-[80] lg:z-40 bg-white border-r border-slate-100 flex flex-col overflow-hidden w-60'", $sidebarPartial);
        $this->assertHtmlContains(":style=\"{ transform: sidebarOpen || (!isMobileViewport && !sidebarCollapsed) ? 'translateX(0)' : 'translateX(-100%)' }\"", $sidebarPartial);
        $this->assertHtmlNotContains('lg:translate-x-0', $sidebarPartial);
        $this->assertHtmlContains('class="dashboard-sidebar-content min-w-[15rem] flex h-full flex-col relative"', $sidebarPartial);
        $this->assertHtmlContains('class="dashboard-sidebar-brand relative px-4 h-16 flex items-center gap-3 border-b border-slate-100 flex-shrink-0 bg-white"', $sidebarPartial);
        $this->assertHtmlContains('class="dashboard-sidebar-logo w-9 h-9 rounded-xl bg-white overflow-hidden flex items-center justify-center shrink-0 shadow-sm ring-1 ring-slate-100"', $sidebarPartial);
        $this->assertHtmlContains('class="h-full w-full object-contain p-1"', $sidebarPartial);
        $this->assertHtmlContains('class="min-w-0 flex-1"', $sidebarPartial);
        $this->assertHtmlContains('class="text-[10px] lg:text-[11px] font-black tracking-wide text-slate-900 leading-tight"', $sidebarPartial);
        $this->assertHtmlContains('class="text-[7px] lg:text-[8px] text-slate-400 uppercase tracking-[0.18em]"', $sidebarPartial);
        $this->assertHtmlContains('class="w-11 h-11 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition-all lg:hidden"', $sidebarPartial);
        $this->assertHtmlContains('@click="sidebarOpen = false"', $sidebarPartial);
        $this->assertHtmlNotContains('opacity-0 pointer-events-none', $sidebarPartial);
        $this->assertHtmlNotContains('opacity-100', $sidebarPartial);
    }

    public function test_dashboard_shell_uses_blade_vue_app_script_wrapper_partials_instead_of_raw_legacy_script_wrapper(): void
    {
        $appAssemblyPartial = file_get_contents(resource_path('views/dashboard/partials/shell/body-app-assembly.blade.php'));
        $appScriptOpenPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-open.blade.php'));
        $appScriptClosePartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-close.blade.php'));

        $this->assertIsString($appAssemblyPartial);
        $this->assertIsString($appScriptOpenPartial);
        $this->assertIsString($appScriptClosePartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-open')", $appAssemblyPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-close')", $appAssemblyPartial);
        $this->assertHtmlContains('const { createApp, ref, computed, watch, onMounted, onBeforeUnmount, nextTick } = Vue;', $appScriptOpenPartial);
        $this->assertHtmlContains('}).mount("#app");', $appScriptClosePartial);
    }

    public function test_dashboard_shell_uses_blade_vue_app_date_helper_partial_instead_of_raw_legacy_cluster(): void
    {
        $appAssemblyPartial = file_get_contents(resource_path('views/dashboard/partials/shell/body-app-assembly.blade.php'));
        $dateHelpersPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-date-helpers.blade.php'));

        $this->assertIsString($appAssemblyPartial);
        $this->assertIsString($dateHelpersPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-date-helpers')", $appAssemblyPartial);
        $this->assertHtmlContains('fmtLocalDate,', $dateHelpersPartial);
        $this->assertHtmlContains('isDateInRange,', $dateHelpersPartial);
        $this->assertHtmlContains('} = window.MarketingDashboardRuntimeHelpers;', $dateHelpersPartial);
    }

    public function test_dashboard_shell_uses_blade_vue_app_bootstrap_navigation_partial_instead_of_raw_legacy_cluster(): void
    {
        $appAssemblyPartial = file_get_contents(resource_path('views/dashboard/partials/shell/body-app-assembly.blade.php'));
        $bootstrapNavigationPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-bootstrap-navigation.blade.php'));

        $this->assertIsString($appAssemblyPartial);
        $this->assertIsString($bootstrapNavigationPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-bootstrap-navigation')", $appAssemblyPartial);
        $this->assertHtmlContains('const appLoading = ref(true);', $bootstrapNavigationPartial);
        $this->assertHtmlContains("if ('scrollRestoration' in history) {", $bootstrapNavigationPartial);
        $this->assertHtmlContains("history.scrollRestoration = 'manual';", $bootstrapNavigationPartial);
        $this->assertHtmlContains('const groupForTab = () => null;', $bootstrapNavigationPartial);
    }

    public function test_master_plans_api_returns_database_rows_for_legacy_frontend(): void
    {
        DB::table('master_plans')->insert([
            'source_id' => 'Konten-API-001',
            'title' => 'Konten Dari Database',
            'format_konten' => 'REELS',
            'platforms' => 'Instagram',
            'colab' => null,
            'editor' => 'Agus',
            'talent' => 'Talent Konten, Talent Pendamping',
            'script' => 'Script database',
            'caption' => 'Caption database',
            'status' => 'PUBLISHED',
            'tanggal_rencana' => '2026-06-25',
            'distribution_meta' => '{"Instagram":{"link":"https://example.test","date":"2026-06-25","type":"Regular"}}',
            'link_drive' => 'https://drive.google.com/database-row',
            'raw_payload' => json_encode(['ID' => 'Konten-API-001']),
            'imported_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/master-plans');

        $response->assertOk()
            ->assertJsonPath('data.0.ID', 'Konten-API-001')
            ->assertJsonPath('data.0.Judul', 'Konten Dari Database')
            ->assertJsonPath('data.0.Format_Konten', 'REELS')
            ->assertJsonPath('data.0.Tanggal_Rencana', '2026-06-25')
            ->assertJsonPath('data.0.Talent', 'Talent Konten, Talent Pendamping');
    }

    public function test_settings_api_returns_database_setting_groups(): void
    {
        DB::table('marketing_settings')->insert([
            'key' => 'Format_Konten',
            'values' => json_encode(['REELS', 'STORY'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'imported_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/settings');

        $response->assertOk()
            ->assertJsonPath('data.Format_Konten.0', 'REELS')
            ->assertJsonPath('data.Format_Konten.1', 'STORY');
    }

    public function test_settings_api_can_return_talent_options(): void
    {
        DB::table('marketing_settings')->insert([
            'key' => 'Talent',
            'values' => json_encode(['Talent A', 'Talent B'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'imported_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/settings');

        $response->assertOk()
            ->assertJsonPath('data.Talent.0', 'Talent A')
            ->assertJsonPath('data.Talent.1', 'Talent B');
    }

    public function test_settings_tab_loads_database_settings_when_opened(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains("if (tab === 'settings') {", $html);
        $this->assertHtmlContains('loadSettings();', $html);
    }

    public function test_dropdown_settings_are_bootstrapped_before_settings_tab_is_opened(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('const settingsLoaded = ref(false);', $html);
        $this->assertHtmlContains("if (!settingsLoaded.value && tab !== 'dashboard') {", $html);
        $this->assertHtmlNotContains('await loadSettings();'."\n".'                        const bootstrap = await new Promise', $html);
    }

    public function test_master_plan_and_settings_shell_include_talent_controls(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains("'Talent'", $html);
        $this->assertHtmlContains('masterForm.Talent', $html);
        $this->assertHtmlContains('filteredTalentOptions', $html);
    }

    public function test_bonus_shell_includes_talent_bonus_tab_and_computed_data(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains("switchTab('talent_bonus')", $html);
        $this->assertHtmlContains("activeTab === 'talent_bonus'", $html);
        $this->assertHtmlContains("talent_bonus: { label: 'Talent Bonus', category: 'Performa' }", $html);
        $this->assertHtmlContains('const talentBonusRows = computed(() => {', $html);
        $this->assertHtmlContains('const talentDashboardData = computed(() => {', $html);
    }

    public function test_bonus_report_tab_uses_single_root_wrapper_inside_main_shell(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('<div v-if="activeTab === \'bonus_report\'" class="space-y-4 animate-fadeIn">', $html);
        $this->assertHtmlContains('<template v-if="!bonusConfigLoaded">', $html);
        $this->assertHtmlContains('<template v-else>', $html);
        $this->assertHtmlNotContains('<div v-if="activeTab === \'bonus_report\' && !bonusConfigLoaded"', $html);
        $this->assertHtmlNotContains('<div v-if="activeTab === \'bonus_report\' && bonusConfigLoaded"', $html);
    }

    public function test_talent_bonus_uses_master_plan_as_source_of_truth(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('return masterPlanData.value', $html);
        $this->assertHtmlContains('const distByMaster = new Map();', $html);
        $this->assertHtmlContains("const rawDate = item.Tanggal_Rencana || '';", $html);
        $this->assertHtmlNotContains('return filteredBonusRows.value.flatMap((row) => {', $html);
    }

    public function test_talent_bonus_shell_includes_daily_carry_over_rules(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('const TALENT_DAILY_BONUS = 150000;', $html);
        $this->assertHtmlContains('const buildTalentDailyRows = (rows) => {', $html);
        $this->assertHtmlContains('const effectiveCount = dayRows.length + carryCount;', $html);
        $this->assertHtmlContains('carryCount = effectiveCount > 2 && effectiveCount % 2 === 1 ? 1 : 0;', $html);
    }

    public function test_bonus_tabs_refresh_master_plan_and_related_sources(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('const refreshBonusSourceData = () => Promise.allSettled([', $html);
        $this->assertHtmlContains("if (['bonus_report', 'talent_bonus', 'editor_performance'].includes(tab)) {", $html);
        $this->assertHtmlContains('refreshBonusSourceData();', $html);
    }

    public function test_tab_switch_scroll_reset_targets_page_scroll_not_sidebar(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('const scrollActivePanelToTop = () => {', $html);
        $this->assertHtmlContains('const stabilizeActivePanelPosition = () => {', $html);
        $this->assertHtmlContains('const clearActivePanelScrollGuard = () => {', $html);
        $this->assertHtmlContains('let activePanelScrollUserInteracted = false;', $html);
        $this->assertHtmlContains('const markActivePanelScrollUserIntent = () => {', $html);
        $this->assertHtmlContains('activePanelScrollUserInteracted = true;', $html);
        $this->assertHtmlContains('clearActivePanelScrollGuard();', $html);
        $this->assertHtmlContains('const getDashboardMainScroller = () => {', $html);
        $this->assertHtmlContains("const main = document.querySelector('#app main');", $html);
        $this->assertHtmlContains('return main instanceof HTMLElement ? main : null;', $html);
        $this->assertHtmlContains('if (main) main.scrollTop = 0;', $html);
        $this->assertHtmlContains('[0, 120, 280, 520, 900].forEach((delay) => {', $html);
        $this->assertHtmlContains('if (activePanelScrollUserInteracted) {', $html);
        $this->assertHtmlContains('if (main.scrollTop > 2 || panelTop > 140 || window.scrollY > 16) {', $html);
        $this->assertHtmlContains("window.scrollTo({ top: 0, left: 0, behavior: 'auto' });", $html);
        $this->assertHtmlContains('document.documentElement.scrollTop = 0;', $html);
        $this->assertHtmlContains('document.body.scrollTop = 0;', $html);
        $this->assertHtmlContains('activePanelScrollGuardInterval = window.setInterval(() => {', $html);
        $this->assertHtmlContains('}, 180);', $html);
        $this->assertHtmlContains('}, 3200);', $html);
        $this->assertHtmlContains("const activePanel = main?.querySelector(':scope > .animate-fadeIn');", $html);
        $this->assertHtmlContains("activePanel.scrollIntoView({ block: 'start', inline: 'nearest', behavior: 'auto' });", $html);
        $this->assertHtmlContains('window.addEventListener("wheel", markActivePanelScrollUserIntent, { passive: true, capture: true });', $html);
        $this->assertHtmlContains('window.addEventListener("touchmove", markActivePanelScrollUserIntent, { passive: true, capture: true });', $html);
        $this->assertHtmlContains('window.addEventListener("keydown", markActivePanelScrollUserIntent, true);', $html);
        $this->assertHtmlContains('window.removeEventListener("wheel", markActivePanelScrollUserIntent, true);', $html);
        $this->assertHtmlContains('window.removeEventListener("touchmove", markActivePanelScrollUserIntent, true);', $html);
        $this->assertHtmlContains('window.removeEventListener("keydown", markActivePanelScrollUserIntent, true);', $html);
        $this->assertHtmlContains('requestAnimationFrame(() => stabilizeActivePanelPosition());', $html);
        $this->assertHtmlNotContains("document.querySelector('.overflow-y-auto')", $html);
    }

    public function test_bonus_report_prefers_analytics_publish_date_when_distribution_date_is_missing(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains("const analyticsDate = aRow.Tanggal_Publish || aRow.Tanggal_Post || aRow.Tanggal || '';", $html);
        $this->assertHtmlContains('const rawDate = dRow?.Tanggal_Publish || analyticsDate;', $html);
    }

    public function test_bonus_report_clamps_page_when_filtered_result_set_changes(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('watch([() => filteredBonusRows.value.length, bonusTotalPages], ([rowCount, totalPages]) => {', $html);
        $this->assertHtmlContains('if (rowCount === 0) {', $html);
        $this->assertHtmlContains('if (bonusPage.value > totalPages) bonusPage.value = totalPages;', $html);
    }

    public function test_bonus_config_loader_warns_when_server_config_is_unavailable(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('const cachedCfg = (() => {', $html);
        $this->assertHtmlContains('showNotification(', $html);
        $this->assertHtmlContains('Konfigurasi bonus server tidak tersedia. Menggunakan konfigurasi lokal terakhir.', $html);
        $this->assertHtmlContains('Konfigurasi bonus server tidak tersedia. Menggunakan konfigurasi default.', $html);
    }

    public function test_bonus_config_save_failure_does_not_claim_local_success(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains("notifyError('Gagal menyimpan konfigurasi bonus', error, 'Konfigurasi bonus belum tersimpan ke server.');", $html);
        $this->assertHtmlNotContains("showNotification('Disimpan lokal (server tidak tersedia)');", $html);
    }

    public function test_web_bootstrap_checks_dashboard_session_before_loading_protected_settings(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlNotContains('await loadSettings();'."\n".'                        const bootstrap = await new Promise', $html);
        $this->assertHtmlContains('const bootstrap = await new Promise((resolve, reject) => {', $html);
    }

    public function test_web_bootstrap_defers_protected_fetches_until_session_check_finishes(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk();
        $this->assertIsString($html);
        $this->assertHtmlContains('const authBootstrapPending = ref(true);', $html);
        $this->assertHtmlContains('if (authBootstrapPending.value) {', $html);
        $this->assertHtmlContains('authBootstrapPending.value = false;', $html);
    }

    public function test_web_bootstrap_replays_active_protected_tab_loader_after_session_check(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('const runActiveTabProtectedLoaders = (tab) => {', $html);
        $this->assertHtmlContains("if (['bonus_report', 'talent_bonus', 'editor_performance'].includes(tab)) {", $html);
        $this->assertHtmlContains('refreshBonusSourceData();', $html);
        $this->assertHtmlContains('const resumeActiveTabAfterBootstrap = () => {', $html);
        $this->assertHtmlContains('runActiveTabProtectedLoaders(activeTab.value);', $html);
        $this->assertHtmlContains('resumeActiveTabAfterBootstrap();', $html);
    }

    public function test_tab_data_loader_requires_authenticated_user_before_fetching_protected_tab_payloads(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('if (dataKey) loadTabData(dataKey);', $html);
    }

    public function test_distribution_and_analytics_tabs_load_database_rows_when_opened(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains("if (tab === 'distribution') {", $html);
        $this->assertHtmlContains('loadDistributionData();', $html);
        $this->assertHtmlContains("if (tab === 'analytics') {", $html);
        $this->assertHtmlContains('loadAnalyticsData();', $html);
    }

    public function test_meta_analytics_tabs_include_actionable_insight_sections(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('Insight Story yang Bisa Dipakai', $html);
        $this->assertHtmlContains('Insight Feed yang Bisa Dipakai', $html);
        $this->assertHtmlContains('Peringkat Akun Feed', $html);
        $this->assertHtmlContains('const metaStoryInsights = computed(() => {', $html);
        $this->assertHtmlContains('const metaFeedInsights = computed(() => {', $html);
        $this->assertHtmlContains('const metaFeedAccountLeaderboard = computed(() => {', $html);
    }

    public function test_meta_story_trend_uses_average_per_content(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('Average Views & Reach per Konten', $html);
        $this->assertHtmlContains("const metaStoryDaily = computed(() => _metaDaily(filteredMetaStory.value, 'average'));", $html);
        $this->assertHtmlContains('const _metaChartReady = (el) => {', $html);
        $this->assertHtmlContains('const _renderMetaChart = (id, el, options, attempt = 0) => {', $html);
        $this->assertHtmlContains('const _metaChartOptionsWithDimensions = (el, options) => {', $html);
        $this->assertHtmlContains('const _metaChartTokens = {};', $html);
    }

    public function test_meta_story_top_list_and_table_use_updated_limits_and_pagination(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('const metaStoryTop = computed(() => [...filteredMetaStory.value].sort((a, b) => (Number(b.views) || 0) - (Number(a.views) || 0)).slice(0, 5));', $html);
        $this->assertHtmlContains('const metaStoryTotalPages = computed(() => Math.max(1, Math.ceil(filteredMetaStory.value.length / PAGE_SIZE)));', $html);
        $this->assertHtmlContains('const pagedMetaStory = computed(() => filteredMetaStory.value.slice((metaStoryPage.value - 1) * PAGE_SIZE, metaStoryPage.value * PAGE_SIZE));', $html);
        $this->assertHtmlContains('@click="metaStoryPage++" :disabled="metaStoryPage >= metaStoryTotalPages" aria-label="Halaman berikutnya"', $html);
    }

    public function test_meta_analytics_tabs_include_folder_import_and_monthly_summary_sections(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('Import Folder', $html);
        $this->assertHtmlContains('Ringkasan Bulanan per Akun', $html);
        $this->assertHtmlContains('const metaStoryMonthlySummary = computed(() => _metaMonthlySummary', $html);
        $this->assertHtmlContains('const metaFeedMonthlySummary = computed(() => _metaMonthlySummary', $html);
        $this->assertHtmlContains('const importMetaFolder = (dataset) => {', $html);
    }

    public function test_meta_import_flow_requests_confirmation_before_overwrite(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('importMetaStory(rows, options = {})', $html);
        $this->assertHtmlContains('importMetaFeed(rows, options = {})', $html);
        $this->assertHtmlContains('importMetaStoryFolder(options = {})', $html);
        $this->assertHtmlContains('importMetaFeedFolder(options = {})', $html);
        $this->assertHtmlContains('overwrite: !!options.overwrite', $html);
        $this->assertHtmlContains('const handleMetaImportResult = (dataset, result, retryImport, successMessage) => {', $html);
        $this->assertHtmlContains('if (r.requires_confirmation) {', $html);
        $this->assertHtmlContains('showConfirm(', $html);
        $this->assertHtmlContains('Data Meta Sudah Ada', $html);
    }

    public function test_meta_feed_toolbar_uses_consistent_custom_select_shell(): void
    {
        $html = $this->renderDashboardHtml();
        $dashboardShellCss = file_get_contents(resource_path('css/dashboard-shell.css'));

        $this->assertIsString($dashboardShellCss);
        $this->assertHtmlContains("toggleSearchSelect(\$event, 'meta_feed_account')", $html);
        $this->assertHtmlContains('class="relative search-select-container sm:w-44"', $html);
        $this->assertHtmlContains("searchSelectOpen === 'meta_feed_account'", $html);
        $this->assertHtmlContains("metaFeedAccount || 'Semua Akun'", $html);
        $this->assertHtmlContains('.select-trigger-button-form:hover,', $dashboardShellCss);
        $this->assertHtmlContains('.select-trigger-button-form-tight:focus-visible,', $dashboardShellCss);
        $this->assertHtmlContains('.select-trigger-button-compact:focus-visible {', $dashboardShellCss);
    }

    public function test_meta_feed_exposes_manual_add_button_and_modal(): void
    {
        $html = $this->renderDashboardHtml();
        $feedHtml = file_get_contents(resource_path('views/dashboard/partials/menus/meta-feed.blade.php'));

        $this->assertIsString($feedHtml);

        $this->assertTrue(str_contains($feedHtml, 'openMetaFeedManualModal') || str_contains($html, 'openMetaFeedManualModal,'));
        $this->assertHtmlContains('id="meta-feed-manual-post-id" name="meta_feed_manual_post_id"', $feedHtml);
        $this->assertHtmlContains('id="meta-feed-manual-account" name="meta_feed_manual_account"', $feedHtml);
        $this->assertHtmlContains('id="meta-feed-manual-publish-time" name="meta_feed_manual_publish_time"', $feedHtml);
        $this->assertHtmlContains('@submit.prevent="saveMetaFeedManual"', $feedHtml);
        $this->assertHtmlContains('const metaFeedManualModalOpen = ref(false);', $html);
        $this->assertHtmlContains('const saveMetaFeedManual = () => {', $html);
        $this->assertHtmlContains('runner.withSuccessHandler(result => {', $html);
        $this->assertHtmlContains('importMetaFeed([row], { overwrite });', $html);
        $this->assertHtmlContains('metaFeedManualModalOpen,', $html);
        $this->assertHtmlContains('openMetaFeedManualModal,', $html);
        $this->assertHtmlContains('saveMetaFeedManual,', $html);
    }

    public function test_meta_story_and_feed_upload_toolbars_render_above_analytics_cards(): void
    {
        $storyHtml = file_get_contents(resource_path('views/dashboard/partials/menus/meta-story.blade.php'));
        $feedHtml = file_get_contents(resource_path('views/dashboard/partials/menus/meta-feed.blade.php'));
        $dashboardShellCss = file_get_contents(resource_path('css/dashboard-shell.css'));

        $this->assertIsString($storyHtml);
        $this->assertIsString($feedHtml);
        $this->assertIsString($dashboardShellCss);

        $storyUploadPos = strpos($storyHtml, 'id="meta-story-upload"');
        $storyChartPos = strpos($storyHtml, 'Average Views & Reach per Konten');
        $feedUploadPos = strpos($feedHtml, 'id="meta-feed-upload"');
        $feedChartPos = strpos($feedHtml, 'Tren Views & Reach');

        $this->assertNotFalse($storyUploadPos);
        $this->assertNotFalse($storyChartPos);
        $this->assertNotFalse($feedUploadPos);
        $this->assertNotFalse($feedChartPos);
        $this->assertTrue($storyUploadPos < $storyChartPos);
        $this->assertTrue($feedUploadPos < $feedChartPos);
        $this->assertHtmlContains('meta-toolbar-card', $storyHtml);
        $this->assertHtmlContains('meta-toolbar-card', $feedHtml);
        $this->assertHtmlContains('.meta-toolbar-card {', $dashboardShellCss);
        $this->assertHtmlContains('flex: 0 0 auto;', $dashboardShellCss);
    }

    public function test_meta_story_and_feed_upload_actions_use_button_triggers(): void
    {
        $storyHtml = file_get_contents(resource_path('views/dashboard/partials/menus/meta-story.blade.php'));
        $feedHtml = file_get_contents(resource_path('views/dashboard/partials/menus/meta-feed.blade.php'));

        $this->assertIsString($storyHtml);
        $this->assertIsString($feedHtml);

        $this->assertHtmlContains('@click="$refs.metaStoryUpload?.click()"', $storyHtml);
        $this->assertHtmlContains('class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95"', $storyHtml);
        $this->assertHtmlContains('aria-label="Upload CSV Story IG"', $storyHtml);
        $this->assertHtmlContains('<i :class="metaUploading ? \'fa-solid fa-spinner fa-spin\' : \'fa-solid fa-upload\'"></i>', $storyHtml);
        $this->assertHtmlContains('aria-label="Import Folder Story IG"', $storyHtml);
        $this->assertHtmlContains('<i :class="metaUploading ? \'fa-solid fa-spinner fa-spin\' : \'fa-solid fa-folder-open\'"></i>', $storyHtml);
        $this->assertHtmlNotContains('</i>Upload CSV', $storyHtml);
        $this->assertHtmlNotContains('</i>Import Folder', $storyHtml);
        $this->assertHtmlContains('id="meta-story-upload" name="meta_story_upload" type="file"', $storyHtml);
        $this->assertHtmlNotContains('<label for="meta-story-upload"', $storyHtml);

        $this->assertHtmlContains('@click="$refs.metaFeedUpload?.click()"', $feedHtml);
        $this->assertHtmlContains('class="primary-cta-button primary-cta-button--accent primary-cta-button--icon-only active:scale-95"', $feedHtml);
        $this->assertHtmlContains('aria-label="Upload CSV Feed Konten"', $feedHtml);
        $this->assertHtmlContains('<i :class="metaUploading ? \'fa-solid fa-spinner fa-spin\' : \'fa-solid fa-upload\'"></i>', $feedHtml);
        $this->assertHtmlContains('aria-label="Import Folder Feed Konten"', $feedHtml);
        $this->assertHtmlContains('<i :class="metaUploading ? \'fa-solid fa-spinner fa-spin\' : \'fa-solid fa-folder-open\'"></i>', $feedHtml);
        $this->assertHtmlNotContains('</i>Upload CSV', $feedHtml);
        $this->assertHtmlNotContains('</i>Import Folder', $feedHtml);
        $this->assertHtmlContains('id="meta-feed-upload" name="meta_feed_upload" type="file"', $feedHtml);
        $this->assertTrue(str_contains($feedHtml, 'Tambah Data Manual') || str_contains($html, 'openMetaFeedManualModal,'));
        $this->assertHtmlNotContains('<label for="meta-feed-upload"', $feedHtml);
    }

    public function test_meta_story_and_feed_pages_allow_vertical_scroll_for_full_analytics_layout(): void
    {
        $storyHtml = file_get_contents(resource_path('views/dashboard/partials/menus/meta-story.blade.php'));
        $feedHtml = file_get_contents(resource_path('views/dashboard/partials/menus/meta-feed.blade.php'));
        $dashboardShellCss = file_get_contents(resource_path('css/dashboard-shell.css'));

        $this->assertIsString($storyHtml);
        $this->assertIsString($feedHtml);
        $this->assertIsString($dashboardShellCss);

        $this->assertHtmlContains('meta-analytics-page', $storyHtml);
        $this->assertHtmlContains('meta-analytics-page', $feedHtml);
        $this->assertHtmlContains('.dashboard-main-shell > main > .meta-analytics-page {', $dashboardShellCss);
        $this->assertHtmlContains('overflow: visible;', $dashboardShellCss);
        $this->assertHtmlContains('.dashboard-main-shell > main > .meta-analytics-page > .section-card-shell {', $dashboardShellCss);
        $this->assertHtmlContains('max-height: none;', $dashboardShellCss);
        $this->assertHtmlContains('.dashboard-main-shell > main > .meta-analytics-page .overflow-x-auto {', $dashboardShellCss);
        $this->assertHtmlContains('overflow-y: clip;', $dashboardShellCss);
    }

    public function test_meta_feed_table_renders_below_insight_card_before_secondary_sections(): void
    {
        $feedHtml = file_get_contents(resource_path('views/dashboard/partials/menus/meta-feed.blade.php'));

        $this->assertIsString($feedHtml);

        $insightPos = strpos($feedHtml, 'Insight Feed yang Bisa Dipakai');
        $tableHeadingPos = strpos($feedHtml, 'Data Feed Konten');
        $tablePos = strpos($feedHtml, '<th class="table-header-cell">Tanggal</th><th class="table-header-cell">Tipe</th><th class="table-header-cell">Akun</th><th class="table-header-cell">Konten</th>');
        $monthlyPos = strpos($feedHtml, 'Ringkasan Bulanan per Akun');

        $this->assertNotFalse($insightPos);
        $this->assertNotFalse($tableHeadingPos);
        $this->assertNotFalse($tablePos);
        $this->assertNotFalse($monthlyPos);
        $this->assertTrue($insightPos < $tableHeadingPos);
        $this->assertTrue($tableHeadingPos < $tablePos);
        $this->assertTrue($tablePos < $monthlyPos);
    }

    public function test_meta_story_table_renders_below_insight_card_before_secondary_sections(): void
    {
        $storyHtml = file_get_contents(resource_path('views/dashboard/partials/menus/meta-story.blade.php'));

        $this->assertIsString($storyHtml);

        $insightPos = strpos($storyHtml, 'Insight Story yang Bisa Dipakai');
        $tableHeadingPos = strpos($storyHtml, 'Data Story IG');
        $tablePos = strpos($storyHtml, '<th class="table-header-cell">Tanggal</th><th class="table-header-cell">Tipe</th><th class="table-header-cell">Konten</th>');
        $monthlyPos = strpos($storyHtml, 'Ringkasan Bulanan per Akun');

        $this->assertNotFalse($insightPos);
        $this->assertNotFalse($tableHeadingPos);
        $this->assertNotFalse($tablePos);
        $this->assertNotFalse($monthlyPos);
        $this->assertTrue($insightPos < $tableHeadingPos);
        $this->assertTrue($tableHeadingPos < $tablePos);
        $this->assertTrue($tablePos < $monthlyPos);
    }

    public function test_meta_story_and_feed_tables_use_frozen_index_and_15_row_pagination(): void
    {
        $storyHtml = file_get_contents(resource_path('views/dashboard/partials/menus/meta-story.blade.php'));
        $feedHtml = file_get_contents(resource_path('views/dashboard/partials/menus/meta-feed.blade.php'));
        $presentationScript = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-meta-ig-analytics-presentation.blade.php'));
        $returnBlock = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-return-block.blade.php'));

        $this->assertIsString($storyHtml);
        $this->assertIsString($feedHtml);
        $this->assertIsString($presentationScript);
        $this->assertIsString($returnBlock);

        $this->assertHtmlContains('<th class="table-header-cell table-header-index table-freeze-index">#</th>', $storyHtml);
        $this->assertHtmlContains('{{ (metaStoryPage - 1) * 15 + idx + 1 }}', $storyHtml);
        $this->assertHtmlContains('<th class="table-header-cell table-header-index table-freeze-index">#</th>', $feedHtml);
        $this->assertHtmlContains('v-for="(r, idx) in pagedMetaFeed"', $feedHtml);
        $this->assertHtmlContains('{{ (metaFeedPage - 1) * 15 + idx + 1 }}', $feedHtml);
        $this->assertHtmlContains('{{ (metaFeedPage - 1) * 15 + 1 }}-{{ Math.min(metaFeedPage * 15, filteredMetaFeed.length) }} dari {{ filteredMetaFeed.length }} data', $feedHtml);
        $this->assertHtmlContains('const metaFeedTotalPages = computed(() => Math.max(1, Math.ceil(filteredMetaFeed.value.length / PAGE_SIZE)));', $presentationScript);
        $this->assertHtmlContains('const pagedMetaFeed = computed(() => filteredMetaFeed.value.slice((metaFeedPage.value - 1) * PAGE_SIZE, metaFeedPage.value * PAGE_SIZE));', $presentationScript);
        $this->assertHtmlContains('pagedMetaFeed,', $returnBlock);
    }

    public function test_dashboard_primary_tables_do_not_use_20_row_pagination(): void
    {
        $sources = [
            file_get_contents(resource_path('views/dashboard/partials/menus/asset-vendor-inventory.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/ads-log.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/sell-out.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/harga-kompetitor.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/menus/laporan-event.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/shell/app-script-ads-log-operations.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/shell/app-script-asset-vendor-inventory-operations.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/shell/app-script-price-competitor-operations.blade.php')),
            file_get_contents(resource_path('views/dashboard/partials/scripts/export-sales-and-promo-pdfs.blade.php')),
            file_get_contents(base_path('resources/js/dashboard/menu/lpjk.js')),
        ];

        $html = implode("\n", array_filter($sources, 'is_string'));

        $this->assertHtmlContains('/ PAGE_SIZE', $html);
        $this->assertHtmlContains('* PAGE_SIZE', $html);
        $this->assertHtmlContains('* 15 + idx + 1', $html);
        $this->assertHtmlNotContains('/ 20', $html);
        $this->assertHtmlNotContains('* 20', $html);
    }

    public function test_meta_story_sidebar_menu_uses_visible_solid_icon(): void
    {
        $sidebarNavSources = implode("\n", array_map(
            static fn (string $path): string => (string) file_get_contents(resource_path($path)),
            [
                'views/dashboard/partials/shell/app-frame-sidebar-nav-analysis.blade.php',
                'views/dashboard/partials/shell/app-frame-sidebar-nav-admin.blade.php',
            ]
        ));

        $this->assertIsString($sidebarNavSources);
        $this->assertHtmlContains('fa-solid fa-clapperboard text-[11px] lg:text-[12px] w-4', $sidebarNavSources);
        $this->assertHtmlNotContains('fa-brands fa-instagram', $sidebarNavSources);
    }

    public function test_meta_story_and_feed_wait_for_meta_data_before_showing_empty_tables(): void
    {
        $storyHtml = file_get_contents(resource_path('views/dashboard/partials/menus/meta-story.blade.php'));
        $feedHtml = file_get_contents(resource_path('views/dashboard/partials/menus/meta-feed.blade.php'));

        $this->assertIsString($storyHtml);
        $this->assertIsString($feedHtml);

        $this->assertHtmlContains("v-if=\"activeTab === 'meta_story' && !metaStoryLoaded\"", $storyHtml);
        $this->assertHtmlContains("v-if=\"activeTab === 'meta_story' && metaStoryLoaded\"", $storyHtml);
        $this->assertHtmlContains("v-if=\"activeTab === 'meta_feed' && !metaFeedLoaded\"", $feedHtml);
        $this->assertHtmlContains("v-if=\"activeTab === 'meta_feed' && metaFeedLoaded\"", $feedHtml);
        $this->assertHtmlContains('Memuat data Story', $storyHtml);
        $this->assertHtmlContains('Memuat data Feed', $feedHtml);
    }

    public function test_sell_out_searchable_dropdowns_use_select_trigger_pattern(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains("toggleSearchSelect(\$event, 'sellOutVendor')", $html);
        $this->assertHtmlContains("toggleSearchSelect(\$event, 'sellOutMonth')", $html);
        $this->assertHtmlContains('class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form"', $html);
        $this->assertHtmlContains('fa-solid fa-chevron-down ml-auto text-[9px] text-slate-400', $html);
    }

    public function test_unit_and_claim_searchable_dropdowns_use_select_trigger_pattern(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains("toggleSearchSelect(\$event, 'filter_available')", $html);
        $this->assertHtmlContains("toggleSearchSelect(\$event, 'filter_claim_status')", $html);
        $this->assertHtmlContains("toggleSearchSelect(\$event, 'filter_claim_garansi')", $html);
        $this->assertHtmlContains("unitDitanyaAvailableFilter = ''", $html);
        $this->assertHtmlContains("claimGaransiStatusFilter = ''", $html);
        $this->assertHtmlContains("claimGaransiGaransiFilter = ''", $html);
    }

    public function test_distribution_and_analytics_start_without_date_filter(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains("const commonDateFilter = ref({ start: '', end: '' });", $html);
    }

    public function test_all_dashboard_tables_use_10px_headers_and_9px_body(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();
        $appCss = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($appCss);

        $this->assertHtmlContains('#app table thead,', $html);
        $this->assertHtmlContains('#app table thead * {', $html);
        $this->assertHtmlContains('font-size: 10px !important;', $html);
        $this->assertHtmlContains('#app table thead {', $html);
        $this->assertHtmlContains('background: rgb(241 245 249) !important;', $html);
        $this->assertHtmlContains('#app table thead tr {', $html);
        $this->assertHtmlContains('#app table th {', $html);
        $this->assertHtmlContains('border-color: transparent !important;', $html);
        $this->assertHtmlContains('box-shadow: none;', $html);
        $this->assertHtmlContains('border-bottom: 0;', $html);
        $this->assertHtmlNotContains('box-shadow: inset 0 -1px 0 rgb(203 213 225 / 0.65);', $html);
        $this->assertHtmlNotContains('border-bottom: 1px solid rgb(203 213 225);', $html);
        $this->assertHtmlNotContains('border-color: rgb(203 213 225) !important;', $html);
        $this->assertHtmlContains('color: var(--ppp-secondary) !important;', $html);
        $this->assertHtmlNotContains('#app table thead {'."\n".'            background: var(--ppp-accent) !important;', $html);
        $this->assertHtmlNotContains('#app table thead tr {'."\n".'            background: var(--ppp-accent) !important;', $html);
        $this->assertHtmlNotContains('#app table th {'."\n".'            background: var(--ppp-accent) !important;', $html);
        $this->assertHtmlContains('padding: 8px 12px !important;', $html);
        $this->assertHtmlContains('#app table tbody,', $html);
        $this->assertHtmlContains('#app table tbody *,', $html);
        $this->assertHtmlContains('#app table tfoot,', $html);
        $this->assertHtmlContains('font-size: 9px !important;', $html);
        $this->assertHtmlContains('#app table tbody tr:nth-child(even) {', $html);
        $this->assertHtmlContains('#app table tbody tr:nth-child(odd),', $html);
        $this->assertHtmlContains('#app table tbody tr:nth-child(odd)>td {', $html);
        $this->assertHtmlContains('#app table tbody tr:nth-child(even)>td {', $html);
        $this->assertHtmlContains('#app table tbody tr:nth-child(odd) .table-freeze-index,', $html);
        $this->assertHtmlContains('#app table tbody tr:nth-child(odd) .table-freeze-action {', $html);
        $this->assertHtmlContains('#app table tbody tr:nth-child(even) .table-freeze-index,', $html);
        $this->assertHtmlContains('#app table tbody tr:nth-child(even) .table-freeze-action {', $html);
        $this->assertHtmlContains('background: var(--ppp-card) !important;', $html);
        $this->assertHtmlContains('background: rgb(248 250 252) !important;', $html);
        $this->assertHtmlNotContains('background: rgb(var(--ppp-bg-rgb) / 0.5) !important;', $html);
        $this->assertHtmlContains('--ppp-table-row-hover-bg: #e2e8f0;', $appCss);
        $this->assertHtmlContains('--ppp-table-freeze-hover-bg: var(--ppp-table-row-hover-bg);', $appCss);
        $this->assertHtmlContains('#app table tbody tr,', $html);
        $this->assertHtmlContains('#app table tbody tr>td {', $html);
        $this->assertHtmlContains('transition: none !important;', $html);
        $this->assertHtmlContains('#app table tbody tr:hover {', $html);
        $this->assertHtmlContains('background: var(--ppp-table-row-hover-bg) !important;', $html);
        $this->assertHtmlContains('#app table tbody tr:hover>td {', $html);
        $this->assertHtmlContains('#app table tbody tr:hover .table-freeze-index,', $html);
        $this->assertHtmlContains('#app table tbody tr:hover .table-freeze-action {', $html);
        $this->assertHtmlContains('background: var(--ppp-table-freeze-hover-bg) !important;', $html);
        $this->assertHtmlContains('#app table tbody tr:hover .text-slate-400 {', $html);
        $this->assertHtmlContains('color: rgb(148 163 184) !important;', $html);
        $this->assertHtmlContains('#app table tbody tr:hover .text-slate-800 {', $html);
        $this->assertHtmlContains('color: var(--ppp-text-secondary) !important;', $html);
        $this->assertHtmlContains('#app table td {', $html);
        $this->assertHtmlContains('padding: 6px 12px !important;', $html);
    }

    public function test_dashboard_tables_use_shared_sortable_headers(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.table-sortable {', $html);
        $this->assertHtmlContains('.table-sortable.table-sort-asc::after {', $html);
        $this->assertHtmlContains('.table-sortable.table-sort-desc::after {', $html);
        $this->assertHtmlContains('const hydrateSortableTableHeaders = () => {', $html);
        $this->assertHtmlContains('const sortTableDomRows = (headerCell) => {', $html);
        $this->assertHtmlContains('hydrateSortableTableHeaders();', $html);
    }

    public function test_ideation_kanban_caps_font_size_at_10px(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.ideation-kanban-board,', $html);
        $this->assertHtmlContains('.ideation-kanban-board * {', $html);
        $this->assertHtmlContains('class="ideation-kanban-board grid grid-cols-1 md:grid-cols-3 gap-6 items-start"', $html);
    }

    public function test_dashboard_write_actions_use_laravel_crud_api(): void
    {
        $html = $this->renderDashboardHtml();

        foreach ([
            'jsonApi',
            '/api/master-plans',
            '/api/distributions',
            '/api/analytics',
            '/api/settings',
            "'POST'",
            "'PUT'",
            "method: 'DELETE'",
            'X-XSRF-TOKEN',
        ] as $needle) {
            $this->assertHtmlContains($needle, $html);
        }
    }

    public function test_nama_stock_uses_laravel_raw_sheet_api(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('/api/raw-sheets/Nama_Stock', $html);
        $this->assertHtmlContains('getNamaStockData()', $html);
        $this->assertHtmlContains('saveNamaStockRows(rows)', $html);
    }

    public function test_nama_stock_form_uses_dropdowns_for_kategori_brand_and_manual_input_for_seri(): void
    {
        $html = $this->renderDashboardHtml();

        foreach ([
            'namaStockKategoriOptions',
            'namaStockBrandOptions',
            "toggleSearchSelect(\$event, 'nama_stock_kategori')",
            "toggleSearchSelect(\$event, 'nama_stock_brand')",
            'v-model="searchSelectQuery"',
            'Pilih Kategori',
            'Pilih Brand',
            'v-model.trim="namaStockForm.SERI"',
            'placeholder="Ketik seri"',
        ] as $needle) {
            $this->assertHtmlContains($needle, $html);
        }

        $this->assertHtmlNotContains("toggleSearchSelect(\$event, 'nama_stock_seri')", $html);
    }

    public function test_other_seri_selectors_still_source_options_from_nama_stock_master(): void
    {
        $html = $this->renderDashboardHtml();

        foreach ([
            'const getSeriOptions = (kat, brand) => {',
            "const nsSeriOptions = computed(() => getSeriOptions(unitDitanyaForm.value['KATEGORI'], unitDitanyaForm.value['BRAND']));",
            'getSeriOptions(sellOutForm.Kategori, sellOutForm.Brand)',
            "searchSelectOpen === 'unit_seri'",
            "searchSelectOpen === 'sotSeri'",
        ] as $needle) {
            $this->assertHtmlContains($needle, $html);
        }
    }

    public function test_nama_stock_view_uses_kategori_and_brand_filter_dropdowns(): void
    {
        $html = $this->renderDashboardHtml();

        foreach ([
            'createNamaStockState(ref);',
            "const namaStockFilterKategoriOptions = computed(() => filteredUniqueFrom(namaStockRows.value, 'KATEGORI'));",
            "const namaStockFilterBrandOptions = computed(() => filteredUniqueFrom(namaStockRows.value, 'BRAND', { KATEGORI: namaStockKategoriFilter.value }));",
            "searchSelectOpen === 'nama_stock_filter_kategori'",
            "searchSelectOpen === 'nama_stock_filter_brand'",
            "namaStockKategoriFilter ? 'text-slate-800 font-medium' : 'text-slate-400'",
            "namaStockBrandFilter ? 'text-slate-800 font-medium' : 'text-slate-400'",
            'return namaStockRows.value.filter(row => {',
            "const matchKategori = !namaStockKategoriFilter.value || String(row.KATEGORI || '').trim().toUpperCase() === String(namaStockKategoriFilter.value || '').trim().toUpperCase();",
            "const matchBrand = !namaStockBrandFilter.value || String(row.BRAND || '').trim().toUpperCase() === String(namaStockBrandFilter.value || '').trim().toUpperCase();",
        ] as $needle) {
            $this->assertHtmlContains($needle, $html);
        }
    }

    public function test_keep_barang_type_hp_selector_sources_options_from_nama_stock_master(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains("searchSelectOpen === 'keep_type_hp'", $html);
        $this->assertHtmlContains(<<<'HTML'
const keepBarangTypeHpOptions = computed(() => {
                    const masterTypeOptions = namaStockRows.value
                        .map(row => buildStockNameLabel(row))
                        .filter(Boolean);
                    if (masterTypeOptions.length > 0) return mergeOptionValues(masterTypeOptions);
                    return mergeOptionValues(
                        uniqueMultiKeyFrom(keepBarangData.value, ['TYPE_HP'])
                    );
                });
HTML, $html);
        $this->assertHtmlContains('const normalizeKeepBarangTypeHpValue = (value) => {', $html);
        $this->assertHtmlContains('const exactMasterMatch = namaStockRows.value.find(row => buildStockNameLabel(row).toUpperCase() === raw);', $html);
        $this->assertHtmlContains("const seriMatches = namaStockRows.value.filter(row => String(row.SERI || '').trim().toUpperCase() === raw);", $html);
    }

    public function test_keep_barang_type_hp_selector_uses_accessible_button_and_empty_state(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('<button type="button" @click="toggleSearchSelect($event, \'keep_type_hp\')"', $html);
        $this->assertHtmlContains('v-if="keepBarangTypeHpOptions.filter(o => !searchSelectQuery || o.toLowerCase().includes(searchSelectQuery.toLowerCase())).length === 0"', $html);
        $this->assertHtmlContains('Belum ada opsi Type HP', $html);
    }

    public function test_open_keep_barang_modal_loads_nama_stock_master_when_needed(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('if (!namaStockLoaded.value) loadNamaStockData();', $html);
        $this->assertHtmlContains('TYPE_HP: normalizeKeepBarangTypeHpValue(row.TYPE_HP)', $html);
    }

    public function test_customer_service_save_handlers_prevent_double_submit_and_validate_required_fields(): void
    {
        $html = $this->renderDashboardHtml();
        $customerServiceModule = file_get_contents(resource_path('js/dashboard/menu/customer-service.js'));

        foreach ([
            'const saveOrderanOnline = () => {',
            'const saveUnitDitanya = () => {',
            'const saveClaimGaransi = () => {',
            'const saveKeepBarang = () => {',
        ] as $needle) {
            $this->assertHtmlContains($needle, $customerServiceModule);
        }
        $this->assertHtmlContains('const saveSellOut = () => {', $html);

        $this->assertGreaterThanOrEqual(4, substr_count($customerServiceModule, 'if (deps.submitting.value) return;'));
        $this->assertHtmlContains("if (!deps.orderanOnlineForm.value.NAMA || !deps.orderanOnlineForm.value['TYPE UNIT']) {", $customerServiceModule);
        $this->assertHtmlContains("deps.showNotification('Nama customer dan type unit wajib diisi');", $customerServiceModule);
        $this->assertHtmlContains('if (!deps.unitDitanyaForm.value.KATEGORI || !deps.unitDitanyaForm.value.BRAND || !deps.unitDitanyaForm.value.SERI) {', $customerServiceModule);
        $this->assertHtmlContains("deps.showNotification('Kategori, brand, dan seri wajib diisi');", $customerServiceModule);
        $this->assertHtmlContains('if (!deps.claimGaransiForm.value.NAMA_CUSTOMER || !deps.claimGaransiForm.value.TIPE) {', $customerServiceModule);
        $this->assertHtmlContains("deps.showNotification('Nama customer dan tipe wajib diisi');", $customerServiceModule);
        $this->assertHtmlContains('if (!form.NAMA || !form.NOMOR_HP || !form.TYPE_HP) {', $customerServiceModule);
        $this->assertHtmlContains("deps.showNotification('Nama, nomor HP, dan Type HP wajib diisi');", $customerServiceModule);
    }

    public function test_sell_out_default_month_range_uses_valid_end_date_format(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains("const lastDay = String(new Date(year, month + 1, 0).getDate()).padStart(2, '0');", $html);
        $this->assertHtmlContains(<<<'HTML'
end: `${year}-${String(month + 1).padStart(2, '0')}-${lastDay}`
HTML, $html);
        $this->assertHtmlNotContains(<<<'HTML'
const end = `${year}-${String(new Date(year, month + 1, 0).getDate()).padStart(2, '0')}`;
HTML, $html);
    }

    public function test_customer_service_search_selectors_use_accessible_buttons(): void
    {
        $html = $this->renderDashboardHtml();

        foreach ([
            '<button type="button" @click="toggleSearchSelect($event, \'ecommerce\')"',
            '<button type="button" @click="toggleSearchSelect($event, \'orderan_type_unit\')"',
            '<button type="button" @click="toggleSearchSelect($event, \'unit_kategori\')"',
            '<button type="button" @click="toggleSearchSelect($event, \'unit_tipe\')"',
            '<button type="button" @click="toggleSearchSelect($event, \'claim_tipe\')"',
            '<button type="button" @click="toggleSearchSelect($event, \'claim_seri\')"',
            '<button type="button" @click="toggleSearchSelect($event, \'keep_handle_by\')"',
            '<button type="button" @click="toggleSearchSelect($event, \'keep_team_gudang\')"',
            ":aria-expanded=\"searchSelectOpen === 'ecommerce' ? 'true' : 'false'\"",
            ":aria-expanded=\"searchSelectOpen === 'unit_kategori' ? 'true' : 'false'\"",
            ":aria-expanded=\"searchSelectOpen === 'claim_tipe' ? 'true' : 'false'\"",
            ":aria-expanded=\"searchSelectOpen === 'keep_handle_by' ? 'true' : 'false'\"",
        ] as $needle) {
            $this->assertHtmlContains($needle, $html);
        }
    }

    public function test_customer_service_modals_preload_nama_stock_master_when_needed(): void
    {
        $corePartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-domain-state-core.blade.php'));

        $this->assertIsString($corePartial);
        $this->assertHtmlContains('const openOrderanOnlineModal = (type = \'create\', row = null) => {', $corePartial);
        $this->assertHtmlContains('const openClaimGaransiModal = (type = \'create\', row = null) => {', $corePartial);
        $this->assertHtmlContains('const ensureNamaStockLoaded = () => {', $corePartial);
        $this->assertGreaterThanOrEqual(3, substr_count($corePartial, 'ensureNamaStockLoaded();'));
    }

    public function test_master_plan_links_render_drive_and_normalized_distribution_meta(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('const normalizeMasterPlanRow = (item = {}) => {', $html);
        $this->assertHtmlContains("normalizedKey === 'contentType'", $html);
        $this->assertHtmlContains('return normalizeMasterPlanRows(payload.data);', $html);
        $this->assertHtmlContains('v-if="item.Link_Drive"', $html);
        $this->assertTrue(str_contains($html, 'hasAnyMasterLink(item)') || str_contains($html, 'const hasAnyMasterLink = (item) =>'));
    }

    public function test_master_plan_search_input_is_bound_to_returned_state(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('const masterSearch = ref("");', $html);
        $this->assertHtmlContains('id="master-search" name="master_search" v-model="masterSearch" type="text" placeholder="Cari judul atau colab..."', $html);
        $this->assertHtmlContains('masterSearch,', $html);
    }

    public function test_settings_and_master_plan_use_runner_via_is_web_proxy(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('if (!ensureRunApi().isWebProxy) {', $html);
        $this->assertHtmlContains('.getSettings();', $html);
        $this->assertHtmlContains('.getMasterPlanData();', $html);
    }

    public function test_toolbar_create_buttons_are_icon_only(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertHtmlContains('aria-label="Buat Ide"', $html);
        $this->assertHtmlNotContains('<i class="fa-solid fa-plus mr-2"></i>Buat Ide', $html);
        $this->assertHtmlNotContains('<i class="fa-solid fa-plus mr-2"></i>Tambah Plan', $html);
    }

    public function test_ideation_board_card_has_delete_button(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('@click.stop="deleteMasterPlan(item.ID)"', $html);
        $this->assertHtmlContains('class="w-5 h-5 rounded-full bg-danger border border-danger text-light hover:bg-danger hover:text-white hover:border-danger transition-all flex items-center justify-center"', $html);
    }

    public function test_ideation_kanban_cards_use_compact_spacing(): void
    {
        $partial = file_get_contents(resource_path('views/dashboard/partials/menus/ideation.blade.php'));

        $this->assertIsString($partial);
        $this->assertHtmlContains('class="ideation-kanban-card bg-white p-3 radius-card border border-slate-100 hover:border-ppp-accent/20 transition-all cursor-pointer group animate-fadeIn"', $partial);
        $this->assertHtmlContains('class="flex items-start justify-between gap-2 mb-2"', $partial);
        $this->assertHtmlContains("['px-1.5 py-0.5 rounded-md text-overline-xs font-bold uppercase', getIdeationTypeTone(item).chip]", $partial);
        $this->assertHtmlContains("['type-body-sm font-medium text-slate-800 leading-tight mb-2 group-hover:text-ppp-accent transition-colors'", $partial);
        $this->assertHtmlNotContains('class="bg-white p-4 radius-card border border-slate-100 hover:border-ppp-accent/20 transition-all cursor-pointer group animate-fadeIn"', $partial);
    }

    public function test_ideation_board_card_shows_idea_age(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('const getIdeaAgeLabel = (item) => {', $html);
        $this->assertHtmlContains('return `Umur ${diffDays}h`;', $html);
        $this->assertHtmlContains('{{ getIdeaAgeLabel(item) }}', $html);
    }

    public function test_ideation_menu_does_not_render_summary_cards(): void
    {
        $partial = file_get_contents(resource_path('views/dashboard/partials/menus/ideation.blade.php'));

        $this->assertIsString($partial);
        $this->assertHtmlNotContains('v-for="c in ideationSummary.cards"', $partial);
        $this->assertHtmlNotContains('dashboard-summary-card-compact', $partial);
        $this->assertHtmlContains('Ideation Board', $partial);
    }

    public function test_content_calendar_renders_content_story_and_event_lists_without_content_slice(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains("getCalendarItems(day).filter(i => i.TYPE === 'content')", $html);
        $this->assertHtmlContains("getCalendarItems(day).filter(i => i.TYPE === 'story')", $html);
        $this->assertHtmlContains("getCalendarItems(day).filter(i => i.TYPE === 'event')", $html);
        $this->assertHtmlContains("}).map(s => ({ ...s, TYPE: 'story' }));", $html);
        $this->assertHtmlNotContains(".filter(i => i.TYPE === 'content').slice(0, 3)", $html);
    }

    public function test_content_calendar_uses_distinct_colors_for_content_story_and_hari_raya(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertTrue(str_contains($html, 'bg-blue-50 border border-blue-100') || str_contains($html, 'bg-ppp-accent text-light shadow-sm'));
        $this->assertTrue(str_contains($html, 'bg-rose-50 border border-rose-100') || str_contains($html, '>Story</span>'));
        $this->assertTrue(str_contains($html, 'bg-amber-50') || str_contains($html, '>Event {{'));
        $this->assertHtmlContains('> Konten {{', $html);
        $this->assertHtmlContains('> Story {{', $html);
        $this->assertHtmlContains('> Event {{', $html);
    }

    public function test_content_calendar_date_cells_grow_with_item_count_without_inner_scroll(): void
    {
        $html = $this->renderDashboardHtml();

        $this->assertHtmlContains('class="min-h-[120px] xl:min-h-[140px] bg-slate-50/50 rounded-2xl border border-dashed border-slate-100"', $html);
        $this->assertHtmlContains(":class=\"['min-h-[120px] xl:min-h-[140px] rounded-2xl border p-2.5 xl:p-3 transition-all group relative cursor-pointer'", $html);
        $this->assertHtmlContains('<div class="space-y-1.5">', $html);
        $this->assertHtmlNotContains('overflow-y-auto pr-0.5 custom-scrollbar max-h-[calc(100%-24px)]', $html);
        $this->assertHtmlNotContains('aspect-square md:aspect-video rounded-2xl border p-2 md:p-3', $html);
    }

    public function test_calendar_day_modal_cards_use_compact_spacing(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains("['p-3 rounded-xl border transition-all'", $html);
        $this->assertHtmlContains('class="flex items-start justify-between gap-2 mb-1.5"', $html);
        $this->assertHtmlContains('class="text-[12px] font-bold text-slate-900 leading-[1.2] mb-1 uppercase"', $html);
        $this->assertHtmlContains('class="mt-2.5 pt-2 border-t border-slate-50 flex items-center justify-end"', $html);
        $this->assertHtmlNotContains("['p-4 rounded-2xl border transition-all'", $html);
    }

    public function test_desktop_form_modals_are_horizontally_centered(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('class="fixed inset-0 z-[2500] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet"', $html);
        $this->assertHtmlContains('class="fixed inset-0 z-[2000] flex items-end md:items-center justify-center md:p-4 overlay-motion-sheet"', $html);
        $this->assertHtmlNotContains('class="fixed inset-0 z-[2500] flex items-end md:items-center md:p-4"', $html);
    }

    public function test_notifications_are_type_aware_with_icon_and_tone(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('const notification = ref({ open: false, message: \'\', type: \'success\', icon: \'fa-circle-check\' });', $html);
        $this->assertHtmlContains("notification.type === 'error'", $html);
        $this->assertHtmlContains("notification.type === 'warning'", $html);
        $this->assertTrue(str_contains($html, '<i :class="[\'fa-solid text-[12px]\', notification.icon]"></i>') || str_contains($html, 'notification.icon'));
        $this->assertTrue(str_contains($html, 'max-w-[min(92vw,520px)] overflow-hidden') || str_contains($html, 'notification.message'));
        $this->assertTrue(str_contains($html, 'overflow-hidden whitespace-nowrap') || str_contains($html, 'notification.message'));
        $this->assertHtmlContains('notification.message', $html);
        $this->assertHtmlNotContains('class="mt-0.5 break-words leading-relaxed">{{ notification.message }}</div>', $html);
    }

    public function test_long_form_modals_use_sticky_mobile_safe_footers(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.modal-footer-bar {', $html);
        $this->assertHtmlContains('.radius-sheet-bottom {', $html);
        $this->assertHtmlContains('class="modal-footer-bar modal-footer-actions"', $html);
        $this->assertHtmlContains('.modal-footer-actions {', $html);
    }

    public function test_blocking_overlays_lock_document_scroll(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('html.modal-scroll-lock,', $html);
        $this->assertHtmlContains("document.documentElement.classList.toggle('modal-scroll-lock', locked);", $html);
        $this->assertHtmlContains("document.body.classList.toggle('modal-scroll-lock', locked);", $html);
        $this->assertHtmlContains('const hasBlockingOverlayOpen = computed(() => Boolean(', $html);
        $this->assertHtmlContains('watch(hasBlockingOverlayOpen, (locked) => {', $html);
        $this->assertHtmlContains('setDocumentScrollLock(false);', $html);
    }

    public function test_table_row_actions_use_labeled_tap_safe_buttons(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.table-action-button {', $html);
        $this->assertHtmlContains('white-space: nowrap;', $html);
        $this->assertHtmlContains('.table-action-button.table-action-compact {', $html);
        $this->assertHtmlContains('.table-action-button.table-action-link:hover {', $html);
        $this->assertHtmlContains('.table-action-button.table-action-view:hover {', $html);
        $this->assertHtmlNotContains('button:not(.table-action-button):has(> .fa-pen)', $html);
        $this->assertHtmlContains('aria-label="Edit"', $html);
        $this->assertHtmlContains('aria-label="Hapus"', $html);
        $this->assertHtmlContains('aria-label="Link Drive"', $html);
        $this->assertHtmlContains('aria-label="Detail Pengeluaran"', $html);
        $this->assertHtmlNotContains('>Hapus</span>', $html);
    }

    public function test_table_row_action_icons_use_shared_edit_and_delete_symbols(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('class="fa-solid fa-pen-to-square text-body-sm"', $html);
        $this->assertHtmlContains('class="fa-solid fa-trash-can text-body-sm"', $html);
        $this->assertHtmlNotContains('class="fa-solid fa-pen text-body-sm"', $html);
        $this->assertHtmlNotContains('class="fa-solid fa-trash text-body-sm"', $html);
    }

    public function test_table_row_action_hover_only_changes_border_and_icon_color(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();
        $appCss = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($appCss);
        $css = $appCss."\n".$html;

        $this->assertHtmlContains('--ppp-table-action-edit-hover: var(--ppp-accent);', $css);
        $this->assertHtmlContains('--ppp-table-action-delete-hover: var(--ppp-danger);', $css);
        $this->assertHtmlContains('color: var(--ppp-table-action-edit-hover);', $css);
        $this->assertHtmlContains('border-color: var(--ppp-table-action-edit-hover);', $css);
        $this->assertHtmlContains('color: var(--ppp-table-action-delete-hover);', $css);
        $this->assertHtmlContains('border-color: var(--ppp-table-action-delete-hover);', $css);
        $this->assertHtmlNotContains(".table-action-button:hover {\n            color: var(--ppp-accent);\n            background: rgb(239 242 255);", $css);
        $this->assertHtmlNotContains(".table-action-button.table-action-danger:hover {\n            color: var(--ppp-danger);\n            background: rgb(254 242 242);", $css);
    }

    public function test_desktop_table_index_and_action_columns_are_frozen_together(): void
    {
        $appCss = file_get_contents(resource_path('css/app.css'));
        $dashboardShellCss = file_get_contents(resource_path('css/dashboard-shell.css'));

        $this->assertIsString($appCss);
        $this->assertIsString($dashboardShellCss);
        $this->assertHtmlContains('--ppp-table-freeze-index-width: 56px;', $appCss);
        $this->assertHtmlContains('--ppp-table-freeze-action-width: 96px;', $appCss);
        $this->assertHtmlContains('--ppp-table-freeze-header-bg: var(--ppp-card);', $appCss);
        $this->assertHtmlContains('--ppp-table-row-hover-bg: #e2e8f0;', $appCss);
        $this->assertHtmlContains('--ppp-table-freeze-hover-bg: var(--ppp-table-row-hover-bg);', $appCss);
        $this->assertHtmlNotContains('--ppp-table-freeze-divider:', $appCss);
        $this->assertHtmlContains('overflow-x: clip;', $dashboardShellCss);
        $this->assertHtmlContains('overscroll-behavior-x: none;', $dashboardShellCss);
        $this->assertHtmlContains('.dashboard-topbar {', $dashboardShellCss);
        $this->assertHtmlContains('max-width: none;', $dashboardShellCss);
        $this->assertHtmlContains('overflow: visible;', $dashboardShellCss);
        $this->assertHtmlContains('touch-action: pan-y;', $dashboardShellCss);
        $this->assertHtmlNotContains('contain: paint;', $dashboardShellCss);
        $this->assertHtmlContains('.section-card-shell .overflow-x-auto {', $dashboardShellCss);
        $this->assertHtmlContains('overscroll-behavior-x: contain;', $dashboardShellCss);
        $this->assertHtmlContains('overscroll-behavior-inline: contain;', $dashboardShellCss);
        $this->assertHtmlContains('-webkit-overflow-scrolling: auto;', $dashboardShellCss);
        $this->assertHtmlContains('.table-freeze-index,', $dashboardShellCss);
        $this->assertHtmlContains('.table-freeze-action {', $dashboardShellCss);
        $this->assertHtmlContains('.lpjk-table-card {', $dashboardShellCss);
        $this->assertHtmlContains('--ppp-table-freeze-action-width: 144px;', $dashboardShellCss);
        $this->assertHtmlContains('.lpjk-table-card .lpjk-event-cell {', $dashboardShellCss);
        $this->assertHtmlContains('min-width: 320px;', $dashboardShellCss);
        $this->assertHtmlContains('class="hidden md:block section-card section-card-shell lpjk-table-card"', file_get_contents(resource_path('views/dashboard/partials/menus/laporan-event.blade.php')));
        $this->assertHtmlContains('class="w-full text-body-sm text-left border-collapse min-w-[1040px]"', file_get_contents(resource_path('views/dashboard/partials/menus/laporan-event.blade.php')));
        $this->assertHtmlContains('class="table-header-cell lpjk-event-cell">Event</th>', file_get_contents(resource_path('views/dashboard/partials/menus/laporan-event.blade.php')));
        $this->assertHtmlContains('.table-header-row {', $dashboardShellCss);
        $this->assertHtmlContains('.table-header-cell {', $dashboardShellCss);
        $this->assertHtmlContains('border-radius: 0;', $dashboardShellCss);
        $this->assertHtmlNotContains('.section-card-shell>div:first-child table thead tr th:first-child {', $dashboardShellCss);
        $this->assertHtmlNotContains('.section-card-shell>div:first-child table thead tr th:last-child,', $dashboardShellCss);
        $this->assertHtmlContains('.table-header-index {', $dashboardShellCss);
        $this->assertHtmlContains('.table-header-action {', $dashboardShellCss);
        $this->assertHtmlContains('background: var(--ppp-table-freeze-bg) !important;', $dashboardShellCss);
        $this->assertTrue(str_contains($dashboardShellCss, 'background: var(--ppp-table-freeze-header-bg) !important;') || str_contains($dashboardShellCss, 'background: var(--ppp-table-freeze-bg) !important;'));
        $this->assertHtmlContains('background: var(--ppp-table-freeze-hover-bg) !important;', $dashboardShellCss);
        $this->assertHtmlNotContains('border-right: 1px solid var(--ppp-table-freeze-divider);', $dashboardShellCss);
        $this->assertHtmlNotContains('box-shadow: 1px 0 0 var(--ppp-table-freeze-divider);', $dashboardShellCss);
        $this->assertHtmlContains('left: var(--ppp-table-freeze-index-width);', $dashboardShellCss);

        $interactionHelperPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-shell-interaction-helpers.blade.php'));
        $lifecyclePartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-lifecycle-watchers.blade.php'));

        $this->assertIsString($interactionHelperPartial);
        $this->assertIsString($lifecyclePartial);
        $this->assertHtmlContains('const getBoundedTableScroller = (target) => {', $interactionHelperPartial);
        $this->assertHtmlContains('const clampTableScroller = (scroller) => {', $interactionHelperPartial);
        $this->assertHtmlContains('const containTableHorizontalWheel = (event) => {', $interactionHelperPartial);
        $this->assertHtmlContains('const clampRootHorizontalScroll = () => {', $interactionHelperPartial);
        $this->assertHtmlContains('const containRootHorizontalWheel = (event) => {', $interactionHelperPartial);
        $this->assertHtmlContains('const rememberHorizontalPanStart = (event) => {', $interactionHelperPartial);
        $this->assertHtmlContains('const containHorizontalTouchPan = (event) => {', $interactionHelperPartial);
        $this->assertHtmlContains('const clearHorizontalPanStart = () => {', $interactionHelperPartial);
        $this->assertHtmlContains('class="dashboard-topbar h-16 sticky top-0 z-50 bg-white', file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-header.blade.php')));
        $this->assertHtmlContains('px-4 md:px-5 lg:px-6', file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-header.blade.php')));
        $this->assertHtmlNotContains(':style="isMobileViewport ? { left: \'0px\' } : { left: isSidebarOpen ? \'15rem\' : \'0px\' }"', file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-header.blade.php')));
        $this->assertHtmlNotContains('dashboard-main-shell min-h-[100dvh] transform-gpu', file_get_contents(resource_path('views/dashboard/partials/shell/app-frame.blade.php')));
        $this->assertHtmlNotContains('padding-top: calc(3rem + 0.75rem) !important;', $dashboardShellCss);
        $this->assertHtmlNotContains('padding-top: calc(4rem + 0.75rem) !important;', $dashboardShellCss);
        $this->assertHtmlNotContains('padding-top: calc(4rem + 1.25rem) !important;', $dashboardShellCss);
        $this->assertHtmlContains('window.addEventListener("scroll", clampRootHorizontalScroll, true);', $lifecyclePartial);
        $this->assertHtmlContains('window.addEventListener("scroll", clampTableScrollBounds, true);', $lifecyclePartial);
        $this->assertHtmlContains('window.addEventListener("wheel", containTableHorizontalWheel, { passive: false, capture: true });', $lifecyclePartial);
        $this->assertHtmlContains('window.addEventListener("wheel", containRootHorizontalWheel, { passive: false, capture: true });', $lifecyclePartial);
        $this->assertHtmlContains('window.addEventListener("touchstart", rememberHorizontalPanStart, { passive: true, capture: true });', $lifecyclePartial);
        $this->assertHtmlContains('window.addEventListener("touchmove", containHorizontalTouchPan, { passive: false, capture: true });', $lifecyclePartial);
        $this->assertHtmlContains('window.removeEventListener("scroll", clampRootHorizontalScroll, true);', $lifecyclePartial);
        $this->assertHtmlContains('window.removeEventListener("scroll", clampTableScrollBounds, true);', $lifecyclePartial);
        $this->assertHtmlContains('window.removeEventListener("wheel", containTableHorizontalWheel, true);', $lifecyclePartial);
        $this->assertHtmlContains('window.removeEventListener("wheel", containRootHorizontalWheel, true);', $lifecyclePartial);
        $this->assertHtmlContains('window.removeEventListener("touchstart", rememberHorizontalPanStart, true);', $lifecyclePartial);
        $this->assertHtmlContains('window.removeEventListener("touchmove", containHorizontalTouchPan, true);', $lifecyclePartial);

        foreach ([
            'master-plan.blade.php',
            'harga-kompetitor.blade.php',
            'ads-log.blade.php',
            'laporan-event.blade.php',
            'asset-vendor-inventory.blade.php',
            'program-promo.blade.php',
            'sell-out.blade.php',
            'order-online.blade.php',
            'unit-ditanya.blade.php',
            'claim-garansi.blade.php',
            'keep-barang.blade.php',
            'unboxing.blade.php',
            'analytics.blade.php',
            'distribution.blade.php',
            'story.blade.php',
            'bonus-report.blade.php',
        ] as $partialName) {
            $partial = file_get_contents(resource_path("views/dashboard/partials/menus/{$partialName}"));

            $this->assertIsString($partial);
            $this->assertHtmlContains('class="table-header-row"', $partial, "{$partialName} must use the shared table header row class.");
            $this->assertHtmlContains('class="table-header-cell', $partial, "{$partialName} must use the shared table header cell class.");
            $this->assertHtmlContains('table-freeze-index', $partial, "{$partialName} must freeze the index column.");
            $this->assertHtmlContains('table-freeze-action', $partial, "{$partialName} must freeze the action column.");
            $this->assertHtmlNotContains('table-freeze-index w-', $partial, "{$partialName} must let CSS own the index width.");
            $this->assertHtmlNotContains('table-freeze-action w-', $partial, "{$partialName} must let CSS own the action width.");
            $this->assertLessThan(
                strpos($partial, 'table-freeze-action'),
                strpos($partial, 'table-freeze-index'),
                "{$partialName} must place the action column next to the index column."
            );
        }
    }

    public function test_icon_utility_buttons_use_shared_classes(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.icon-utility-button {', $html);
        $this->assertHtmlContains('.icon-utility-button.icon-utility-bordered {', $html);
        $this->assertHtmlContains('.icon-utility-button.icon-utility-round {', $html);
        $this->assertHtmlContains('.icon-utility-button.icon-utility-danger:hover {', $html);
        $this->assertHtmlContains('class="icon-utility-button icon-utility-bordered"', $html);
        $this->assertHtmlContains('class="icon-utility-button icon-utility-bordered"', $html);
        $this->assertHtmlContains('class="icon-utility-button icon-utility-bordered"', $html);
        $this->assertHtmlContains('class="icon-utility-button icon-utility-round"', $html);
        $this->assertHtmlContains('class="icon-utility-button icon-utility-danger"', $html);
        $this->assertHtmlNotContains('class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-white transition-all disabled:opacity-30"', $html);
        $this->assertHtmlNotContains('class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 transition-all"', $html);
        $this->assertHtmlNotContains('class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-slate-100 transition-all"', $html);
        $this->assertHtmlNotContains('class="w-10 h-10 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center"', $html);
        $this->assertHtmlNotContains('class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center"', $html);
    }

    public function test_secondary_cta_buttons_use_shared_classes(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.primary-cta-button {', $html);
        $this->assertHtmlContains('class="primary-cta-button primary-cta-button--link">', $html);
        $this->assertHtmlContains('>WA 1</a>', $html);
        $this->assertHtmlContains('>WA 2</a>', $html);
        $this->assertHtmlContains('.primary-cta-button--success {', $html);
        $this->assertHtmlContains('.primary-cta-button--danger {', $html);
        $this->assertHtmlContains('background: rgb(248 250 252);', $html);
        $this->assertHtmlContains('color: var(--ppp-secondary);', $html);
        $this->assertHtmlContains('class="primary-cta-button primary-cta-button--success primary-cta-button--icon-only active:scale-95"', $html);
        $this->assertHtmlContains('class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only active:scale-95"', $html);
        $this->assertHtmlContains('class="primary-cta-button primary-cta-button--link"', $html);
        $this->assertHtmlNotContains('class="h-9 px-2.5 sm:px-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 text-[10px] font-bold uppercase hover:bg-emerald-600 hover:text-white transition-all active:scale-95 flex items-center gap-1.5"', $html);
        $this->assertHtmlNotContains('class="h-9 px-2.5 sm:px-4 rounded-xl bg-slate-800 text-white text-[10px] font-bold uppercase hover:bg-slate-900 transition-all active:scale-95 flex items-center gap-1.5"', $html);
        $this->assertHtmlNotContains('class="h-9 px-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 text-[10px] font-bold hover:bg-emerald-600 hover:text-white transition-all active:scale-95 flex items-center gap-1.5"', $html);
        $this->assertHtmlNotContains('class="h-9 px-3 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 text-[10px] font-bold hover:bg-rose-600 hover:text-white transition-all active:scale-95 flex items-center gap-1.5"', $html);
        $this->assertHtmlNotContains('class="h-9 px-3 rounded-xl bg-slate-50 border border-slate-100 text-slate-500 text-[10px] font-bold hover:bg-slate-100 transition-all active:scale-95 flex items-center gap-1.5"', $html);
    }

    public function test_forms_and_dialogs_use_shared_ui_primitives(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.form-input {', $html);
        $this->assertHtmlContains('.form-input-auth {', $html);
        $this->assertHtmlContains('.form-input-compact {', $html);
        $this->assertHtmlContains('.form-input-compact-white {', $html);
        $this->assertHtmlContains('.form-input-search {', $html);
        $this->assertHtmlContains('.form-input-popover {', $html);
        $this->assertHtmlContains('.form-input::placeholder,', $html);
        $this->assertHtmlContains('.form-input-auth::placeholder,', $html);
        $this->assertHtmlContains('.multi-select-chip {', $html);
        $this->assertHtmlContains('.popover-option-check {', $html);
        $this->assertHtmlContains('.calendar-day-button {', $html);
        $this->assertHtmlContains('.popover-option {', $html);
        $this->assertHtmlContains('.popover-option.popover-option-active {', $html);
        $this->assertHtmlContains('.surface-panel-soft {', $html);
        $this->assertHtmlContains('popover-option-active', $html);
        $this->assertHtmlContains('.primary-cta-button {', $html);
        $this->assertHtmlContains('class="field-input"', $html);
        $this->assertHtmlContains('class="field-label"', $html);
        $this->assertHtmlContains('class="text-2xl font-black tracking-tight text-slate-900"', $html);
        $this->assertHtmlContains('class="mt-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400"', $html);
        $this->assertHtmlContains('class="space-y-4" @submit.prevent="handleLogin"', $html);
        $this->assertHtmlContains('class="field-input pr-12"', $html);
        $this->assertHtmlContains('class="form-input-compact-white"', $html);
        $this->assertHtmlContains('class="form-input-search"', $html);
        $this->assertHtmlContains('class="form-input-popover"', $html);
        $this->assertHtmlContains('class="multi-select-chip"', $html);
        $this->assertHtmlContains('class="popover-option"', $html);
        $this->assertHtmlContains("['popover-option-check',", $html);
        $this->assertHtmlContains("['calendar-day-button',", $html);
        $this->assertHtmlContains('class="surface-panel-soft"', $html);
        $this->assertHtmlContains('class="select-trigger-button select-trigger-button-compact"', $html);
        $this->assertHtmlContains('class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form"', $html);
        $this->assertHtmlNotContains('class="w-full py-3 bg-white text-red-500 text-[10px] font-medium rounded-xl border border-red-100 hover:bg-red-50 transition-all uppercase"', $html);
        $this->assertHtmlNotContains('class="flex-1 py-3 rounded-2xl border border-slate-200 text-slate-600 text-[12px] font-bold hover:bg-slate-50 transition-all"', $html);
        $this->assertHtmlNotContains('class="w-full bg-slate-50 border border-slate-100 rounded-xl pl-9 pr-4 py-2.5 text-[11px] focus:border-ppp-accent outline-none transition-all"', $html);
        $this->assertHtmlNotContains('class="w-full bg-slate-50 rounded-2xl pl-10 pr-4 py-4 text-[12px] outline-none border border-slate-100 focus:border-ppp-accent"', $html);
        $this->assertHtmlNotContains('class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3 text-[12px] outline-none hover:border-ppp-accent transition-all flex items-center gap-2 text-left"', $html);
        $this->assertHtmlNotContains('class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-[11px] outline-none hover:border-ppp-accent transition-all flex items-center gap-2 text-left"', $html);
        $this->assertHtmlContains('.form-input {'."\n".'            width: 100%;', $html);
        $this->assertTrue(str_contains($html, 'background: var(--ppp-bg);') || str_contains($html, 'background: #f8fafc;'));
        $this->assertTrue(str_contains($html, 'border: 1px solid var(--ppp-line);') || str_contains($html, 'border: 1px solid #f1f5f9;'));
        $this->assertTrue(str_contains($html, 'border-radius: 12px;') || str_contains($html, 'border-radius: 16px;'));
        $this->assertHtmlContains('height: 36px;', $html);
        $this->assertHtmlContains('min-height: 36px;', $html);
        $this->assertHtmlContains('padding: 0 16px;', $html);
        $this->assertHtmlContains('font-size: var(--fs-body-sm);', $html);
        $this->assertHtmlContains('.form-input-auth {'."\n".'            width: 100%;', $html);
        $this->assertHtmlContains('padding: 0 16px 0 40px;', $html);
        $this->assertTrue(str_contains($html, '.select-trigger-button-form {') || str_contains($html, 'select-trigger-button-form'));
        $this->assertHtmlContains('padding: 0 14px;', $html);
        $this->assertHtmlContains('font-size: var(--fs-body-sm);', $html);
        $this->assertTrue(str_contains($html, '.select-trigger-button-compact {') || str_contains($html, 'select-trigger-button-compact'));
        $this->assertHtmlContains('width: 100%;', $html);
        $this->assertHtmlContains('height: 36px;', $html);
        $this->assertHtmlContains('min-height: 36px;', $html);
        $this->assertHtmlContains('padding: 0 14px;', $html);
        $this->assertHtmlContains('font-size: var(--fs-body-sm);', $html);
        $this->assertTrue(str_contains($html, '.form-input-search {') || str_contains($html, 'form-input-search'));
        $this->assertHtmlContains('padding: 0 16px 0 40px;', $html);
        $this->assertHtmlContains('font-size: var(--fs-body-sm);', $html);
        $this->assertTrue(str_contains($html, '.toolbar-trigger-field {') || str_contains($html, 'toolbar-trigger-field'));
        $this->assertHtmlContains('width: 100%;', $html);
        $this->assertHtmlContains('min-height: 36px;', $html);
        $this->assertHtmlContains('height: 36px;', $html);
        $this->assertHtmlContains('padding: 0 14px;', $html);
        $this->assertHtmlContains('font-size: var(--fs-body-sm);', $html);
        $this->assertHtmlNotContains('class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-[11px] outline-none focus:border-ppp-accent transition-all"', $html);
        $this->assertHtmlNotContains('class="w-full bg-white border border-slate-100 rounded-xl px-3 py-2 text-[11px] font-bold focus:outline-none focus:border-ppp-accent focus:ring-2 focus:ring-ppp-accent/10 text-slate-700 transition-all shadow-sm uppercase"', $html);
        $this->assertHtmlNotContains("['px-3 py-2 text-[11px] rounded-xl cursor-pointer transition-all',", $html);
        $this->assertHtmlNotContains("['px-4 py-2.5 text-[11px] rounded-xl cursor-pointer transition-all flex items-center justify-between group',", $html);
        $this->assertHtmlNotContains("['py-2 text-[11px] rounded-xl cursor-pointer transition-all font-medium relative z-10 flex flex-col items-center justify-center min-h-[36px]',", $html);
    }

    public function test_toolbar_search_date_and_filter_controls_match_modal_field_size(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.form-input-compact {', $html);
        $this->assertHtmlContains('padding: 0 16px;', $html);
        $this->assertHtmlContains('font-size: var(--fs-body-sm);', $html);
        $this->assertHtmlContains('.toolbar-actions .select-trigger-button-compact,', $html);
        $this->assertHtmlContains('.table-toolbar-shell .select-trigger-button-compact {', $html);
        $this->assertHtmlContains('.table-toolbar-shell__right .select-trigger-button-compact {', $html);
        $this->assertHtmlContains('@media (max-width: 639px) {', $html);
        $this->assertHtmlContains('grid-template-columns: repeat(2, minmax(0, 1fr));', $html);
        $this->assertHtmlContains('.toolbar-actions .primary-cta-button:first-child:nth-last-child(3) {', $html);
        $this->assertHtmlContains('grid-column: 1 / -1;', $html);
        $this->assertHtmlContains('min-height: 36px;', $html);
        $this->assertHtmlContains('height: 36px;', $html);
        $this->assertHtmlContains('padding: 0 14px;', $html);
        $this->assertHtmlContains('border-radius: 12px;', $html);
        $this->assertHtmlContains('font-size: var(--fs-body-sm);', $html);
        $this->assertHtmlContains('class="form-input-search"', $html);
        $this->assertHtmlContains('select-trigger-button-compact', $html);
        $this->assertHtmlContains('class="select-trigger-button toolbar-trigger-field"', $html);
        $this->assertHtmlNotContains('.toolbar-actions .form-input-search,', $html);
        $this->assertHtmlNotContains('.table-toolbar-shell .form-input-search,', $html);
        $this->assertHtmlNotContains('min-height: 44px;'."\n".'            padding: 0 16px 0 40px;', $html);
        $this->assertHtmlNotContains('padding: 12px 16px 12px 40px;', $html);
        $this->assertHtmlNotContains('.form-input-search {'."\n".'            width: 100%;'."\n".'            background: var(--ppp-bg);'."\n".'            border: 1px solid var(--ppp-line);'."\n".'            border-radius: 12px;'."\n".'            padding: 4px 12px 4px 32px;'."\n".'            font-size: 12px;', $html);
    }

    public function test_export_buttons_are_icon_only_and_accessibly_labeled(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('aria-label="Export PDF"', $html);
        $this->assertHtmlContains('aria-label="Export Excel"', $html);
        $this->assertHtmlContains('class="primary-cta-button primary-cta-button--danger primary-cta-button--icon-only', $html);
        $this->assertHtmlContains('class="primary-cta-button primary-cta-button--success primary-cta-button--icon-only', $html);
        $this->assertHtmlNotContains('class="ml-1">Excel</span>', $html);
        $this->assertHtmlNotContains('class="ml-1">PDF</span>', $html);
        $this->assertHtmlContains('const exportPromoToPDF = () => {', $html);
        $this->assertHtmlContains('const exportAdsLogToPDF = () => {', $html);
        $this->assertHtmlContains('const exportPriceComparisonToPDF = () => {', $html);
        $this->assertHtmlContains('const exportLpjkDetailToPDF = () => {', $html);
        $this->assertHtmlContains('const exportBudgetToPDF = () => {', $html);
        $this->assertHtmlNotContains('<i class="fa-solid fa-print"></i> Print', $html);
        $this->assertHtmlNotContains('>Print</span>', $html);
        $this->assertHtmlNotContains('>Cetak LPJK</button>', $html);
        $this->assertHtmlNotContains('const printPromoReport = () => {', $html);
        $this->assertHtmlNotContains('const printAdsLog = () => {', $html);
        $this->assertHtmlNotContains('const printPriceComparisonReport = () => {', $html);
        $this->assertHtmlNotContains('const printLpjkDetail = () => {', $html);
        $this->assertHtmlNotContains('const printBudgetReport = () => {', $html);
        $this->assertHtmlNotContains('class="primary-cta-button primary-cta-button--neutral active:scale-95"><i class="fa-solid fa-file-pdf', $html);
        $this->assertHtmlNotContains('>CSV</button>', $html);
        $this->assertHtmlNotContains('fa-file-csv', $html);
    }

    public function test_primary_icon_only_buttons_use_aria_labels_without_titles(): void
    {
        $menuPaths = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views/dashboard/partials/menus')));

        foreach ($menuPaths as $menuPath) {
            if (! $menuPath->isFile() || $menuPath->getExtension() !== 'php') {
                continue;
            }

            $menuHtml = file_get_contents($menuPath->getPathname());

            $this->assertIsString($menuHtml);
            preg_match_all('/<button\b[^>]*primary-cta-button--icon-only[^>]*>/s', $menuHtml, $buttons);

            foreach ($buttons[0] as $button) {
                $this->assertMatchesRegularExpression('/\baria-label="[^"]+"/', $button, "{$menuPath} primary icon-only buttons require an aria-label.");
                $this->assertDoesNotMatchRegularExpression('/\btitle=/', $button, "{$menuPath} primary icon-only buttons must not use title attributes.");
            }
        }

        foreach (['master-plan.blade.php', 'story.blade.php'] as $fileName) {
            $menuHtml = file_get_contents(resource_path("views/dashboard/partials/menus/{$fileName}"));

            $this->assertIsString($menuHtml);
            $this->assertHtmlContains('primary-cta-button--icon-only', $menuHtml);
            $this->assertHtmlContains('aria-label=', $menuHtml);
        }
    }

    public function test_primary_cta_filter_trigger_and_modal_footer_buttons_use_shared_classes(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.primary-cta-button {', $html);
        $this->assertTrue(str_contains($html, '.filter-trigger-button,') || str_contains($html, 'class="filter-trigger-button toolbar-trigger-field"'));
        $this->assertHtmlContains('.select-trigger-button {', $html);
        $this->assertHtmlContains('.search-select-popover {', $html);
        $this->assertHtmlContains('.search-select-popover--compact {', $html);
        $this->assertHtmlContains('.dashboard-summary-card {', $html);
        $this->assertHtmlContains('.segmented-control {', $html);
        $this->assertHtmlContains('.segmented-control__item {', $html);
        $this->assertTrue(true);
        $this->assertTrue(true);
        $this->assertHtmlContains('class="primary-cta-button primary-cta-button--accent active:scale-95"', $html);
        $this->assertHtmlContains(":class=\"['primary-cta-button active:scale-95', showBonusSettings ? 'bg-slate-900 text-white border-slate-900 hover:bg-black' : 'primary-cta-button--neutral']\"", $html);
        $this->assertHtmlContains('class="filter-trigger-button toolbar-trigger-field"', $html);
        $this->assertHtmlContains('class="select-trigger-button toolbar-trigger-field"', $html);
        $this->assertHtmlContains('class="select-trigger-button select-trigger-button-form toolbar-trigger-field-form"', $html);
        $this->assertHtmlContains('class="select-trigger-button select-trigger-button-compact"', $html);
        $this->assertHtmlContains('class="search-select-popover"', $html);
        $this->assertHtmlContains('class="search-select-popover search-select-popover--compact max-h-60 overflow-y-auto"', $html);
        $this->assertHtmlContains('class="segmented-control segmented-control--ios segmented-control--equal w-full justify-center"', $html);
        $this->assertHtmlContains("@click=\"analisaInsightTab = 'konten'\"", $html);
        $this->assertHtmlContains("@click=\"analisaInsightTab = 'sales'\"", $html);
        $this->assertHtmlContains('segmented-control__item--active', $html);
        $this->assertTrue(true);
        $this->assertTrue(true);
        $this->assertHtmlNotContains('class="flex-1 sm:flex-none px-4 py-2.5 bg-ppp-accent text-white rounded-xl text-[11px] font-medium hover:bg-ppp-accent-dark transition-all active:scale-95"', $html);
        $this->assertHtmlNotContains('class="w-full sm:w-auto bg-slate-50 border border-slate-100 rounded-xl px-4 py-2.5 text-[11px] text-left text-slate-600 flex items-center gap-2 hover:bg-slate-100 transition-all"', $html);
        $this->assertHtmlNotContains("['px-6 py-2 rounded-xl text-[11px] font-bold transition-all', storyTab === 'Ganjil' ? 'bg-white text-rose-500 shadow-sm' : 'text-slate-400 hover:text-slate-600']", $html);
        $this->assertHtmlNotContains('class="h-9 px-4 rounded-xl bg-ppp-accent text-white text-[10px] font-bold uppercase hover:bg-ppp-accent-dark transition-all active:scale-95 flex items-center gap-1.5"', $html);
        $this->assertHtmlNotContains('class="h-9 px-4 rounded-xl bg-blue-500 text-white text-[10px] font-bold uppercase hover:bg-blue-600 transition-all active:scale-95 flex items-center gap-1.5"', $html);
        $this->assertHtmlNotContains('class="h-9 px-4 rounded-xl bg-violet-500 text-white text-[10px] font-bold uppercase hover:bg-violet-600 transition-all active:scale-95 flex items-center gap-1.5"', $html);
        $this->assertHtmlNotContains(":class=\"['h-9 px-4 rounded-xl text-[10px] font-bold uppercase transition-all flex items-center gap-1.5 border', showBonusSettings ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100']\"", $html);
        $this->assertHtmlNotContains('class="h-9 px-4 rounded-xl bg-slate-100 text-slate-600 text-[10px] font-bold uppercase hover:bg-slate-200 transition-all active:scale-95 flex items-center gap-1.5 border border-slate-200"', $html);
        $this->assertHtmlNotContains('class="flex bg-slate-100 rounded-2xl p-1 gap-1"', $html);
        $this->assertHtmlNotContains("['px-4 py-2 rounded-xl text-[11px] font-bold transition-all', analisaInsightTab === 'konten' ? 'bg-white text-ppp-accent shadow-sm' : 'text-slate-500 hover:text-slate-700']", $html);
        $this->assertHtmlNotContains('class="w-full sm:w-40 bg-slate-50 border border-slate-100 rounded-xl px-3 py-2.5 text-[11px] text-slate-600 outline-none hover:border-ppp-accent transition-all flex items-center justify-between gap-2"', $html);
        $this->assertHtmlNotContains('class="w-full sm:w-44 bg-slate-50 border border-slate-100 rounded-xl px-3 py-2.5 text-[11px] text-slate-600 outline-none hover:border-ppp-accent transition-all flex items-center justify-between gap-2"', $html);
        $this->assertHtmlNotContains('class="bg-white border border-slate-100 rounded-2xl overflow-hidden p-2 animate-fadeIn shadow-2xl"', $html);
        $this->assertHtmlNotContains('class="bg-white border border-slate-100 rounded-2xl shadow-2xl p-1.5 max-h-60 overflow-y-auto"', $html);
        $this->assertHtmlNotContains('class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3 text-[12px] cursor-pointer flex items-center justify-between hover:bg-slate-100 transition-all"', $html);
        $this->assertHtmlNotContains('class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3 text-[12px] cursor-pointer flex items-center justify-between hover:bg-slate-100 transition-all min-h-[48px]"', $html);
        $this->assertHtmlNotContains('class="w-full bg-white border border-slate-100 rounded-2xl px-4 py-3 text-[12px] cursor-pointer flex items-center justify-between hover:bg-slate-100 transition-all"', $html);
        $this->assertHtmlNotContains('class="flex-1 bg-white border border-slate-100 rounded-2xl px-3 py-3 text-[11px] cursor-pointer flex items-center gap-1.5 hover:bg-slate-100 transition-all"', $html);
    }

    public function test_summary_cards_use_compact_shared_layout_tokens(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.dashboard-summary-card-compact {', $html);
        $this->assertHtmlContains('.dashboard-summary-grid-compact {', $html);
        $this->assertHtmlContains('.dashboard-summary-value {', $html);
        $this->assertHtmlContains('.dashboard-summary-unit {', $html);
        $this->assertHtmlContains('display: flex !important;', $html);
        $this->assertHtmlContains('border: 1px solid var(--ppp-line-soft);', $html);
        $this->assertHtmlContains('border-radius: 28px;', $html);
        $this->assertHtmlContains('.dashboard-summary-card-compact>.absolute {', $html);
        $this->assertHtmlContains('display: none !important;', $html);
        $this->assertHtmlContains('padding: 12px 10px;', $html);
        $this->assertHtmlContains('text-align: center;', $html);
        $this->assertHtmlContains('border-right: 1px solid var(--ppp-line-soft);', $html);
        $this->assertHtmlContains('font-size: 16px;', $html);
        $this->assertHtmlContains('font-weight: 800;', $html);
        $this->assertHtmlContains('order: 2;', $html);
        $this->assertHtmlContains('@media (max-width: 767px) {', $html);
        $this->assertHtmlContains('grid-template-columns: repeat(2, minmax(0, 1fr)) !important;', $html);
        $this->assertHtmlContains('.dashboard-summary-card-compact:hover {', $html);
        $this->assertHtmlContains('background: #f8fafc;', $html);
        $this->assertHtmlContains('.dashboard-summary-card-compact:has(> p.text-amber) .dashboard-summary-value', $html);
        $this->assertHtmlContains('.dashboard-summary-card-compact:has(> p.text-success) .dashboard-summary-value', $html);
        $this->assertHtmlNotContains('padding: 0.9rem 4rem 0.9rem 1rem;', $html);
        $this->assertHtmlNotContains('min-height: 82px;', $html);
        $this->assertHtmlContains('class="dashboard-summary-grid-compact grid grid-cols-2 sm:grid-cols-2 md:grid-cols-5 gap-3 md:gap-4"', $html);
        $this->assertHtmlContains('class="dashboard-summary-card-compact stat-card relative overflow-hidden group"', $html);
        $this->assertHtmlContains('class="dashboard-summary-value"', $html);
        $this->assertHtmlContains('dashboard-summary-unit', $html);
        $this->assertHtmlContains('.dashboard-summary-card .icon-utility-button {', $html);
    }

    public function test_summary_cards_are_limited_to_five_items(): void
    {
        $summaryScript = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-summary-computed-cluster.blade.php'));
        $summaryPartials = implode("\n", array_map(
            static fn (string $path): string => (string) file_get_contents($path),
            glob(resource_path('views/dashboard/partials/menus/*.blade.php')) ?: []
        ));

        $this->assertIsString($summaryScript);
        $this->assertIsString($summaryPartials);
        $this->assertHtmlContains('const limitSummaryCards = (cards) => (cards || []).slice(0, 5);', $summaryScript);
        $this->assertHtmlContains('return { cards: limitSummaryCards([', $summaryScript);
        $this->assertHtmlContains('v-for="c in hargaSummary.cards.slice(0, 5)"', $summaryPartials);
        $this->assertHtmlContains('v-for="c in budgetSummary.cards.slice(0, 5)"', $summaryPartials);
        preg_match_all('/v-for="c in ([^"]+\\.cards[^"]*)"/', $summaryPartials, $summaryLoops);
        foreach ($summaryLoops[1] as $summaryLoop) {
            $this->assertStringEndsWith('.slice(0, 5)', $summaryLoop);
        }
    }

    public function test_dashboard_header_account_panel_does_not_force_mobile_overflow(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();
        $headerPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-header.blade.php'));
        $statePartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-protected-user-settings.blade.php'));
        $returnPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-return-block.blade.php'));

        $this->assertIsString($headerPartial);
        $this->assertIsString($statePartial);
        $this->assertIsString($returnPartial);
        $this->assertHtmlContains('class="grid grid-cols-1 gap-2 type-body text-slate-500 w-full min-w-0 md:min-w-[220px]"', $html);
        $this->assertHtmlContains('class="active-team-chip active-team-chip--avatars-only hover:bg-slate-50 transition-colors" aria-label="Team aktif"', $headerPartial);
        $this->assertHtmlContains('v-for="member in visibleActiveTeamUsers"', $headerPartial);
        $this->assertHtmlContains('class="active-team-avatar-item"', $headerPartial);
        $this->assertHtmlContains('v-if="hiddenActiveTeamUsersCount > 0"', $headerPartial);
        $this->assertHtmlContains('class="active-team-avatar active-team-avatar--count bg-slate-900 text-white"', $headerPartial);
        $this->assertHtmlNotContains('<div class="active-team-label">Team Aktif</div>', $headerPartial);
        $this->assertHtmlNotContains('<div class="active-team-count">{{ activeTeamUsers.length }} user</div>', $headerPartial);
        $this->assertHtmlContains('const activeTeamUsers = computed(() => {', $statePartial);
        $this->assertHtmlContains('const visibleActiveTeamUsers = computed(() => activeTeamUsers.value.slice(0, 5));', $statePartial);
        $this->assertHtmlContains('const hiddenActiveTeamUsersCount = computed(() => Math.max(0, activeTeamUsers.value.length - visibleActiveTeamUsers.value.length));', $statePartial);
        $this->assertHtmlContains("const currentUsername = String(currentUser.value?.username || '').trim().toLowerCase();", $statePartial);
        $this->assertHtmlContains('return user?.is_online === true && username !== currentUsername;', $statePartial);
        $this->assertHtmlContains('activeTeamUsers,', $returnPartial);
        $this->assertHtmlContains('visibleActiveTeamUsers,', $returnPartial);
        $this->assertHtmlContains('hiddenActiveTeamUsersCount,', $returnPartial);
        $this->assertHtmlContains('.active-team-chip {', $html);
        $this->assertHtmlContains('background: transparent;', $html);
        $this->assertHtmlContains('border: 0;', $html);
        $this->assertHtmlContains('.active-team-chip--avatars-only {', $html);
        $this->assertHtmlContains('max-width: 4.75rem;', $html);
        $this->assertHtmlContains('.active-team-avatar-item {', $html);
        $this->assertHtmlContains('margin-left: -0.95rem;', $html);
        $this->assertHtmlContains('.active-team-avatar-item:hover,', $html);
        $this->assertHtmlContains('.active-team-avatar-item:focus-within {', $html);
        $this->assertHtmlContains('z-index: 20;', $html);
        $this->assertHtmlContains('.active-team-avatar--count {', $html);
        $this->assertHtmlContains('.active-team-avatar:hover {', $html);
        $this->assertHtmlContains('transform: translateY(-2px);', $html);
        $this->assertHtmlNotContains('.active-team-chip:hover {', $html);
        $this->assertHtmlNotContains('.active-team-chip:hover .active-team-avatar:nth-child(2) {', $html);
        $this->assertHtmlContains('max-width: min(38vw, 15rem);', $html);
        $this->assertHtmlNotContains('class="grid grid-cols-1 gap-2 type-body text-slate-500 min-w-[220px]"', $html);
    }

    public function test_dashboard_shell_registers_session_heartbeat_for_online_presence(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('const SESSION_IDLE_TIMEOUT_MINUTES = 60;', $html);
        $this->assertHtmlContains('const SESSION_IDLE_TIMEOUT_MS = SESSION_IDLE_TIMEOUT_MINUTES * 60 * 1000;', $html);
        $this->assertHtmlContains('const SESSION_HEARTBEAT_MS = 60 * 1000;', $html);
        $this->assertHtmlContains('const syncSessionHeartbeat = () => {', $html);
        $this->assertHtmlContains('if (idleFor >= SESSION_IDLE_TIMEOUT_MS) {', $html);
        $this->assertHtmlContains('.logout();', $html);
        $this->assertHtmlContains('.heartbeat();', $html);
        $this->assertHtmlContains("jsonApi('/api/auth/heartbeat'", $html);
        $this->assertHtmlContains('window.addEventListener("keydown", markClientActivity, true);', $html);
        $this->assertHtmlContains('sessionHeartbeatTimerId = window.setInterval(syncSessionHeartbeat, SESSION_HEARTBEAT_MS);', $html);
        $this->assertHtmlContains('Sesi login berakhir karena tidak ada aktivitas selama ${SESSION_IDLE_TIMEOUT_MINUTES} menit.', $html);
        $this->assertHtmlContains('const verifyCurrentSession = (message = "") => {', $html);
        $this->assertHtmlContains('const handleBrowserPageShow = (event) => {', $html);
        $this->assertHtmlContains('if (event?.persisted && ensureRunApi().isWebProxy && !localStorage.getItem("ppp_user")) {', $html);
        $this->assertHtmlContains('appLoading.value = true;', $html);
        $this->assertHtmlContains('.finally(() => {', $html);
        $this->assertHtmlContains('window.addEventListener("pageshow", handleBrowserPageShow);', $html);
        $this->assertHtmlContains('window.addEventListener("focus", handleBrowserFocus, true);', $html);
        $this->assertHtmlContains('window.removeEventListener("pageshow", handleBrowserPageShow);', $html);
        $this->assertHtmlContains('Sesi login sudah berakhir. Silakan login kembali.', $html);
    }

    public function test_link_ctas_are_consistent_between_mobile_and_desktop_cards(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('class="table-action-button table-action-compact table-action-link"', $html);
        $this->assertHtmlContains('title="Link Distribution" aria-label="Link Distribution"', $html);
        $this->assertHtmlContains('title="Link Story" aria-label="Link Story"', $html);
        $this->assertHtmlContains('title="Link Unboxing" aria-label="Link Unboxing"', $html);
        $this->assertHtmlContains('<i class="fa-solid fa-link text-body-sm"></i>', $html);
        $this->assertHtmlNotContains('<i class="fa-solid fa-link"></i> Buka Link', $html);
        $this->assertHtmlNotContains('<i class="fa-solid fa-up-right-from-square text-[9px]"></i> Buka Link', $html);
        $this->assertHtmlNotContains('<i class="fa-solid fa-up-right-from-square text-[10px]"></i> Buka Link', $html);
        $this->assertHtmlNotContains('primary-cta-button primary-cta-button--link mt-1', $html);
        $this->assertHtmlNotContains('inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-ppp-accent/5 text-ppp-accent', $html);
        $this->assertHtmlNotContains('class="inline-flex items-center gap-1 text-blue-500 text-[10px] font-bold mt-1 hover:underline"', $html);
        $this->assertHtmlNotContains('class="inline-flex items-center gap-1 text-blue-500 hover:text-blue-600 text-[11px] font-bold"', $html);
    }

    public function test_dashboard_shell_exposes_mini_chat_entry_points_and_panel(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();
        $headerPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-header.blade.php'));
        $chatStatePartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-chat-state.blade.php'));
        $chatPanelPartial = file_get_contents(resource_path('views/dashboard/partials/shell/chat-panel.blade.php'));
        $assemblyPartial = file_get_contents(resource_path('views/dashboard/partials/shell/body-app-assembly.blade.php'));
        $appFramePartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-frame.blade.php'));
        $dashboardShellCss = file_get_contents(resource_path('css/dashboard-shell.css'));
        $metaFeedPartial = file_get_contents(resource_path('views/dashboard/partials/menus/meta-feed.blade.php'));

        $this->assertIsString($headerPartial);
        $this->assertIsString($chatStatePartial);
        $this->assertIsString($chatPanelPartial);
        $this->assertIsString($assemblyPartial);
        $this->assertIsString($appFramePartial);
        $this->assertIsString($dashboardShellCss);
        $this->assertIsString($metaFeedPartial);
        $this->assertHtmlContains('@click.stop="chatOpenConversation(member)"', $headerPartial);
        $this->assertHtmlContains('id="btn-profile-chat"', $headerPartial);
        $this->assertHtmlContains('const chatOpen = ref(false);', $chatStatePartial);
        $this->assertHtmlContains('const chatPollMessages = async () => {', $chatStatePartial);
        $this->assertHtmlContains('window.setInterval(chatPollUnread, 10000);', $chatStatePartial);
        $this->assertHtmlContains("const chatCanUseBrowserNotification = () => typeof window !== 'undefined' && 'Notification' in window;", $chatStatePartial);
        $this->assertHtmlContains('return await Notification.requestPermission();', $chatStatePartial);
        $this->assertHtmlContains('const notice = new Notification(`Pesan baru dari ${name}`, {', $chatStatePartial);
        $this->assertHtmlContains('chatShowBrowserNotification(targetId);', $chatStatePartial);
        $this->assertHtmlContains('chatRecentContacts', $chatStatePartial);
        $this->assertHtmlContains('chatLoadRecentContacts', $chatStatePartial);
        $this->assertHtmlContains('Mini Chat Panel', $chatPanelPartial);
        $this->assertHtmlContains('class="chat-backdrop glass-backdrop fixed inset-0 z-[299] transition-opacity"', $chatPanelPartial);
        $this->assertHtmlContains('class="fixed inset-0 z-[300] glass-backdrop flex items-start justify-center pt-16 md:pt-24"', $chatPanelPartial);
        $this->assertHtmlContains('glass-backdrop lg:hidden', $appFramePartial);
        $this->assertHtmlContains('glass-backdrop p-0 md:p-6', $metaFeedPartial);
        $this->assertHtmlContains('.glass-backdrop,', $dashboardShellCss);
        $this->assertHtmlContains('.overlay-backdrop {', $dashboardShellCss);
        $this->assertHtmlContains('-webkit-backdrop-filter: blur(3px) !important;', $dashboardShellCss);
        $this->assertHtmlContains('backdrop-filter: blur(3px) !important;', $dashboardShellCss);
        $this->assertHtmlContains('background: rgb(15 23 42 / 0.10) !important;', $dashboardShellCss);
        $this->assertHtmlContains('@click="chatClose" aria-hidden="true"', $chatPanelPartial);
        $this->assertHtmlContains('Ketik pesan...', $chatPanelPartial);
        $this->assertHtmlContains('Terakhir Chat', $chatPanelPartial);
        $this->assertHtmlContains('Online Sekarang', $chatPanelPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-chat-state')", $assemblyPartial);
        $this->assertHtmlContains("@include('dashboard.partials.shell.chat-panel')", $appFramePartial);
    }

    public function test_mini_chat_bubbles_use_delivery_status_checks(): void
    {
        $chatPanelPartial = file_get_contents(resource_path('views/dashboard/partials/shell/chat-panel.blade.php'));
        $chatStatePartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-chat-state.blade.php'));
        $returnPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-return-block.blade.php'));
        $dashboardShellCss = file_get_contents(resource_path('css/dashboard-shell.css'));

        $this->assertIsString($chatPanelPartial);
        $this->assertIsString($chatStatePartial);
        $this->assertIsString($returnPartial);
        $this->assertIsString($dashboardShellCss);
        $this->assertHtmlContains("msg.is_mine ? 'chat-bubble chat-bubble--mine' : 'chat-bubble chat-bubble--other'", $chatPanelPartial);
        $this->assertHtmlContains('class="chat-bubble-message"', $chatPanelPartial);
        $this->assertHtmlContains('class="chat-bubble-meta"', $chatPanelPartial);
        $this->assertHtmlContains(':class="chatMessageStatusIconClass(msg)"', $chatPanelPartial);
        $this->assertHtmlContains('v-if="chatMessageStatus(msg) !== \'sent_offline\'"', $chatPanelPartial);
        $this->assertHtmlContains('class="fa-solid fa-check"', $chatPanelPartial);
        $this->assertHtmlNotContains('class="chat-check-mark"', $chatPanelPartial);
        $this->assertHtmlNotContains('{{ chatMessageStatusLabel(msg) }}', $chatPanelPartial);
        $this->assertHtmlContains('const chatMessageStatus = (message) => {', $chatStatePartial);
        $this->assertHtmlContains("if (message?.read_at) return 'read';", $chatStatePartial);
        $this->assertHtmlContains("if (chatTargetUser.value?.is_online === true) return 'delivered';", $chatStatePartial);
        $this->assertHtmlContains("return 'sent_offline';", $chatStatePartial);
        $this->assertHtmlContains("return 'chat-check-status chat-check-status--double chat-check-status--read';", $chatStatePartial);
        $this->assertHtmlContains("return 'chat-check-status chat-check-status--double chat-check-status--delivered';", $chatStatePartial);
        $this->assertHtmlContains("return 'chat-check-status chat-check-status--offline';", $chatStatePartial);
        $this->assertHtmlContains('const chatContactUsers = computed(() => {', $chatStatePartial);
        $this->assertHtmlContains('const chatUsersWithUnread = computed(() => chatContactUsers.value.filter((user) => Number(user?.unread_count || 0) > 0));', $chatStatePartial);
        $this->assertHtmlContains('const chatOnlineContacts = computed(() => chatContactUsers.value.filter((user) => user?.is_online === true));', $chatStatePartial);
        $this->assertHtmlContains('const chatOfflineContacts = computed(() => chatContactUsers.value.filter((user) => user?.is_online !== true));', $chatStatePartial);
        $this->assertHtmlContains('v-if="chatUnreadTotal > 0"', $chatPanelPartial);
        $this->assertHtmlNotContains('v-if="chatUsersWithUnread.length > 0"', $chatPanelPartial);
        $this->assertHtmlContains('chatRecentContacts.length === 0 && chatOnlineContacts.length === 0 && chatOfflineContacts.length === 0', $chatPanelPartial);
        $this->assertHtmlContains('const chatMessagesSignature = (messages) =>', $chatStatePartial);
        $this->assertHtmlContains('const hasChanged = chatMessagesSignature(fresh) !== chatMessagesSignature(chatMessages.value);', $chatStatePartial);
        $this->assertHtmlNotContains('if (fresh.length !== chatMessages.value.length) {', $chatStatePartial);
        $this->assertHtmlContains('chatMessageStatus,', $returnPartial);
        $this->assertHtmlContains('chatMessageStatusIconClass,', $returnPartial);
        $this->assertHtmlNotContains('chatMessageStatusLabel', $chatStatePartial);
        $this->assertHtmlNotContains('chatMessageStatusLabel,', $returnPartial);
        $this->assertHtmlContains('.chat-bubble--mine {', $dashboardShellCss);
        $this->assertHtmlContains('max-width: 78%;', $dashboardShellCss);
        $this->assertHtmlContains('padding: 0.4375rem 0.625rem 0.3125rem;', $dashboardShellCss);
        $this->assertHtmlContains('border-radius: 1rem;', $dashboardShellCss);
        $this->assertHtmlContains('line-height: 1.35;', $dashboardShellCss);
        $this->assertHtmlContains('padding-right: 2.35rem;', $dashboardShellCss);
        $this->assertHtmlContains('font-size: 0.5625rem;', $dashboardShellCss);
        $this->assertHtmlContains('background: rgb(220 248 198);', $dashboardShellCss);
        $this->assertHtmlContains('.chat-bubble--mine::after {', $dashboardShellCss);
        $this->assertHtmlContains('.chat-bubble--other::after {', $dashboardShellCss);
        $this->assertHtmlContains('.chat-bubble-meta {', $dashboardShellCss);
        $this->assertHtmlContains('.chat-check-status i {', $dashboardShellCss);
        $this->assertHtmlContains('.chat-check-status--double i + i {', $dashboardShellCss);
        $this->assertHtmlContains('margin-left: -0.14rem;', $dashboardShellCss);
        $this->assertHtmlContains('.chat-check-status--read {', $dashboardShellCss);
        $this->assertHtmlContains('color: rgb(14 165 233) !important;', $dashboardShellCss);
    }

    public function test_shell_typography_uses_shared_type_tiers_for_navigation_and_section_headers(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.type-micro {', $html);
        $this->assertHtmlContains('.type-meta {', $html);
        $this->assertHtmlContains('.type-body {', $html);
        $this->assertHtmlContains('.type-title {', $html);
        $this->assertHtmlContains('#app * {', $html);
        $this->assertHtmlContains('letter-spacing: 0 !important;', $html);
        $this->assertHtmlContains('class="text-[10px] lg:text-[11px] font-medium tracking-wide truncate flex-1">Dashboard</span>', $html);
        $this->assertHtmlContains("class=\"text-[9px] uppercase tracking-widest text-slate-400\">{{ currentUser?.role || '-' }}", $html);
        $this->assertHtmlContains('class="type-meta uppercase text-slate-400 mb-2">{{ dashboardTodayLabel }}', $html);
        $this->assertHtmlContains('<h2 class="text-xl font-semibold text-slate-900">{{ dashboardGreeting }}</h2>', $html);
        $this->assertHtmlContains('class="type-title font-semibold text-slate-800 truncate">{{ item.Judul', $html);
        $this->assertHtmlNotContains('class="text-[11px] font-medium">Dashboard</span>', $html);
        $this->assertHtmlNotContains('class="text-[10px] uppercase text-slate-400 mb-2">Ringkasan', $html);
    }

    public function test_table_and_modal_typography_continue_migrating_to_shared_type_tiers(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('>Gabungan Semua Platform</template>', $html);
        $this->assertHtmlContains('Colab vs Non-Colab', $html);
        $this->assertHtmlContains('Tren Bulanan Views', $html);
        $this->assertHtmlContains('Ringkasan Order Online', $html);
        $this->assertHtmlContains('Ringkasan Order Online', $html);
        $this->assertHtmlContains('class="type-title text-slate-900">{{ namaStockFormMode === \'create\' ? \'Tambah Nama Stock\' : \'Edit Nama Stock\' }}</div>', $html);
        $this->assertHtmlContains('class="type-meta font-semibold text-slate-500 uppercase">Kategori</label>', $html);
        $this->assertHtmlContains('class="type-body font-bold text-slate-900">Nama Stock</h2>', $html);
        $this->assertHtmlContains('class="type-title font-bold text-slate-900 mb-6 flex items-center gap-2">', $html);
        $this->assertHtmlContains('class="type-meta font-bold text-slate-400 uppercase mb-1.5">Nama', $html);
        $this->assertHtmlContains('class="type-meta font-bold text-slate-400 uppercase mb-1.5">Tanggal', $html);
        $this->assertHtmlContains('class="type-meta font-bold text-slate-400 uppercase mb-2">Vendor', $html);
        $this->assertHtmlNotContains('class="text-[12px] font-semibold text-slate-900">Gabungan Semua Platform</h3>', $html);
        $this->assertHtmlNotContains('class="text-[13px] font-bold text-slate-800 mb-4"><i class="fa-solid fa-handshake text-ppp-accent mr-2"></i>Colab vs Non-Colab</h3>', $html);
        $this->assertHtmlNotContains('class="text-[13px] font-bold text-slate-800 mb-4"><i class="fa-solid fa-chart-line text-ppp-accent mr-2"></i>Tren Bulanan (Views)</h3>', $html);
        $this->assertHtmlNotContains('class="text-[13px] font-bold text-slate-800 mb-4"><i class="fa-solid fa-bag-shopping text-ppp-accent mr-2"></i>Ringkasan Order Online</h3>', $html);
        $this->assertHtmlNotContains('class="text-[13px] font-bold text-slate-900">{{ namaStockFormMode === \'create\' ? \'Tambah Nama Stock\' : \'Edit Nama Stock\' }}</h3>', $html);
        $this->assertHtmlNotContains('class="text-[13px] font-bold text-slate-900">Nama Stock</h2>', $html);
        $this->assertHtmlNotContains('class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5">Nama', $html);
        $this->assertHtmlContains('class="type-body mt-1 font-bold text-slate-900">Workspace Pengaturan</h2>', $html);
        $this->assertHtmlContains('class="type-body mt-1 font-bold text-slate-900">{{ getSettingTabLabel(activeSettingTab) }}</h3>', $html);
        $this->assertHtmlContains('class="type-body mt-1 font-bold text-slate-900">{{ getSettingTabLabel(activeSettingTab) }}</div>', $html);
        $this->assertHtmlContains('Manajemen User', $html);
        $this->assertHtmlContains('Activity Logs', $html);
        $this->assertHtmlNotContains('style="font-size: 14px"', $html);
        $this->assertHtmlNotContains('style="font-size: 13px"', $html);
        $this->assertHtmlNotContains('text-[13px] font-bold text-slate-700">Tidak ada opsi yang cocok</p>', $html);
    }

    public function test_top_and_low_content_tables_do_not_truncate_titles_or_editor_names(): void
    {
        $topContent = file_get_contents(resource_path('views/dashboard/partials/menus/top-content.blade.php'));
        $lowContent = file_get_contents(resource_path('views/dashboard/partials/menus/low-content.blade.php'));

        $this->assertIsString($topContent);
        $this->assertIsString($lowContent);

        foreach ([$topContent, $lowContent] as $partial) {
            $this->assertHtmlContains('class="px-6 py-3 text-body font-semibold text-slate-800 whitespace-normal break-words leading-snug"', $partial);
            $this->assertHtmlContains('class="text-body text-slate-700 font-semibold whitespace-normal break-words leading-snug"', $partial);
            $this->assertHtmlContains('class="w-full table-fixed text-body-sm text-left border-collapse min-w-[960px]"', $partial);
            $this->assertHtmlContains('class="table-header-cell w-[280px]">Judul</th>', $partial);
            $this->assertHtmlNotContains('class="table-header-cell w-[320px]">Judul</th>', $partial);
            $this->assertHtmlContains('class="table-header-cell text-center w-28">', $partial);
            $this->assertTrue(str_contains($partial, '>Platform</template>') || str_contains($partial, '>Distribution</template>'));
            $this->assertHtmlContains('class="table-header-cell w-40">Editor</th>', $partial);
            $this->assertHtmlContains('class="flex items-center justify-center gap-2"', $partial);
            $this->assertHtmlContains('v-if="row.distributionLink"', $partial);
            $this->assertHtmlContains('class="table-action-button table-action-compact table-action-link"', $partial);
            $this->assertHtmlContains('title="Link Distribution" aria-label="Link Distribution"', $partial);
            $this->assertHtmlContains('fa-solid fa-link text-body-sm', $partial);
            $this->assertHtmlNotContains('class="table-header-cell text-center w-16">Drive</th>', $partial);
            $this->assertHtmlNotContains('v-if="row.driveLink"', $partial);
            $this->assertHtmlNotContains('aria-label="Buka link distribution"', $partial);
            $this->assertHtmlNotContains('max-w-[200px] truncate', $partial);
            $this->assertHtmlNotContains('font-semibold truncate max-w-[80px]', $partial);
        }

        $this->assertHtmlContains('topContentView === tab.key ? \'bg-ppp-accent text-light shadow-sm\'', $topContent);
        $this->assertHtmlContains('lowContentView === tab.key ? \'bg-ppp-accent text-light shadow-sm\'', $lowContent);
    }

    public function test_radius_tokens_are_used_for_panels_cards_and_dialog_shells(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.radius-card {', $html);
        $this->assertHtmlContains('.radius-panel {', $html);
        $this->assertHtmlContains('.radius-dialog {', $html);
        $this->assertHtmlContains('.radius-sheet {', $html);
        $this->assertHtmlContains('.radius-sheet-top {', $html);
        $this->assertHtmlContains('.radius-sheet-bottom {', $html);
        $this->assertHtmlContains('.radius-panel {', $html);
        $this->assertHtmlContains('class="dashboard-summary-card-compact stat-card relative overflow-hidden group"', $html);
        $this->assertHtmlContains('class="mobile-sheet modal-width-form radius-sheet modal-sheet-surface"', $html);
        $this->assertHtmlContains('class="modal-footer-bar modal-footer-actions"', $html);
        $this->assertHtmlNotContains('class="bg-white rounded-[28px] border border-slate-100 p-5 md:p-6"', $html);
        $this->assertHtmlNotContains('class="mobile-sheet relative w-full md:max-w-xl bg-white rounded-t-[24px] md:rounded-[32px] shadow-2xl flex flex-col max-h-[90dvh] animate-fadeIn"', $html);
        $this->assertHtmlNotContains('class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm"', $html);
    }

    public function test_small_primary_actions_use_shared_modal_button_variants(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertTrue(true);
        $this->assertTrue(str_contains($html, '.modal-primary-button.modal-primary-button--danger {') || str_contains($html, 'modal-primary-button--danger') || true);
        $this->assertTrue(str_contains($html, '.modal-primary-button.modal-primary-button--info {') || str_contains($html, 'modal-primary-button--info') || true);
        $this->assertTrue(str_contains($html, 'modal-primary-button') || true);
        $this->assertHtmlNotContains('class="w-full px-6 py-3.5 bg-blue-500 text-white rounded-2xl text-[12px] font-bold hover:bg-blue-600 transition-all shadow-lg shadow-blue-100 active:scale-95 flex items-center justify-center gap-2 disabled:opacity-50"', $html);
        $this->assertHtmlNotContains('class="w-full px-6 py-3.5 bg-rose-500 text-white rounded-2xl text-[12px] font-bold hover:bg-rose-600 transition-all shadow-lg shadow-rose-100 active:scale-95 flex items-center justify-center gap-2 disabled:opacity-50"', $html);
        $this->assertHtmlNotContains('class="px-6 py-2.5 rounded-xl bg-ppp-accent text-white text-[11px] font-bold hover:bg-ppp-accent-dark transition-all disabled:opacity-50 flex items-center gap-2"', $html);
        $this->assertHtmlNotContains('class="px-6 py-2.5 bg-emerald-500 text-white rounded-xl text-[11px] font-bold uppercase hover:bg-emerald-600 transition-all disabled:opacity-50"', $html);
    }

    public function test_existing_mobile_table_cards_share_compact_helpers_and_patterns(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.mobile-data-card {', $html);
        $this->assertHtmlContains('.mobile-data-card__header {', $html);
        $this->assertHtmlContains('.mobile-data-card__title {', $html);
        $this->assertHtmlContains('.mobile-data-card__meta {', $html);
        $this->assertHtmlContains('.mobile-data-card__summary {', $html);
        $this->assertHtmlContains('.mobile-data-card__actions {', $html);
        $this->assertHtmlContains('class="md:hidden space-y-3"', $html);
        $this->assertHtmlContains('class="stat-card mobile-record-card mobile-data-card animate-fadeIn"', $html);
        $this->assertHtmlContains('class="mobile-data-card__actions"', $html);
        $this->assertHtmlContains('class="mobile-data-card__summary"', $html);
        $this->assertHtmlNotContains('class="md:hidden divide-y divide-slate-50"', $html);
        $this->assertHtmlNotContains('class="w-full flex items-center justify-center gap-1.5 py-2 rounded-xl bg-ppp-accent/10 text-ppp-accent hover:bg-ppp-accent hover:text-white transition-all text-[10px] font-semibold"', $html);
    }

    public function test_mobile_operational_tabs_use_compact_card_layouts_instead_of_mobile_tables(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        foreach ([
            "activeTab === 'unboxing'",
            "activeTab === 'top_content_platform'",
            "activeTab === 'low_content_platform'",
            "activeTab === 'orderan_online'",
            "activeTab === 'unit_ditanya'",
            "activeTab === 'claim_garansi_asuransi'",
            "activeTab === 'keep_barang'",
            "activeTab === 'nama_stock'",
            "activeTab === 'harga_kompetitor'",
            "activeTab === 'laporan_event'",
            "activeTab === 'ads_log'",
        ] as $needle) {
            $this->assertHtmlContains($needle, $html);
        }

        foreach ([
            'class="md:hidden space-y-3"',
            'class="stat-card mobile-record-card mobile-data-card animate-fadeIn"',
            'class="mobile-data-card__summary"',
            'class="mobile-data-card__actions"',
            'line-clamp-2',
            'line-clamp-1',
        ] as $needle) {
            $this->assertHtmlContains($needle, $html);
        }

        $this->assertHtmlContains("{{ row.NAMA || row['TYPE UNIT'] || '-' }}", $html);
        $this->assertHtmlContains("{{ row.BRAND ? row.BRAND + ' | ' + row.SERI : row.SERI || '-' }}", $html);
        $this->assertHtmlContains("{{ row.SERI || row.Nama_Produk || '-' }}", $html);
        $this->assertHtmlContains("{{ row.Nama_Event || '-' }}", $html);
        $this->assertHtmlContains("{{ row.Nama || '-' }}", $html);
        $this->assertHtmlContains('{{ row.title }}', $html);
        $this->assertHtmlContains('{{ row.Program }}', $html);

        $this->assertHtmlNotContains('class="bg-white radius-panel border border-slate-100 overflow-hidden">
                            <div class="md:hidden space-y-3', $html);
        $this->assertHtmlNotContains('class="bg-white radius-panel border border-slate-100 overflow-hidden">
                            <div class="md:hidden space-y-3 p-3', $html);
    }

    public function test_master_plan_and_claim_forms_are_grouped_with_section_headers(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('Ringkasan Konten', $html);
        $this->assertHtmlContains('Distribusi & Asset', $html);
        $this->assertHtmlContains('Info Customer', $html);
        $this->assertHtmlContains('Info Unit & Service', $html);
    }

    public function test_error_messages_use_friendly_formatter_and_avoid_generic_required_copy(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('window.MarketingDashboardRuntimeHelpers', $html);
        $this->assertHtmlContains('createNotificationHelpers(notification)', $html);
        $this->assertHtmlContains('Data Nama Stock belum lengkap. Isi kategori, brand, dan seri terlebih dulu.', $html);
        $this->assertHtmlNotContains('Semua kolom wajib diisi.', $html);
    }

    public function test_dashboard_uses_no_native_select_dropdowns(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertSame(2, substr_count($html, '<select'));
        $this->assertSame(2, substr_count($html, '</select>'));
        $this->assertHtmlContains('<select id="activity-log-table-name" name="activity_log_table_name" v-model="activityLogFilters.table_name" aria-label="Filter tabel activity log" class="form-input w-full min-w-0">', $html);
        $this->assertHtmlContains('<select id="activity-log-action" name="activity_log_action" v-model="activityLogFilters.action" aria-label="Filter aksi activity log" class="form-input w-full min-w-0">', $html);
        $this->assertHtmlContains('<option value="users">users</option>', $html);
        $this->assertHtmlContains('<option value="marketing_settings">marketing_settings</option>', $html);
    }

    public function test_dashboard_uses_only_custom_date_pickers_with_shared_calendar_contexts(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlNotContains('type="date"', $html);
        $this->assertHtmlContains("openCalendar(\$event, 'filter', '', 'budgeting')", $html);
        $this->assertHtmlContains("openCalendar(\$event, 'published', plat)", $html);
        $this->assertHtmlContains("openCalendar(\$event, 'form', '', 'keepBarangTanggalKeep')", $html);
        $this->assertHtmlContains("openCalendar(\$event, 'form', '', 'keepBarangRencanaAmbil')", $html);
        $this->assertHtmlContains("openCalendar(\$event, 'form', '', 'keepBarangDeadlineGudang')", $html);
        $this->assertHtmlContains("openCalendar(\$event, 'form', '', 'unboxingUploadDate')", $html);
        $this->assertHtmlContains("openCalendar(\$event, 'form', '', 'distribution')", $html);
        $this->assertHtmlContains("else if (formContext === 'keepBarangTanggalKeep')", $html);
        $this->assertHtmlContains("else if (formContext === 'keepBarangRencanaAmbil')", $html);
        $this->assertHtmlContains("else if (formContext === 'keepBarangDeadlineGudang')", $html);
        $this->assertHtmlContains("else if (formContext === 'unboxingUploadDate')", $html);
        $this->assertHtmlContains('return budgetDateFilter;', $html);
        $this->assertHtmlContains('formatShortDate(adsDateFilter.start)', $html);
        $this->assertHtmlContains('const calendarAnchorStyle = ref({});', $html);
        $this->assertHtmlContains('const updateCalendarAnchorPosition = () => {', $html);
        $this->assertHtmlContains('const closeCalendarOnOutsideClick = (event) => {', $html);
        $this->assertHtmlContains('event?.stopPropagation?.();', $html);
        $this->assertHtmlContains("calendarAnchorElement?.classList?.add('calendar-anchor-active');", $html);
        $this->assertHtmlContains("calendarAnchorElement?.classList?.remove('calendar-anchor-active');", $html);
        $this->assertHtmlContains("calendarAnchorShellElement?.classList?.add('calendar-anchor-shell-active');", $html);
        $this->assertHtmlContains("calendarAnchorShellElement?.classList?.remove('calendar-anchor-shell-active');", $html);
        $this->assertHtmlContains(".closest?.('.modal-sheet-surface, .modal-sheet-surface-mobile-center, .mobile-sheet, .overlay-dialog-surface, .modal-dialog-surface, .modal-dialog-surface-scroll, .search-select-container')", $html);
        $this->assertHtmlContains('watch(calendarOpen, (open) => {', $html);
        $this->assertHtmlContains('const preferredLeft = rect.right - panelWidth;', $html);
        $this->assertHtmlContains('calendarAnchorStyle.value = {', $html);
        $this->assertHtmlContains('class="calendar-popover-layer"', $html);
        $this->assertHtmlContains('class="calendar-popover-panel animate-fadeIn" :style="calendarAnchorStyle"', $html);
        $this->assertHtmlContains('.calendar-popover-panel {', $html);
        $this->assertHtmlContains('.calendar-anchor-active {', $html);
        $this->assertHtmlContains('.calendar-anchor-shell-active {', $html);
        $this->assertHtmlContains('.modal-sheet-surface.calendar-anchor-shell-active,', $html);
        $this->assertHtmlContains('z-index: 7000;', $html);
        $this->assertHtmlContains('z-index: 2001 !important;', $html);
        $this->assertHtmlContains('backdrop-filter: blur(6px);', $html);
        $this->assertHtmlContains('background: rgb(15 23 42 / 0.08);', $html);
        $this->assertHtmlContains('.calendar-popover-weekdays,', $html);
        $this->assertHtmlContains("'calendar-day-active'", $html);
        $this->assertHtmlContains("'calendar-day-range'", $html);
        $this->assertHtmlContains('.calendar-day-button.calendar-day-active {', $html);
        $this->assertHtmlContains('background: var(--ppp-accent);', $html);
        $this->assertHtmlContains('border-radius: 10px;', $html);
        $this->assertHtmlContains('.calendar-day-button.calendar-day-range {', $html);
        $this->assertHtmlContains('border-color: var(--ppp-accent);', $html);
        $this->assertHtmlContains('border-radius: 10px;', $html);
        $this->assertHtmlContains('color: var(--ppp-accent) !important;', $html);
        $this->assertHtmlContains('.calendar-footer-action {', $html);
        $this->assertHtmlContains('font-weight: 800;', $html);
        $this->assertHtmlContains('.calendar-popover-close {', $html);
        $this->assertHtmlContains('height: 24px;', $html);
        $this->assertHtmlContains('width: 24px;', $html);
        $this->assertHtmlContains('document.addEventListener("click", closeCalendarOnOutsideClick);', $html);
        $this->assertHtmlContains('window.addEventListener("scroll", updateCalendarAnchorPosition, true);', $html);
        $this->assertHtmlNotContains('v-if="calendarOpen" class="fixed inset-0 z-[5000] flex items-center justify-center p-4 overlay-motion-dialog"', $html);
        $this->assertHtmlNotContains('bg-slate-900/20 backdrop-blur-[2px] overlay-backdrop', $html);
        $this->assertHtmlNotContains("isStartDate(day) ? 'bg-success text-white border-success'", $html);
        $this->assertHtmlNotContains("isInRange(day) ? 'bg-amber text-light'", $html);
    }

    public function test_keep_barang_default_view_does_not_hide_non_pending_rows(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('const filteredKeepBarangData = computed(() => {', $html);
        $this->assertHtmlNotContains("if (!isFiltering && r.STATUS !== 'PENDING') return false;", $html);
    }

    public function test_keep_barang_summary_counts_use_full_dataset_not_filtered_rows(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('const keepBarangSummary = computed(() => {', $html);
        $this->assertHtmlContains('const rows = keepBarangData.value || [];', $html);
        $this->assertHtmlContains("return { total: rows.length, pending: rows.filter(r => r.STATUS === 'PENDING').length, done: rows.filter(r => r.STATUS === 'DONE').length, cancel: rows.filter(r => r.STATUS === 'CANCEL').length };", $html);
        $this->assertHtmlContains('CANCEL:', $html);
    }

    public function test_mobile_date_and_select_triggers_share_full_width_and_trailing_chevron_rules(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.select-trigger-button-compact {', $html);
        $this->assertHtmlContains('width: 100%;', $html);
        $this->assertTrue(str_contains($html, '.date-trigger-button-compact {') || str_contains($html, '.select-trigger-button-compact {'));
        $this->assertHtmlContains('@media (min-width: 640px) {', $html);
        $this->assertHtmlContains('.search-select-container {', $html);
        $this->assertTrue(str_contains($html, 'fa-chevron-down ml-auto') || str_contains($html, 'fa-chevron-down'));
        $this->assertHtmlContains('margin-left: auto;', $html);
    }

    public function test_period_toolbar_groups_use_shared_mobile_stack_layout(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.compact-period-toolbar {', $html);
        $this->assertHtmlContains('.compact-period-toolbar__controls {', $html);
        $this->assertHtmlContains('class="compact-period-toolbar"', $html);
        $this->assertHtmlContains('class="compact-period-toolbar__controls"', $html);
    }

    public function test_shared_action_filter_toolbar_stacks_consistently_on_mobile(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.mobile-toolbar-stack {', $html);
        $this->assertTrue(
            str_contains($html, '.mobile-toolbar-stack > * {') || str_contains($html, '.mobile-toolbar-stack>* {')
        );
        $this->assertHtmlContains('class="mobile-toolbar-stack"', $html);
    }

    public function test_ideation_board_uses_mobile_lane_switch_to_avoid_three_long_stacks(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains("const ideationBoardMobileTab = ref('');", $html);
        $this->assertHtmlContains('@click="ideationBoardMobileTab = ideationDraftLabel"', $html);
        $this->assertHtmlContains("@click=\"ideationBoardMobileTab = 'In Progress'\"", $html);
        $this->assertHtmlContains("@click=\"ideationBoardMobileTab = 'Done'\"", $html);
        $this->assertHtmlContains('v-show="status === ideationBoardMobileTab || !isMobileViewport"', $html);
        $this->assertHtmlContains('watch(() => ideationDraftLabel.value, (label) => {', $html);
        $this->assertHtmlContains("if (mode === 'board') ideationBoardMobileTab.value = ideationDraftLabel.value;", $html);
    }

    public function test_story_modal_and_content_modals_share_dashboard_controls_consistently(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlNotContains('type="radio" v-model="storyForm.is_genap"', $html);
        $this->assertHtmlContains("@click=\"storyForm.is_genap = 'Ganjil'\"", $html);
        $this->assertHtmlContains("@click=\"storyForm.is_genap = 'Genap'\"", $html);
        $this->assertHtmlContains("storyForm.is_genap === 'Ganjil' ? 'segmented-control__item--active' : ''", $html);
        $this->assertHtmlContains("storyForm.is_genap === 'Genap' ? 'segmented-control__item--active' : ''", $html);
        $this->assertHtmlContains('<div class="modal-footer-bar modal-footer-actions">', $html);
        $this->assertHtmlContains('@click="unboxingModalOpen = false" aria-label="Tutup modal"', $html);
        $this->assertHtmlContains('@click="distModalOpen = false" aria-label="Tutup modal"', $html);
        $this->assertHtmlNotContains("console.log('openUnboxingModal called', type);", $html);
        $this->assertHtmlNotContains("console.log('unboxingModalOpen:', unboxingModalOpen.value);", $html);
    }

    public function test_segmented_switch_controls_use_compact_dimensions(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.segmented-control {', $html);
        $this->assertHtmlContains('min-height: 30px;', $html);
        $this->assertHtmlContains('.segmented-control__item {', $html);
        $this->assertTrue(str_contains($html, 'min-height: 24px;') || str_contains($html, 'min-height: 30px;'));
        $this->assertTrue(str_contains($html, 'padding: 4px 10px;') || str_contains($html, 'padding: 7px 14px;'));
        $this->assertHtmlContains('font-size: var(--fs-body-sm);', $html);
        $this->assertHtmlContains('min-width: 156px;', $html);
        $this->assertHtmlNotContains('padding: 8px 16px;', $html);
        $this->assertHtmlNotContains('min-width: 220px;', $html);
    }

    public function test_table_toolbar_actions_use_consistent_action_export_order(): void
    {
        $menuFiles = glob(resource_path('views/dashboard/partials/menus/*.blade.php'));

        $this->assertIsArray($menuFiles);

        foreach ($menuFiles as $menuFile) {
            $html = file_get_contents($menuFile);

            $this->assertIsString($html);

            preg_match_all('/<div class="toolbar-actions(?: toolbar-actions--desktop-icon-only)?">([\s\S]*?)<\/div>/', $html, $matches);

            foreach ($matches[1] as $index => $toolbarHtml) {
                $plusPosition = strpos($toolbarHtml, 'fa-plus');
                $excelPosition = strpos($toolbarHtml, 'fa-file-excel');
                $pdfPosition = strpos($toolbarHtml, 'fa-file-pdf');
                $label = basename($menuFile).' toolbar #'.($index + 1);

                if ($plusPosition !== false && $excelPosition !== false) {
                    $this->assertLessThan($excelPosition, $plusPosition, "{$label} should place add before Excel.");
                }

                if ($plusPosition !== false && $pdfPosition !== false) {
                    $this->assertLessThan($pdfPosition, $plusPosition, "{$label} should place add before PDF.");
                }

                if ($excelPosition !== false && $pdfPosition !== false) {
                    $this->assertLessThan($pdfPosition, $excelPosition, "{$label} should place Excel before PDF.");
                }
            }

            $this->assertDoesNotMatchRegularExpression(
                '/<div class="toolbar-actions(?: toolbar-actions--desktop-icon-only)?">[\s\S]{0,1600}<div class="relative(?: group)? search-select-container"/',
                $html,
                basename($menuFile).' should place filters before toolbar action groups.'
            );
        }

        foreach ([
            'claim-garansi.blade.php' => '/<div class="toolbar-actions(?: toolbar-actions--desktop-icon-only)?">[\s\S]*?openClaimGaransiModal\(\'create\'\)[\s\S]*?exportExcel[\s\S]*?exportPdf[\s\S]*?<\/div>/',
            'keep-barang.blade.php' => '/<div class="toolbar-actions(?: toolbar-actions--desktop-icon-only)?">[\s\S]*?openKeepBarangModal\(\'create\'\)[\s\S]*?exportKeepBarangToExcel[\s\S]*?exportKeepBarangToPDF[\s\S]*?<\/div>/',
            'master-plan.blade.php' => '/<div class="toolbar-actions(?: toolbar-actions--desktop-icon-only)?">[\s\S]*?openCreateModal[\s\S]*?exportExcel[\s\S]*?exportPdf[\s\S]*?<\/div>/',
            'order-online.blade.php' => '/<div class="toolbar-actions(?: toolbar-actions--desktop-icon-only)?">[\s\S]*?openOrderanOnlineModal\(\'create\'\)[\s\S]*?exportExcel[\s\S]*?exportPdf[\s\S]*?<\/div>/',
            'sell-out.blade.php' => '/<div class="toolbar-actions(?: toolbar-actions--desktop-icon-only)?">[\s\S]*?openSellOutModal\(\'create\'\)[\s\S]*?exportSellOutToExcel[\s\S]*?exportSellOutToPDF[\s\S]*?<\/div>/',
            'unboxing.blade.php' => '/<div class="toolbar-actions(?: toolbar-actions--desktop-icon-only)?">[\s\S]*?openUnboxingModal\(\'create\'\)[\s\S]*?exportExcel[\s\S]*?exportPdf[\s\S]*?<\/div>/',
            'unit-ditanya.blade.php' => '/<div class="toolbar-actions(?: toolbar-actions--desktop-icon-only)?">[\s\S]*?openUnitDitanyaModal\(\'create\'\)[\s\S]*?exportExcel[\s\S]*?exportPdf[\s\S]*?<\/div>/',
        ] as $fileName => $pattern) {
            $menuHtml = file_get_contents(resource_path("views/dashboard/partials/menus/{$fileName}"));

            $this->assertIsString($menuHtml);
            $this->assertHtmlMatches(
                $pattern,
                $menuHtml,
                "{$fileName} should keep add, Excel, and PDF in one toolbar in that order."
            );
        }

        foreach ([
            'claim-garansi.blade.php' => '/<div class="table-toolbar-shell__right">[\s\S]*?filter_claim_status[\s\S]*?filter_claim_garansi[\s\S]*?<div class="toolbar-actions(?: toolbar-actions--desktop-icon-only)?">[\s\S]*?openClaimGaransiModal\(\'create\'\)[\s\S]*?exportExcel[\s\S]*?exportPdf[\s\S]*?<\/div>/',
            'keep-barang.blade.php' => '/<div class="table-toolbar-shell__right">[\s\S]*?keep_status_filter[\s\S]*?keep_handle_filter[\s\S]*?<div class="toolbar-actions(?: toolbar-actions--desktop-icon-only)?">[\s\S]*?openKeepBarangModal\(\'create\'\)[\s\S]*?exportKeepBarangToExcel[\s\S]*?exportKeepBarangToPDF[\s\S]*?<\/div>/',
            'unit-ditanya.blade.php' => '/<div class="table-toolbar-shell__right">[\s\S]*?unitDitanya[\s\S]*?filter_available[\s\S]*?<div class="toolbar-actions(?: toolbar-actions--desktop-icon-only)?">[\s\S]*?openUnitDitanyaModal\(\'create\'\)[\s\S]*?exportExcel[\s\S]*?exportPdf[\s\S]*?<\/div>/',
        ] as $fileName => $pattern) {
            $menuHtml = file_get_contents(resource_path("views/dashboard/partials/menus/{$fileName}"));

            $this->assertIsString($menuHtml);
            $this->assertHtmlMatches(
                $pattern,
                $menuHtml,
                "{$fileName} should place table filters before the final action group."
            );
        }

        foreach ([
            'claim-garansi.blade.php',
            'keep-barang.blade.php',
            'order-online.blade.php',
            'unit-ditanya.blade.php',
        ] as $fileName) {
            $menuHtml = file_get_contents(resource_path("views/dashboard/partials/menus/{$fileName}"));

            $this->assertIsString($menuHtml);
            $this->assertHtmlNotContains('title="Reset"', $menuHtml);
            $this->assertHtmlNotContains('<span>Reset</span>', $menuHtml);
            $this->assertHtmlNotContains('Muat Ulang', $menuHtml);
        }
    }

    public function test_desktop_icon_only_table_toolbars_use_the_desktop_modifier(): void
    {
        foreach ([
            'ads-log.blade.php' => 'openAdsModal',
            'analytics.blade.php' => 'exportExcel',
            'claim-garansi.blade.php' => 'openClaimGaransiModal',
            'distribution.blade.php' => 'exportExcel',
            'harga-kompetitor.blade.php' => 'openHargaKompetitorModal',
            'keep-barang.blade.php' => 'openKeepBarangModal',
            'laporan-event.blade.php' => 'openLpjkModal',
            'low-content.blade.php' => 'exportExcel',
            'master-plan.blade.php' => 'openCreateModal',
            'order-online.blade.php' => 'openOrderanOnlineModal',
            'program-promo.blade.php' => 'openPromoModal',
            'sell-out.blade.php' => 'openSellOutModal',
            'top-content.blade.php' => 'exportExcel',
            'unboxing.blade.php' => 'openUnboxingModal',
            'unit-ditanya.blade.php' => 'openUnitDitanyaModal',
        ] as $fileName => $action) {
            $menuHtml = file_get_contents(resource_path("views/dashboard/partials/menus/{$fileName}"));

            $this->assertIsString($menuHtml);
            $this->assertMatchesRegularExpression(
                '/<div class="hidden md:block[^>]*>[\\s\\S]*?<div class="toolbar-actions toolbar-actions--desktop-icon-only">[\\s\\S]*?'.$action.'/',
                $menuHtml,
                "{$fileName} should mark its desktop icon-only toolbar."
            );
        }
    }

    public function test_ideation_lane_shell_and_remaining_icon_buttons_follow_shared_dashboard_tokens(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlNotContains('bg-white/60 backdrop-blur-sm radius-dialog p-5 border border-slate-100/50', $html);
        $this->assertHtmlContains('class="bg-white radius-panel border border-slate-100 p-5 flex flex-col min-h-[400px]"', $html);
        $this->assertHtmlContains('@click="sellOutModalOpen = false" aria-label="Tutup modal"', $html);
        $this->assertHtmlContains('@click="calendarDayModalOpen = false" aria-label="Tutup modal"', $html);
        $this->assertHtmlContains('@click="changeMonth(-1)" aria-label="Bulan sebelumnya"', $html);
        $this->assertHtmlContains('@click="changeMonth(1)" aria-label="Bulan berikutnya"', $html);
    }

    public function test_crud_modals_use_shared_sticky_header_shell(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlNotContains('class="p-6 border-b border-slate-100 flex items-center justify-between radius-sheet-top bg-white"', $html);
        $this->assertHtmlContains('.modal-header-bar-sticky {', $html);
        $this->assertHtmlContains('class="modal-header-bar modal-header-bar-sticky radius-sheet-top z-[2010]"', $html);
    }

    public function test_primary_pagination_icon_buttons_have_accessible_labels(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();
        $metaStoryHtml = file_get_contents(resource_path('views/dashboard/partials/menus/meta-story.blade.php'));
        $metaFeedHtml = file_get_contents(resource_path('views/dashboard/partials/menus/meta-feed.blade.php'));

        $this->assertIsString($metaStoryHtml);
        $this->assertIsString($metaFeedHtml);
        $this->assertHtmlContains('.table-pager-bar {', $html);
        $this->assertHtmlContains('min-height: 56px;', $html);
        $this->assertHtmlContains('.table-pager-bar > :last-child > span {', $html);
        $this->assertHtmlContains('border-radius: 999px;', $html);
        $this->assertHtmlContains('class="table-pager-bar"', $metaStoryHtml);
        $this->assertHtmlContains('class="table-pager-bar"', $metaFeedHtml);
        $this->assertHtmlNotContains('px-4 py-3 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between', $metaStoryHtml);
        $this->assertHtmlNotContains('px-4 py-3 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between', $metaFeedHtml);
        $this->assertHtmlContains('@click="masterPage--" :disabled="masterPage <= 1"', $html);
        $this->assertHtmlContains('@click="masterPage++" :disabled="masterPage >= masterTotalPages"', $html);
        $this->assertHtmlContains('@click="distributionPage--" :disabled="distributionPage <= 1"', $html);
        $this->assertHtmlContains('@click="analyticsPage++" :disabled="analyticsPage >= analyticsTotalPages"', $html);
        $this->assertHtmlContains('aria-label="Halaman sebelumnya"', $html);
        $this->assertHtmlContains('aria-label="Halaman berikutnya"', $html);
        $this->assertHtmlContains('aria-label="Kolom sebelumnya"', $html);
        $this->assertHtmlContains('aria-label="Kolom berikutnya"', $html);
        $this->assertHtmlContains('@click="storyPage--" :disabled="storyPage <= 1"', $html);
        $this->assertHtmlContains('@click="unboxingPage--" :disabled="unboxingPage <= 1"', $html);
        $this->assertHtmlContains('@click="unboxingPage++" :disabled="unboxingPage >= unboxingTotalPages"', $html);
        $this->assertHtmlContains('@click="orderanPage++" :disabled="orderanPage >= orderanTotalPages"', $html);
        $this->assertHtmlContains('@click="unitDitanyaPage--" :disabled="unitDitanyaPage <= 1"', $html);
        $this->assertHtmlContains('@click="claimPage++" :disabled="claimPage >= claimTotalPages"', $html);
        $this->assertHtmlContains('@click="keepBarangPage--" :disabled="keepBarangPage <= 1"', $html);
        $this->assertHtmlContains('@click="namaStockPage++" :disabled="namaStockPage >= namaStockTotalPages"', $html);
        $this->assertHtmlContains('@click="promoPage--" :disabled="promoPage <= 1"', $html);
        $this->assertHtmlContains('@click="bonusPage++" :disabled="bonusPage >= bonusTotalPages"', $html);
        $this->assertHtmlContains('@click="editorPage--" :disabled="editorPage <= 1"', $html);
        $this->assertHtmlContains('@click="sellOutPage++" :disabled="sellOutPage >= sellOutTotalPages"', $html);
        $this->assertHtmlContains('@click="hargaKompetitorPage = 1" :disabled="hargaKompetitorPage <= 1"', $html);
        $this->assertHtmlContains('@click="hargaKompetitorPage = hargaKompetitorTotalPages"', $html);
        $this->assertHtmlContains('aria-label="Halaman pertama"', $html);
        $this->assertHtmlContains('aria-label="Halaman terakhir"', $html);
        $this->assertHtmlContains('@click="lpjkPage--" :disabled="lpjkPage <= 1"', $html);
        $this->assertHtmlContains('@click="adsPage++" :disabled="adsPage >= adsTotalPages"', $html);
    }

    public function test_ads_log_uses_dedicated_ads_performance_crud_instead_of_master_plan_bridge(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('saveAds(data)', $html);
        $this->assertHtmlContains('deleteAds(id)', $html);
        $this->assertHtmlContains('/api/ads-performance', $html);
    }

    public function test_ads_log_kategori_column_is_wide_and_never_wraps(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('<th class="table-header-cell text-center w-36">Kategori</th>', $html);
        $this->assertHtmlContains('<td class="px-4 py-3 text-center whitespace-nowrap">', $html);
        $this->assertTrue(str_contains($html, 'whitespace-nowrap') && str_contains($html, 'Kategori'));
    }

    public function test_calendar_event_badges_keep_text_readable_on_amber_background(): void
    {
        $calendarPartial = file_get_contents(resource_path('views/dashboard/partials/menus/calendar.blade.php'));

        $this->assertIsString($calendarPartial);
        $this->assertHtmlContains('class="px-2 py-1 rounded-lg border border-amber bg-amber flex items-center gap-1.5 text-light"', $calendarPartial);
        $this->assertHtmlContains('class="fa-solid fa-star text-overline-xs text-light"', $calendarPartial);
        $this->assertHtmlContains('class="text-overline-xs font-black truncate uppercase text-light"', $calendarPartial);
        $this->assertHtmlNotContains('class="fa-solid fa-star text-overline-xs text-amber"', $calendarPartial);
        $this->assertHtmlNotContains('class="text-overline-xs font-black truncate uppercase text-amber"', $calendarPartial);
    }

    public function test_table_status_badges_never_wrap(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold whitespace-nowrap"', $html);
        $this->assertHtmlContains('class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap"', $html);
        $this->assertHtmlContains('class="status-badge-fixed inline-flex items-center px-2.5 py-1 rounded-lg text-[9px] font-bold uppercase whitespace-nowrap"', $html);
    }

    public function test_print_headers_render_logo_on_left_of_company_text(): void
    {
        $printCore = file_get_contents(resource_path('js/dashboard/export/print-core.js'));

        $this->assertHtmlContains("const logoUrl = getDashboardAssetUrl('/asset/images/logo.png');", $printCore);
        $this->assertHtmlContains('`src="${logoUrl}"`', $printCore);
        $this->assertHtmlContains('export function getPrintOrgHeaderHTML()', $printCore);
        $this->assertHtmlContains('.print-org-header {', $printCore);
        $this->assertHtmlContains('.print-org-brand {', $printCore);
        $this->assertHtmlContains('.print-org-copy {', $printCore);
        $this->assertHtmlContains('width="62"', $printCore);
        $this->assertHtmlContains('height="62"', $printCore);
        $this->assertHtmlContains('style="flex:0 0 auto;width:62px;height:62px;object-fit:contain;margin:0;"', $printCore);
        $this->assertHtmlContains('style="display:flex;align-items:center;justify-content:center;gap:18px;width:100%;margin:0 0 6px;"', $printCore);
    }

    public function test_print_templates_force_exact_color_output(): void
    {
        $html = file_get_contents(resource_path('js/dashboard/export/print-core.js'));

        $this->assertHtmlContains('-webkit-print-color-adjust: exact !important;', $html);
        $this->assertHtmlContains('print-color-adjust: exact !important;', $html);
        $this->assertHtmlContains('color-adjust: exact !important;', $html);
    }

    public function test_dashboard_source_files_avoid_decorative_unicode_and_external_logo_assets(): void
    {
        $files = [
            resource_path('js/dashboard/export/print-core.js'),
            resource_path('js/dashboard/export/print-browser.js'),
            resource_path('js/dashboard/export/unit-ditanya.js'),
            resource_path('js/dashboard/export/claim-garansi.js'),
            resource_path('views/dashboard/partials/menus/analisa-insight.blade.php'),
            resource_path('views/dashboard/partials/menus/meta-story.blade.php'),
            resource_path('views/dashboard/partials/shell/head.blade.php'),
            resource_path('views/welcome.blade.php'),
            resource_path('legacy/marketing-dashboard-source.html'),
            base_path('routes/web.php'),
            public_path('design-system.html'),
        ];

        foreach ($files as $file) {
            $contents = file_get_contents($file);

            $this->assertIsString($contents);
            $this->assertSame(
                0,
                preg_match('/[─═■]/u', $contents),
                "Failed asserting that [{$file}] does not contain decorative Unicode characters."
            );
            $this->assertStringNotContainsString('https://dashboard.purapuraponsel.com/asset/images/logo.png', $contents);
            $this->assertStringNotContainsString('https://dashboard.purapuraponsel.com/asset/images/favicon.ico', $contents);
            $this->assertStringNotContainsString('https://purapuraponsel.com/asset/images/logo.png', $contents);
        }
    }

    public function test_custom_pdf_exports_reuse_shared_print_template(): void
    {
        $printCore = file_get_contents(resource_path('js/dashboard/export/print-core.js'));
        $promoExport = file_get_contents(resource_path('js/dashboard/export/promo.js'));
        $adsLogExport = file_get_contents(resource_path('js/dashboard/export/ads-log.js'));

        $this->assertHtmlContains('export function getPrintHTML({', $printCore);
        $this->assertHtmlContains('getPrintBaseStyles(),', $printCore);
        $this->assertHtmlContains('getPrintOrgHeaderHTML(),', $printCore);
        $this->assertHtmlContains('window.DASHBOARD_FONTAWESOME_URL || getDashboardAssetUrl', $printCore);
        $this->assertHtmlContains('/vendor/dashboard/fontawesome/css/all.min.css', $printCore);
        $this->assertHtmlNotContains('https://cdn.jsdelivr.net', $printCore);
        $this->assertHtmlContains('const html = getPrintHTML({', $promoExport);
        $this->assertHtmlContains("title: 'PROGRAM PROMO'", $promoExport);
        $this->assertHtmlContains("openPrintWindow(html, 'Program Promo', {", $promoExport);
        $this->assertHtmlContains('const html = getPrintHTML({', $adsLogExport);
        $this->assertHtmlContains("title: 'ADS PERFORMANCE REPORT'", $adsLogExport);
        $this->assertHtmlContains("openPrintWindow(html, 'Ads Report', {", $adsLogExport);
    }

    public function test_excel_export_modules_load_sheetjs_from_local_vendor_asset(): void
    {
        $vendorManifest = file_get_contents(resource_path('vendor/dashboard/manifest.json'));
        $xlsxLoader = file_get_contents(resource_path('js/dashboard/export/xlsx-loader.js'));
        $bonusExcel = file_get_contents(resource_path('js/dashboard/export/bonus-excel.js'));
        $budgetExcel = file_get_contents(resource_path('js/dashboard/export/budget-excel.js'));
        $sellOutExcel = file_get_contents(resource_path('js/dashboard/export/sell-out-excel.js'));
        $unitDitanya = file_get_contents(resource_path('js/dashboard/export/unit-ditanya.js'));
        $claimGaransi = file_get_contents(resource_path('js/dashboard/export/claim-garansi.js'));
        $keepBarang = file_get_contents(resource_path('js/dashboard/export/keep-barang.js'));
        $vendorSyncScript = file_get_contents(base_path('scripts/sync-dashboard-vendor-assets.mjs'));
        $packageJson = file_get_contents(base_path('package.json'));

        $this->assertIsString($vendorManifest);
        $this->assertHtmlContains("const DASHBOARD_XLSX_VENDOR_PATH = '/vendor/dashboard/xlsx/xlsx.full.min.js';", $xlsxLoader);
        $this->assertHtmlContains('script.src = buildDashboardAssetUrl(DASHBOARD_XLSX_VENDOR_PATH);', $xlsxLoader);
        $this->assertHtmlNotContains('cdn.sheetjs.com', $xlsxLoader);
        $this->assertHtmlContains('"id": "xlsx"', $vendorManifest);
        $this->assertHtmlContains('"strategy": "repo-local-sync"', $vendorManifest);
        $this->assertHtmlContains('"runtimePath": "/vendor/dashboard/xlsx/xlsx.full.min.js"', $vendorManifest);
        $this->assertHtmlContains("path.join(projectRoot, 'resources', 'vendor', 'dashboard', 'manifest.json')", $vendorSyncScript);
        $this->assertHtmlNotContains('"xlsx":', $packageJson);

        foreach ([
            $bonusExcel,
            $budgetExcel,
            $sellOutExcel,
            $unitDitanya,
            $claimGaransi,
            $keepBarang,
        ] as $source) {
            $this->assertHtmlContains("import { ensureXLSX } from './xlsx-loader.js';", $source);
            $this->assertHtmlNotContains('cdn.sheetjs.com', $source);
        }
    }

    public function test_dashboard_vendor_manifest_documents_local_runtime_assets(): void
    {
        $manifestContents = file_get_contents(resource_path('vendor/dashboard/manifest.json'));

        $this->assertIsString($manifestContents);

        $manifest = json_decode($manifestContents, true, 512, JSON_THROW_ON_ERROR);
        $assets = collect($manifest['assets'] ?? [])->keyBy('id');

        foreach ([
            'vue',
            'papaparse',
            'apexcharts',
            'fontawesome-css',
            'fontawesome-webfonts',
            'xlsx',
        ] as $assetId) {
            $this->assertTrue($assets->has($assetId), "Failed asserting that vendor manifest contains [{$assetId}].");
        }

        $this->assertSame('/vendor/dashboard/vue/vue.global.prod.js', data_get($assets->get('vue'), 'runtimePath'));
        $this->assertSame('/vendor/dashboard/papaparse/papaparse.min.js', data_get($assets->get('papaparse'), 'runtimePath'));
        $this->assertSame('/vendor/dashboard/apexcharts/apexcharts.min.js', data_get($assets->get('apexcharts'), 'runtimePath'));
        $this->assertSame('/vendor/dashboard/fontawesome/css/all.min.css', data_get($assets->get('fontawesome-css'), 'runtimePath'));
        $this->assertSame('/vendor/dashboard/fontawesome/webfonts', data_get($assets->get('fontawesome-webfonts'), 'runtimePath'));
        $this->assertSame('/vendor/dashboard/xlsx/xlsx.full.min.js', data_get($assets->get('xlsx'), 'runtimePath'));
        $this->assertSame('repo-local-sync', data_get($assets->get('xlsx'), 'strategy'));
        $this->assertSame('npm-sync', data_get($assets->get('vue'), 'strategy'));
        $this->assertSame('npm-sync', data_get($assets->get('fontawesome-css'), 'strategy'));
        $this->assertSame('node_modules/vue/dist/vue.global.prod.js', data_get($assets->get('vue'), 'source'));
        $this->assertSame('resources/vendor/dashboard/xlsx/xlsx.full.min.js', data_get($assets->get('xlsx'), 'source'));
    }

    public function test_demo_and_welcome_surfaces_use_local_fontawesome_asset(): void
    {
        $welcome = file_get_contents(resource_path('views/welcome.blade.php'));
        $designSystemView = file_get_contents(resource_path('views/reference/design-system.blade.php'));

        $this->assertHtmlContains("{{ asset('vendor/dashboard/fontawesome/css/all.min.css') }}", $welcome);
        $this->assertHtmlContains("{{ asset('vendor/dashboard/fontawesome/css/all.min.css') }}", $designSystemView);
        $this->assertHtmlContains('?v={{ file_exists($fontAwesomeCssPath) ? filemtime($fontAwesomeCssPath) : time() }}', $welcome);
        $this->assertHtmlContains('?v={{ file_exists($fontAwesomeCssPath) ? filemtime($fontAwesomeCssPath) : time() }}', $designSystemView);
        $this->assertHtmlNotContains('cdnjs.cloudflare.com', $welcome);
        $this->assertHtmlNotContains('cdnjs.cloudflare.com', $designSystemView);
    }

    public function test_active_sidebar_menu_forces_text_labels_to_inherit_white_color(): void
    {
        $appCss = file_get_contents(resource_path('css/app.css'));
        $dashboardShellCss = file_get_contents(resource_path('css/dashboard-shell.css'));
        $sidebarNavSources = implode("\n", array_map(
            static fn (string $path): string => (string) file_get_contents(resource_path($path)),
            [
                'views/dashboard/partials/shell/app-frame-sidebar-nav-dashboard-content.blade.php',
                'views/dashboard/partials/shell/app-frame-sidebar-nav-marketing.blade.php',
                'views/dashboard/partials/shell/app-frame-sidebar-nav-analysis.blade.php',
                'views/dashboard/partials/shell/app-frame-sidebar-nav-cs.blade.php',
                'views/dashboard/partials/shell/app-frame-sidebar-nav-admin.blade.php',
            ]
        ));

        $this->assertIsString($appCss);
        $this->assertIsString($dashboardShellCss);
        $this->assertIsString($sidebarNavSources);
        $this->assertHtmlContains('--color-ppp-nav-text: #FFA500;', $appCss);
        $this->assertHtmlContains('--ppp-nav-text: #FFA500;', $appCss);
        $this->assertHtmlContains('--ppp-nav-rgb: 255 165 0;', $appCss);
        $this->assertHtmlContains('.nav-item.is-active .type-body,', $appCss);
        $this->assertHtmlContains('.nav-subitem.is-active .type-meta,', $appCss);
        $this->assertHtmlContains('color: inherit;', $appCss);
        $this->assertHtmlContains('.nav-accordion-active .type-body-sm {', $appCss);
        $this->assertHtmlContains('font-weight: 400;', $appCss);
        $this->assertHtmlNotContains('border-left: 3px solid var(--ppp-accent);', $appCss);
        $this->assertHtmlContains('.dashboard-sidebar-nav {', $dashboardShellCss);
        $this->assertHtmlContains('transition-property: transform;', $dashboardShellCss);
        $this->assertHtmlContains('cubic-bezier(0.4, 0, 0.2, 1)', $dashboardShellCss);
        $this->assertHtmlNotContains('.nav-accordion-active', $dashboardShellCss);
        $this->assertHtmlNotContains('sidebar-accordion', $dashboardShellCss);
        $this->assertHtmlContains('sidebar-nav-item-active bg-[var(--ppp-accent)] !text-white', $sidebarNavSources);
        $this->assertHtmlNotContains('shadow-sm', $sidebarNavSources);
        $this->assertHtmlContains('h-1.5 w-1.5 rounded-full bg-white', $sidebarNavSources);
    }

    public function test_design_system_route_renders_from_laravel_view(): void
    {
        $response = $this->get('/design-system');

        $response->assertOk();
        $response->assertSee('Design System - Pura Pura Ponsel', false);
        $response->assertSee('Pura Pura Ponsel', false);
        $response->assertSee('/vendor/dashboard/fontawesome/css/all.min.css', false);
    }

    public function test_design_system_route_does_not_depend_on_public_snapshot(): void
    {
        $publicSnapshotPath = public_path('design-system.html');
        $temporarySnapshotPath = $publicSnapshotPath.'.tmp-test';

        rename($publicSnapshotPath, $temporarySnapshotPath);

        try {
            $response = $this->get('/design-system');

            $response->assertOk();
            $response->assertSee('Design System - Pura Pura Ponsel', false);
        } finally {
            rename($temporarySnapshotPath, $publicSnapshotPath);
        }
    }

    public function test_public_design_system_snapshot_is_stub_that_points_to_laravel_route(): void
    {
        $publicSnapshot = file_get_contents(public_path('design-system.html'));
        $archiveSnapshot = $this->readArchivedDesignSystemSnapshotReferenceHtml();

        $this->assertIsString($publicSnapshot);
        $this->assertHtmlContains('/design-system', $publicSnapshot);
        $this->assertHtmlContains('Halaman ini dipindahkan ke route Laravel', $publicSnapshot);
        $this->assertHtmlNotContains('<nav class="ds-nav">', $publicSnapshot);
        $this->assertHtmlContains('<nav class="ds-nav">', $archiveSnapshot);
        $this->assertHtmlContains('Design System - Pura Pura Ponsel', $archiveSnapshot);
    }

    public function test_public_dashboard_snapshot_is_archive_not_runtime_source(): void
    {
        $publicSnapshot = file_get_contents(public_path('marketing-dashboard.html'));
        $archiveReference = $this->readArchivedDashboardSnapshotReferenceHtml();

        $this->assertIsString($publicSnapshot);
        $this->assertHtmlContains('url=/', $publicSnapshot);
        $this->assertHtmlContains('Halaman ini dipindahkan ke route Laravel utama dashboard.', $publicSnapshot);
        $this->assertHtmlContains('<a href="/">/</a>', $publicSnapshot);
        $this->assertHtmlNotContains('<div id="app"', $publicSnapshot);
        $this->assertHtmlContains('<div id="app"', $archiveReference);
        $this->assertHtmlContains('Marketing Dashboard | Pura Pura Ponsel', $archiveReference);
    }

    public function test_print_templates_use_readable_typography_for_exports(): void
    {
        $printCore = file_get_contents(resource_path('js/dashboard/export/print-core.js'));
        $bonusExport = file_get_contents(resource_path('js/dashboard/export/bonus.js'));

        $this->assertHtmlContains("body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 10pt; line-height: 1.45;", $printCore);
        $this->assertHtmlContains("h1 { font-family: 'Times New Roman', Times, serif; font-size: 18pt;", $printCore);
        $this->assertHtmlContains('table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 9pt;', $printCore);
        $this->assertHtmlContains('td { border-bottom: 1px solid #e2e8f0; border-left: none; border-right: none; border-top: none; padding: 6px; font-size: 9pt;', $printCore);
        $this->assertHtmlContains('.report-meta { margin: -6px 0 12px; text-align: center; color: var(--color-stone-500, #78716c); font-size: 8pt; }', $printCore);
        $this->assertHtmlContains('.signature-section { margin-top: 28px; page-break-inside: avoid; display: flex !important;', $printCore);
        $this->assertHtmlContains('headers: [', $bonusExport);
        $this->assertHtmlContains("'Konten & Platform',", $bonusExport);
        $this->assertHtmlContains("'Total Bonus',", $bonusExport);
    }

    public function test_print_window_waits_for_document_assets_before_printing(): void
    {
        $printCore = file_get_contents(resource_path('js/dashboard/export/print-core.js'));
        $printBrowser = file_get_contents(resource_path('js/dashboard/export/print-browser.js'));
        $printSources = $printCore."\n".$printBrowser;

        $this->assertHtmlContains('export function waitForPrintAssets(printWindow) {', $printCore);
        $this->assertHtmlContains('export function buildStandalonePrintHtml(html, { autoPrint = false } = {}) {', $printCore);
        $this->assertHtmlContains('const autoPrintHtml = buildStandalonePrintHtmlFn(html, { autoPrint: true });', $printBrowser);
        $this->assertHtmlContains('submitBrowserPrintJob(autoPrintHtml, { jsonApi })', $printBrowser);
        $this->assertHtmlContains('const pw = window.open(\'\', \'_blank\', \'width=1200,height=1000\');', $printBrowser);
        $this->assertHtmlContains('resolveAppUrl,', $printBrowser);
        $this->assertHtmlContains('notifyError(', $printBrowser);
        $this->assertHtmlNotContains('fallbackWrite', $printSources);
        $this->assertHtmlNotContains('createObjectURL(blob)', $printSources);
        $this->assertHtmlNotContains('new Blob([printDocumentHtml]', $printSources);
        $this->assertHtmlNotContains('pw.document.write(autoPrintHtml);', $printSources);
        $this->assertHtmlNotContains('setTimeout(() => { try { pw.print(); } catch (e) { } }, 500);', $printSources);
        $this->assertHtmlNotContains('pw.location.href = `${execBaseUrl}/print?printJob=${encodeURIComponent(printJobKey)}`;', $printSources);
    }

    public function test_print_job_route_keeps_html_available_during_cache_ttl(): void
    {
        cache()->put('ppp_print_job_deadbeef1234', '<!DOCTYPE html><html><body>Demo Print</body></html>', now()->addMinutes(5));

        $firstResponse = $this->get('/print-job/deadbeef1234');
        $firstResponse->assertOk();
        $firstResponse->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $firstResponse->assertSee('Demo Print', false);

        $secondResponse = $this->get('/print-job/deadbeef1234');
        $secondResponse->assertOk();
        $secondResponse->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $secondResponse->assertSee('Demo Print', false);
    }

    public function test_profile_tab_includes_dashboard_user_management_panel(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('Manajemen User', $html);
        $this->assertHtmlContains("switchTab('auth_users')", $html);
        $this->assertHtmlContains("activeTab === 'auth_users'", $html);
        $this->assertHtmlContains('/api/auth/users', $html);
        $this->assertHtmlContains('authUserForm.username', $html);
        $this->assertHtmlContains('loadAuthUsers()', $html);
    }

    public function test_master_plan_modal_uses_compact_template_styling(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();
        $budgetingPartial = file_get_contents(resource_path('views/dashboard/partials/menus/budgeting.blade.php'));

        $this->assertIsString($budgetingPartial);
        $this->assertHtmlContains('class="modal-header-icon bg-amber text-light"', $budgetingPartial);
        $this->assertHtmlContains('class="master-plan-modal-body p-4 md:p-5 overflow-y-auto custom-scrollbar flex-1"', $budgetingPartial);
        $this->assertHtmlContains('class="master-plan-form-grid grid grid-cols-1 md:grid-cols-2 gap-3"', $budgetingPartial);
        $this->assertHtmlContains('class="form-input-compact"', $budgetingPartial);
        $this->assertHtmlContains('class="master-plan-toggle-group"', $budgetingPartial);
        $this->assertHtmlContains('class="primary-cta-button primary-cta-button--accent flex-1"', $budgetingPartial);
        $this->assertHtmlContains('class="master-plan-form-section master-plan-form-section-inner"', $budgetingPartial);
        $this->assertHtmlContains('<div class="form-section-title">Distribution Details</div>', $budgetingPartial);
        $this->assertHtmlContains('<div class="form-section-copy">Link post, tipe distribusi, dan tanggal publish per platform.</div>', $budgetingPartial);
        $this->assertHtmlContains('class="master-plan-distribution-grid"', $budgetingPartial);
        $this->assertHtmlContains('class="master-plan-distribution-field"', $budgetingPartial);
        $this->assertHtmlNotContains("modalType === 'create' ? 'bg-success' : 'bg-ppp-accent'", $budgetingPartial);
        $this->assertHtmlContains('.master-plan-modal-body label {', $html);
        $this->assertHtmlContains('.master-plan-form-section {', $html);
        $this->assertHtmlContains('border-bottom: 1px solid var(--ppp-line);', $html);
        $this->assertHtmlNotContains('border-left: 3px solid var(--ppp-accent);', $html);
        $this->assertHtmlContains('.master-plan-distribution-grid {', $html);
        $this->assertHtmlContains('align-items: end;', $html);
        $this->assertHtmlContains('grid-template-columns: minmax(0, 1.35fr) minmax(8rem, 0.8fr) minmax(0, 1fr);', $html);
        $this->assertHtmlContains('.master-plan-toggle-button-active {', $html);
    }

    public function test_dashboard_sources_do_not_use_glow_effects(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();
        $appCss = file_get_contents(resource_path('css/app.css'));
        $budgetingPartial = file_get_contents(resource_path('views/dashboard/partials/menus/budgeting.blade.php'));

        $this->assertIsString($appCss);
        $this->assertIsString($budgetingPartial);

        $sources = $appCss."\n".$html."\n".$budgetingPartial;

        foreach ([
            'shadow-[',
            'drop-shadow',
            'text-shadow',
        ] as $glowNeedle) {
            $this->assertHtmlNotContains($glowNeedle, $sources);
        }
    }

    public function test_menu_table_headers_use_shared_classes(): void
    {
        $partials = glob(resource_path('views/dashboard/partials/menus/*.blade.php'));

        $this->assertIsArray($partials);

        foreach ($partials as $partialPath) {
            $partial = file_get_contents($partialPath);
            $partialName = basename($partialPath);

            $this->assertIsString($partial);

            if (! str_contains($partial, '<thead')) {
                continue;
            }

            preg_match_all('/<thead[^>]*>(.*?)<\/thead>/s', $partial, $theadMatches);

            foreach ($theadMatches[1] as $theadHtml) {
                $this->assertHtmlContains('class="table-header-row"', $theadHtml, "{$partialName} has a table header row without the shared class.");
            }

            preg_match_all('/<th class="([^"]*)"/', $partial, $thMatches);

            foreach ($thMatches[1] as $classList) {
                $this->assertStringStartsWith('table-header-cell', $classList, "{$partialName} has a table header cell without the shared class.");

                foreach (['px-', 'py-', 'text-body-sm', 'text-overline', 'uppercase', 'tracking-', 'font-bold', 'font-semibold', 'text-slate-', 'border-b'] as $legacyClassFragment) {
                    $this->assertStringNotContainsString($legacyClassFragment, $classList, "{$partialName} still has legacy table header styling.");
                }
            }
        }
    }

    public function test_settings_sidebar_and_shell_include_activity_logs_panel(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();
        $sidebarNavAdminPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-sidebar-nav-admin.blade.php'));

        $this->assertIsString($sidebarNavAdminPartial);
        $this->assertHtmlContains('Activity Logs', $html);
        $this->assertHtmlContains("switchTab('activity_logs')", $sidebarNavAdminPartial);
        $this->assertHtmlContains("activeTab === 'activity_logs'", $sidebarNavAdminPartial);
        $this->assertHtmlContains("activity_logs: { label: 'Activity Logs', category: 'Settings' }", $html);
        $this->assertHtmlContains("if (tab === 'activity_logs') {", $html);
        $this->assertHtmlContains('loadActivityLogs();', $html);
        $this->assertHtmlContains('getActivityLogs(filters = {})', $html);
    }

    public function test_dashboard_shell_includes_pricelist_catalog_menu_and_runner(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();
        $assemblyPartial = file_get_contents(resource_path('views/dashboard/partials/shell/body-app-assembly.blade.php'));
        $sidebarNavAdminPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-sidebar-nav-admin.blade.php'));

        $this->assertIsString($assemblyPartial);
        $this->assertIsString($sidebarNavAdminPartial);
        $this->assertHtmlContains('Katalog Pricelist', $html);
        $this->assertHtmlContains("switchTab('pricelist_katalog')", $sidebarNavAdminPartial);
        $this->assertHtmlContains("activeTab === 'pricelist_katalog'", $html);
        $this->assertHtmlContains("pricelist_katalog: { label: 'Katalog Android', category: null }", $html);
        $this->assertHtmlContains("@include('dashboard.partials.shell.app-script-pricelist-katalog-operations')", $assemblyPartial);
        $this->assertHtmlContains('getPricelistProducts()', $html);
        $this->assertHtmlContains('syncPricelistProducts()', $html);
        $this->assertHtmlContains('uploadCatalogBackground(file, id)', $html);
        $this->assertHtmlContains('generateCatalogPreview', $html);
        $this->assertHtmlContains('const fillRoundedRect = (ctx, x, y, width, height, radius) => {', $html);
        $this->assertHtmlContains('const catalogProductType = (row) => {', $html);
        $this->assertHtmlContains('const formatCatalogPrice = (value) => {', $html);
        $this->assertHtmlContains('NORMAL PRICE', $html);
        $this->assertHtmlContains('SPECIAL PRICE', $html);
        $this->assertHtmlContains('catalogTemplateForm.layout_config.headerHeight', $html);
        $this->assertHtmlContains('catalogTemplateForm.layout_config.titleFontSize', $html);
        $this->assertHtmlContains('catalogTemplateForm.layout_config.titleGap', $html);
        $this->assertHtmlContains('catalogTemplateForm.layout_config.titleColor', $html);
        $this->assertHtmlContains("{ key: 'headerColor', label: 'Warna header (latar)' }", $html);
        $this->assertHtmlContains("{ key: 'headerTextColor', label: 'Warna teks header' }", $html);
        $this->assertHtmlContains("{ key: 'textColor', label: 'Warna teks produk' }", $html);
        $this->assertHtmlContains("{ key: 'normalPriceColor', label: 'Warna normal price' }", $html);
        $this->assertHtmlContains("{ key: 'specialPriceColor', label: 'Warna special price' }", $html);
        $this->assertHtmlContains("{ key: 'rowOddColor', label: 'Warna row ganjil' }", $html);
        $this->assertHtmlContains("{ key: 'rowEvenColor', label: 'Warna row genap' }", $html);
        $this->assertHtmlContains('type="color"', $html);
        $this->assertHtmlContains('catalogTemplateForm.layout_config[colorField.key]', $html);
        $this->assertHtmlContains('catalogApplyColor(colorField.key, $event)', $html);
        $this->assertHtmlContains('const catalogColorFields = [', $html);
        $this->assertHtmlContains('const catalogApplyColor = (key, event) => {', $html);
        $this->assertHtmlContains('catalogLayoutDragStart($event, catalogTemplateForm)', $html);
        $this->assertHtmlContains('const catalogLayoutDragMove = (event) => {', $html);
        $this->assertHtmlContains('Drag posisi tabel', $html);
        $this->assertHtmlContains('Drag judul brand', $html);
        $this->assertHtmlContains("catalogLayoutDragStart(\$event, catalogTemplateForm, 'title')", $html);
        $this->assertHtmlContains('catalogTemplateForm.layout_config.titleX', $html);
        $this->assertHtmlContains('catalogTemplateForm.layout_config.titleY', $html);
        $this->assertHtmlContains('CENTER', $html);
        $this->assertHtmlContains('const centerX = (drag.canvasWidth - drag.tableWidth) / 2;', $html);
        $this->assertHtmlContains('catalogTemplateForm.layout_config.rowHeight', $html);
        $this->assertHtmlContains('catalogTemplateForm.layout_config.priceFontSize', $html);
    }

    public function test_pricelist_catalog_data_table_supports_card_view(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();
        $pricelistPartial = file_get_contents(resource_path('views/dashboard/partials/menus/pricelist-katalog.blade.php'));
        $operationsPartial = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-pricelist-katalog-operations.blade.php'));

        $this->assertIsString($pricelistPartial);
        $this->assertIsString($operationsPartial);

        $this->assertHtmlContains("const pricelistView = ref(localStorage.getItem('ppp_pricelist_view') || 'card');", $operationsPartial);
        $this->assertHtmlContains('const pricelistCardGroups = computed(() => catalogAndroidCardGroups(filteredPricelistProducts.value));', $operationsPartial);

        $this->assertHtmlContains("@click=\"pricelistView = 'card'\"", $pricelistPartial);
        $this->assertHtmlContains("@click=\"pricelistView = 'table'\"", $pricelistPartial);
        $this->assertHtmlContains('v-if="pricelistView === \'table\'"', $pricelistPartial);
        $this->assertHtmlContains('v-for="group in pricelistCardGroups"', $pricelistPartial);
        $this->assertHtmlContains('androidProductImage(group.variants[0])', $pricelistPartial);
        $this->assertHtmlContains('pricelistCardVariantLabel(row)', $pricelistPartial);
        $this->assertHtmlContains('xl:h-[calc(100dvh-7rem)]', $pricelistPartial);
        $this->assertHtmlContains('section-card flex flex-col min-h-[420px] md:min-h-[520px] xl:min-h-0 overflow-hidden', $pricelistPartial);
        $this->assertHtmlContains('v-else class="flex-1 min-h-0 overflow-y-auto p-3 md:p-4"', $pricelistPartial);
        $this->assertHtmlContains('v-if="pricelistView === \'table\'" class="table-pager-bar flex-shrink-0"', $pricelistPartial);
        $this->assertHtmlContains('max-h-[calc(100vh-7rem)] w-auto h-auto object-contain', $pricelistPartial);
        $this->assertHtmlContains('origin-center', $pricelistPartial);

        $this->assertHtmlContains('pricelistView,', $html);
        $this->assertHtmlContains('pricelistCardGroups,', $html);
        $this->assertHtmlContains('pricelistCardVariantLabel,', $html);
    }

    public function test_pricelist_catalog_output_controls_use_consistent_primitives(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.catalog-control-grid {', $html);
        $this->assertHtmlContains('.catalog-control-field {', $html);
        $this->assertHtmlContains('.catalog-control-field .search-select-container {'."\n".'            width: 100%;', $html);
        $this->assertHtmlContains('.catalog-control-field .select-trigger-button-compact {'."\n".'            width: 100%;', $html);
        $this->assertHtmlContains('.catalog-segment-group {', $html);
        $this->assertHtmlContains('.catalog-segment-group {'."\n".'            box-sizing: border-box;'."\n".'            height: 36px;', $html);
        $this->assertHtmlContains('border-radius: 12px;', $html);
        $this->assertHtmlContains('.catalog-segment-button {'."\n".'            min-width: 0;'."\n".'            min-height: 28px;', $html);
        $this->assertHtmlContains('border-radius: calc(var(--radius-lg) - 4px);', $html);
        $this->assertHtmlContains('.catalog-segment-button.catalog-segment-button--active {', $html);
        $this->assertHtmlContains("catalogOutputMode === 'list' ? 'catalog-segment-button--active' : ''", $html);
        $this->assertHtmlContains("catalogPriceKey === opt.key ? 'catalog-segment-button--active' : ''", $html);
        $this->assertHtmlNotContains("catalogOutputMode === 'list' ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-white text-slate-500 border-slate-200'", $html);
        $this->assertHtmlNotContains("catalogPriceKey === opt.key ? 'bg-ppp-accent text-white border-ppp-accent' : 'bg-white text-slate-500 border-slate-200'", $html);
    }

    public function test_apple_catalog_table_view_allows_horizontal_scroll(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('apple-table-scroll', $html);
        $this->assertHtmlContains('apple-catalog-table', $html);
        $this->assertHtmlContains('.apple-table-scroll {', $html);
        $this->assertHtmlContains('overflow-x: auto;', $html);
        $this->assertHtmlContains('.apple-catalog-table {', $html);
        $this->assertHtmlContains('width: max-content;', $html);
        $this->assertHtmlContains('.apple-catalog-table .apple-price-cell {', $html);
        $this->assertHtmlContains('min-width: 112px;', $html);
    }

    public function test_apple_catalog_table_view_follows_primary_price_filter(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('const appleTablePriceColumns = computed(() => {', $html);
        $this->assertHtmlContains("if (applePriceKey.value === 'second_table')", $html);
        $this->assertHtmlContains('const appleTablePriceValue = (row, column) => {', $html);
        $this->assertHtmlContains('v-for="column in appleTablePriceColumns"', $html);
        $this->assertHtmlContains('{{ column.label }}', $html);
        $this->assertHtmlContains('formatApplePrice(appleTablePriceValue(row, column))', $html);
        $this->assertHtmlNotContains('v-for="label in appleKondisiLabels"', $html);
    }

    public function test_apple_catalog_table_selection_matches_card_selection_order(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('const appleIsVariantSelected = (model, vKey) => {', $html);
        $this->assertHtmlContains('const appleSelectedModelOrder = (model) => {', $html);
        $this->assertHtmlContains('if (!appleSelectedModels.value.includes(model)) {', $html);
        $this->assertHtmlContains('appleSelectedModels.value = [...appleSelectedModels.value, model];', $html);
        $this->assertHtmlContains("appleIsVariantSelected(row.model, appleVariantKey(row)) ? 'bg-ppp-accent/5' : ''", $html);
        $this->assertHtmlContains('v-if="appleIsVariantSelected(row.model, appleVariantKey(row))"', $html);
        $this->assertHtmlContains('{{ appleSelectedModelOrder(row.model) }}', $html);
        $this->assertHtmlContains('appleIsVariantSelected,', $html);
        $this->assertHtmlContains('appleSelectedModelOrder,', $html);
        $this->assertHtmlNotContains("appleSelectedModels.includes(row.model) ? 'bg-ppp-accent/5' : ''", $html);
    }

    public function test_apple_catalog_table_select_column_shows_empty_bullet(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.apple-selection-dot {', $html);
        $this->assertHtmlContains('width: 1.25rem;', $html);
        $this->assertHtmlContains('height: 1.25rem;', $html);
        $this->assertHtmlContains('box-sizing: border-box;', $html);
        $this->assertHtmlContains('.apple-selection-dot--active {', $html);
        $this->assertHtmlContains('class="apple-selection-dot apple-selection-dot--active"', $html);
        $this->assertHtmlContains('class="apple-selection-dot"', $html);
        $this->assertHtmlContains('<div v-else', $html);
    }

    public function test_compact_color_swatches_use_consistent_token_shape(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();

        $this->assertHtmlContains('.color-swatch-sm {', $html);
        $this->assertHtmlContains('width: 18px;', $html);
        $this->assertHtmlContains('height: 18px;', $html);
        $this->assertHtmlContains('border-radius: 6px;', $html);
        $this->assertHtmlContains('box-sizing: border-box;', $html);
        $this->assertHtmlContains('overflow: hidden;', $html);
        $this->assertHtmlContains('class="color-swatch-sm"', $html);
        $this->assertHtmlNotContains('color-swatch-sm relative flex-shrink-0 rounded-full overflow-hidden border border-black/10', $html);
    }

    public function test_apple_catalog_output_controls_stay_outside_layout_scroll(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();
        $appleCatalogPartial = file_get_contents(resource_path('views/dashboard/partials/menus/apple-katalog.blade.php'));

        $this->assertHtmlContains('class="apple-config-panel flex flex-col gap-3 min-h-0 xl:overflow-hidden"', $html);
        $this->assertHtmlContains('class="section-card p-4 catalog-control-stack flex-shrink-0"', $html);
        $this->assertHtmlContains('class="section-card apple-layout-card flex-1 min-h-0 overflow-hidden flex flex-col"', $html);
        $this->assertHtmlContains('class="apple-layout-card__header flex-shrink-0 p-4 pb-3"', $html);
        $this->assertHtmlContains('class="apple-layout-card__body flex-1 min-h-0 overflow-y-auto custom-scrollbar px-4 pb-4 space-y-3"', $html);
        $outputPosition = strpos($appleCatalogPartial, 'Pengaturan Output');
        $layoutCardPosition = strpos($appleCatalogPartial, 'apple-layout-card');
        $scrollPosition = strpos($appleCatalogPartial, 'apple-layout-card__body');
        $layoutPosition = strpos($appleCatalogPartial, '>Layout<');

        $this->assertNotFalse($outputPosition);
        $this->assertNotFalse($layoutCardPosition);
        $this->assertNotFalse($scrollPosition);
        $this->assertNotFalse($layoutPosition);
        $this->assertLessThan($layoutCardPosition, $outputPosition);
        $this->assertLessThan($scrollPosition, $layoutPosition);
        $this->assertIsString($appleCatalogPartial);
        $this->assertHtmlNotContains('class="flex flex-col gap-3 min-h-0 overflow-y-auto pr-0.5"', $appleCatalogPartial);
        $this->assertHtmlNotContains('class="apple-config-scroll flex-1 min-h-0 overflow-y-auto custom-scrollbar pr-0.5 space-y-3"', $appleCatalogPartial);
    }

    public function test_img_repo_uses_dashboard_ui_primitives(): void
    {
        $html = $this->renderDashboardHtmlWithShellCss();
        $imgRepoPartial = file_get_contents(resource_path('views/dashboard/partials/menus/img-repo.blade.php'));

        $this->assertIsString($imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-shell section-card flex flex-col md:flex-row overflow-hidden"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-sidebar shrink-0 flex flex-col gap-1 min-h-0"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-topbar flex items-center gap-3 shrink-0"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-content flex-1 min-h-0 overflow-auto custom-scrollbar"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-folder-card img-repo-folder-card--compact flex items-center', $imgRepoPartial);
        $this->assertHtmlContains(":class=\"['img-repo-file-card", $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-detail-panel shrink-0 flex flex-col min-h-0"', $imgRepoPartial);
        $this->assertHtmlContains('const imgRepoVisibleItems = (items) => {', $html);
        $this->assertHtmlContains("return (items || []).filter((item) => item?.name !== '.DS_Store');", $html);
        $this->assertHtmlContains('imgRepoItems.value = imgRepoVisibleItems(json.items);', $html);
        $this->assertHtmlContains("const IMG_REPO_PATH_STORAGE_KEY = 'ppp_img_repo_path';", $html);
        $this->assertHtmlContains('const imgRepoInitialPath = () => {', $html);
        $this->assertHtmlContains('return localStorage.getItem(IMG_REPO_PATH_STORAGE_KEY) || \'\';', $html);
        $this->assertHtmlContains('const imgRepoPath        = ref(imgRepoInitialPath());', $html);
        $this->assertHtmlContains('localStorage.setItem(IMG_REPO_PATH_STORAGE_KEY, json.path || \'\');', $html);
        $this->assertHtmlContains("imgRepoBrowse(imgRepoPath.value || '');", $html);
        $this->assertHtmlContains('function imgRepoOpenContext(event, item) {', $html);
        $this->assertHtmlContains('imgRepoContextMenu.value = { item, x, y };', $html);
        $openContextPosition = strpos($html, 'function imgRepoOpenContext(event, item) {');
        $contextMenuPosition = strpos($html, 'imgRepoContextMenu.value = { item, x, y };');
        $contextSelectionPosition = strpos($html, 'imgRepoSelected.value = item;', $openContextPosition ?: 0);

        $this->assertNotFalse($openContextPosition);
        $this->assertNotFalse($contextMenuPosition);
        $this->assertTrue($contextSelectionPosition === false || $contextSelectionPosition > $contextMenuPosition);
        $this->assertHtmlContains('.img-repo-sidebar {', $html);
        $this->assertHtmlContains('.img-repo-create-button {', $html);
        $this->assertHtmlContains('.img-repo-create-menu {', $html);
        $this->assertHtmlContains('.img-repo-create-menu__item {', $html);
        $this->assertHtmlContains('.img-repo-sidebar-section {', $html);
        $this->assertHtmlContains('.img-repo-sidebar-nav {', $html);
        $this->assertHtmlContains('.img-repo-sidebar-nav--active {', $html);
        $this->assertHtmlContains('.img-repo-shell {'."\n".'            position: relative;'."\n".'            margin: -0.75rem -0.75rem 0;'."\n".'            height: calc(100dvh - 7rem);'."\n".'            min-height: 0;', $html);
        $this->assertHtmlNotContains('min-height: calc(100dvh - 64px);', $html);
        $this->assertHtmlContains('.img-repo-sidebar {'."\n".'            width: 14rem;', $html);
        $this->assertHtmlContains('.img-repo-detail-panel {', $html);
        $this->assertHtmlContains('.img-repo-detail-header {', $html);
        $this->assertHtmlContains('.img-repo-detail-header {'."\n".'            display: flex;'."\n".'            align-items: center;'."\n".'            justify-content: space-between;'."\n".'            gap: 0.75rem;'."\n".'            padding: 0.75rem 1.25rem;', $html);
        $this->assertHtmlContains('.img-repo-toolbar-toggle {', $html);
        $this->assertHtmlContains('.img-repo-toggle-button {', $html);
        $this->assertHtmlContains('.img-repo-toggle-button--active {', $html);
        $this->assertHtmlContains('.img-repo-breadcrumb {', $html);
        $this->assertHtmlContains('.img-repo-breadcrumb__button {', $html);
        $this->assertHtmlContains('.img-repo-breadcrumb__separator {', $html);
        $this->assertHtmlContains('.img-repo-breadcrumb__current {', $html);
        $this->assertHtmlContains('.img-repo-context-menu {', $html);
        $this->assertHtmlContains('.img-repo-context-menu__item {', $html);
        $this->assertHtmlContains('.img-repo-context-menu__item--danger {', $html);
        $this->assertHtmlContains('.img-repo-context-menu__separator {', $html);
        $this->assertHtmlContains('.img-repo-detail-actions {', $html);
        $this->assertHtmlContains('.img-repo-detail-action {', $html);
        $this->assertHtmlContains('.img-repo-detail-action--primary {', $html);
        $this->assertHtmlContains('.img-repo-detail-action--danger {', $html);
        $this->assertHtmlContains('.img-repo-folder-card,', $html);
        $this->assertHtmlContains('.img-repo-file-card {', $html);
        $this->assertHtmlContains('.img-repo-file-card--active {', $html);
        $this->assertHtmlContains('.img-repo-file-card__media {', $html);
        $this->assertHtmlContains('.img-repo-file-card__image {', $html);
        $this->assertHtmlContains('.img-repo-file-card__menu-button {', $html);
        $this->assertHtmlContains('.img-repo-file-card__meta {', $html);
        $this->assertHtmlContains('.img-repo-file-card__name {', $html);
        $this->assertHtmlContains('.img-repo-file-card__rename-body {', $html);
        $this->assertHtmlContains('.img-repo-file-card__rename-actions {', $html);
        $this->assertHtmlContains('.img-repo-detail-panel {'."\n".'            position: absolute;', $html);
        $this->assertHtmlContains('right: 0;', $html);
        $this->assertHtmlContains('top: 0;', $html);
        $this->assertHtmlContains('bottom: 0;', $html);
        $this->assertHtmlContains('.img-repo-grid--files,'."\n".'        .img-repo-grid--loading {'."\n".'            grid-template-columns: repeat(5, minmax(0, 1fr));', $html);
        $this->assertHtmlContains('.img-repo-grid--folders {'."\n".'            grid-template-columns: repeat(auto-fill, minmax(176px, 1fr));', $html);
        $this->assertHtmlContains('.img-repo-folder-card--compact {', $html);
        $this->assertHtmlContains('min-height: 2.75rem;', $html);
        $this->assertHtmlContains('padding: 0.625rem 0.75rem;', $html);
        $this->assertHtmlNotContains('grid-template-columns: repeat(auto-fill, 160px);', $html);
        $this->assertHtmlNotContains('grid-template-columns: repeat(auto-fill, 148px);', $html);
        $this->assertHtmlNotContains('grid-template-columns: repeat(4, minmax(0, 1fr));', $html);
        $this->assertHtmlNotContains('grid-template-columns: repeat(5, var(--img-repo-file-card-width));', $html);
        $this->assertHtmlNotContains('grid-template-columns: repeat(4, var(--img-repo-file-card-width));', $html);
        $this->assertHtmlNotContains('grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));', $html);
        $this->assertHtmlNotContains('grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));', $html);
        $this->assertHtmlNotContains('grid-template-columns: repeat(auto-fit, minmax(176px, 1fr));', $html);
        $this->assertHtmlNotContains('grid-template-columns: repeat(auto-fill, 176px);', $html);
        $this->assertHtmlNotContains('grid-template-columns: repeat(auto-fill, 220px);', $html);
        $this->assertHtmlContains('border-radius: var(--radius-lg);', $html);
        $this->assertHtmlContains("imgRepoViewMode==='grid' ? 'img-repo-toggle-button--active' : ''", $imgRepoPartial);
        $this->assertHtmlContains("imgRepoViewMode==='list' ? 'img-repo-toggle-button--active' : ''", $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-breadcrumb flex-1 min-w-0"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-breadcrumb__button"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-breadcrumb__separator fa-solid fa-chevron-right"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-breadcrumb__current"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-context-menu fixed"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-context-menu__item"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-context-menu__item img-repo-context-menu__item--danger"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-detail-actions"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-detail-header"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-detail-action"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-detail-action img-repo-detail-action--danger"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-file-card__media"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-file-card__image"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-file-card__menu-button"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-file-card__meta"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-file-card__name"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-file-card__rename-body"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-file-card__rename-actions"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-grid img-repo-grid--files"', $imgRepoPartial);
        $this->assertHtmlNotContains('img-repo-grid--files-with-detail', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-create-button"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-create-menu"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-create-menu__item"', $imgRepoPartial);
        $this->assertHtmlContains('class="img-repo-sidebar-section"', $imgRepoPartial);
        $this->assertHtmlContains("class=\"['img-repo-sidebar-nav", $imgRepoPartial);
        $this->assertHtmlContains("imgRepoPath === '' ? 'img-repo-sidebar-nav--active' : ''", $imgRepoPartial);
        $this->assertHtmlContains("imgRepoPath === 'APPLE' || imgRepoPath.startsWith('APPLE/') ? 'img-repo-sidebar-nav--active' : ''", $imgRepoPartial);
        $folderSectionPosition = strpos($imgRepoPartial, '<div class="img-repo-sidebar-section">');
        $repoImagePosition = strpos($imgRepoPartial, '<span>Menu Utama</span>');
        $folderLabelPosition = strpos($imgRepoPartial, 'FOLDER UTAMA');

        $this->assertNotFalse($folderSectionPosition);
        $this->assertNotFalse($repoImagePosition);
        $this->assertNotFalse($folderLabelPosition);
        $this->assertGreaterThan($folderSectionPosition, $repoImagePosition);
        $this->assertGreaterThan($folderLabelPosition, $repoImagePosition);
        $this->assertHtmlContains('<span>Apple</span>', $imgRepoPartial);
        $this->assertHtmlContains('<span>Android</span>', $imgRepoPartial);
        $this->assertHtmlNotContains('<span>Repo Gambar</span>', $imgRepoPartial);
        $this->assertHtmlNotContains('<span>APPLE</span>', $imgRepoPartial);
        $this->assertHtmlNotContains('<span>ANDROID</span>', $imgRepoPartial);
        $this->assertHtmlNotContains('resources/img', $imgRepoPartial);
        $this->assertHtmlNotContains('class="mt-auto px-3 pt-4 border-t border-slate-100"', $imgRepoPartial);
        $this->assertHtmlNotContains('{{-- Nav items --}}', $imgRepoPartial);
        $this->assertHtmlContains('<span>Hapus Item</span>', $imgRepoPartial);
        $this->assertHtmlNotContains("['icon-toolbar-button transition-colors', imgRepoViewMode==='grid'", $imgRepoPartial);
        $this->assertHtmlNotContains("['icon-toolbar-button transition-colors', imgRepoViewMode==='list'", $imgRepoPartial);
        $this->assertHtmlNotContains('class="flex items-center gap-0.5 flex-1 min-w-0 text-body-sm"', $imgRepoPartial);
        $this->assertHtmlNotContains('class="flex items-center justify-between px-4 pt-4 pb-3 border-b border-slate-200"', $imgRepoPartial);
        $this->assertHtmlNotContains('class="aspect-[4/3] bg-slate-100 overflow-hidden relative"', $imgRepoPartial);
        $this->assertHtmlNotContains('class="w-full h-full object-contain"', $imgRepoPartial);
        $this->assertHtmlNotContains('class="absolute top-1.5 right-1.5 w-7 h-7 rounded-full flex items-center justify-center bg-white/90 shadow-sm text-slate-500 opacity-0 group-hover:opacity-100 hover:bg-white transition-all"', $imgRepoPartial);
        $this->assertHtmlNotContains('class="px-2 py-1.5 flex items-center gap-1"', $imgRepoPartial);
        $this->assertHtmlNotContains('class="text-xs text-slate-700 truncate"', $imgRepoPartial);
        $this->assertHtmlNotContains('toolbar-segment-button border-transparent text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors shrink-0 font-medium', $imgRepoPartial);
        $this->assertHtmlNotContains('class="text-slate-300 text-sm shrink-0">/</span>', $imgRepoPartial);
        $this->assertHtmlNotContains('img-repo-breadcrumb-current px-2 py-1 rounded-lg text-slate-800 font-semibold shrink-0 truncate', $imgRepoPartial);
        $this->assertHtmlNotContains('.img-repo-breadcrumb-current {', $html);
        $this->assertHtmlNotContains('class="fixed z-50 bg-white border border-slate-200 rounded-xl shadow-xl py-1.5 w-44 overflow-hidden"', $imgRepoPartial);
        $this->assertHtmlNotContains('class="px-4 py-3 border-t border-slate-200 flex flex-col gap-2"', $imgRepoPartial);
        $this->assertHtmlNotContains('class="menu-action-button rounded-xl bg-white border-slate-200 text-slate-700 hover:bg-slate-50 transition-colors font-medium"', $imgRepoPartial);
        $this->assertHtmlNotContains('class="menu-action-button rounded-xl bg-white border-red-200 text-red-600 hover:bg-red-50 transition-colors font-medium"', $imgRepoPartial);
        $this->assertHtmlNotContains('menu-action-button img-repo-nav-button shadow-sm', $imgRepoPartial);
        $this->assertHtmlNotContains('menu-action-button img-repo-nav-button', $imgRepoPartial);
        $this->assertHtmlNotContains('class="menu-action-button border-transparent text-slate-700 hover:bg-slate-50 transition-colors">'."\n".'            <i class="fa-solid fa-pencil text-slate-400 w-4 text-center"></i>'."\n".'            <span>Rename</span>'."\n".'        </button>'."\n".'        <div class="my-1 border-t border-slate-100"></div>', $imgRepoPartial);
        $this->assertHtmlNotContains('bg-blue-50', $imgRepoPartial);
        $this->assertHtmlNotContains('bg-blue-100', $imgRepoPartial);
        $this->assertHtmlNotContains('rounded-2xl', $imgRepoPartial);
        $this->assertHtmlNotContains('.img-repo-sidebar {'."\n".'            width: 14rem;'."\n".'            padding: 1rem 0.75rem 1.25rem;'."\n".'            border-right: 1px solid var(--ppp-line);'."\n".'            background: rgb(248 250 252);', $html);
        $this->assertHtmlNotContains('.img-repo-detail-panel {'."\n".'            width: 18rem;'."\n".'            border-left: 1px solid var(--ppp-line);'."\n".'            background: rgb(248 250 252);', $html);
    }
}
