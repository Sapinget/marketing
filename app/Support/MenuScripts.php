<?php

namespace App\Support;

use Illuminate\Support\Facades\View;

/**
 * Memilih script milik menu (`shell/menu-script-<menu>.blade.php`) yang harus dimuat sebuah halaman.
 *
 * Satu menu = satu blade markup (`menus/<menu>.blade.php`) = satu script (`menu-script-<menu>`).
 * Halaman menu memuat script menunya, script partial yang di-@include menunya (mis. story-modal),
 * dan script view tambahan (mis. content-modal). Dashboard lama (`/`) dengan flag url_routing mati
 * merender semua menu, jadi memuat semua script menu.
 */
class MenuScripts
{
    private const PREFIX = 'dashboard.partials.shell.menu-script-';

    /**
     * @param  list<string>  $menuViews  nama view menu, mis. `dashboard.partials.menus.calendar`
     * @return list<string> nama view script yang ada
     */
    public static function forViews(array $menuViews): array
    {
        $found = [];
        $seen = [];

        $visit = function (string $view) use (&$visit, &$found, &$seen): void {
            if (isset($seen[$view])) {
                return;
            }
            $seen[$view] = true;

            $script = self::PREFIX.substr($view, (int) strrpos($view, '.') + 1);
            if (View::exists($script)) {
                $found[$script] = true;
            }

            $path = resource_path('views/'.str_replace('.', '/', $view).'.blade.php');
            if (! is_file($path)) {
                return;
            }

            preg_match_all("/@include\\('(dashboard\\.partials\\.menus\\.[\\w-]+)'\\)/", (string) file_get_contents($path), $m);
            foreach ($m[1] as $nested) {
                $visit($nested);
            }
        };

        foreach ($menuViews as $view) {
            $visit($view);
        }

        return array_keys($found);
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        $views = [];

        foreach (glob(resource_path('views/dashboard/partials/shell/menu-script-*.blade.php')) ?: [] as $path) {
            $views[] = self::PREFIX.substr(basename($path, '.blade.php'), strlen('menu-script-'));
        }

        sort($views);

        return $views;
    }
}
