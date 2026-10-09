<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicDirectoryHygieneTest extends TestCase
{
    public function test_public_directory_has_no_symlink_leaving_the_project(): void
    {
        // Pernah ada `public/pma` -> /opt/homebrew/share/phpmyadmin: phpMyAdmin ikut terbuka lewat tunnel Cloudflare.
        $base = realpath(base_path());
        $offenders = [];

        foreach (scandir(public_path()) ?: [] as $entry) {
            $path = public_path($entry);

            if ($entry === '.' || $entry === '..' || ! is_link($path)) {
                continue;
            }

            $target = realpath($path);
            if ($target === false || ! str_starts_with($target, (string) $base)) {
                $offenders[] = $entry.' -> '.readlink($path);
            }
        }

        $this->assertSame([], $offenders, 'public/ must not expose directories outside the project.');
    }

    public function test_database_admin_tools_are_not_served(): void
    {
        $this->get('/pma/')->assertNotFound();
        $this->get('/phpmyadmin/')->assertNotFound();
    }
}
