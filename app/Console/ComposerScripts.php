<?php

namespace App\Console;

use Composer\Script\Event;
use Illuminate\Foundation\PackageManifest;

/**
 * Composer callbacks that run inside the Composer process.
 *
 * Hostinger's PHP 8.5 config disables proc_open, so a script such as
 * `@php artisan package:discover` (which Composer starts as a child process)
 * fails there and breaks the Git auto-deploy. Building the package manifest
 * in-process does the same work as `artisan package:discover` without
 * starting a process.
 */
class ComposerScripts
{
    public static function discoverPackages(Event $event): void
    {
        require_once $event->getComposer()->getConfig()->get('vendor-dir').'/autoload.php';

        $app = require dirname(__DIR__, 2).'/bootstrap/app.php';

        $manifest = $app->make(PackageManifest::class);
        $manifest->build();

        foreach (array_keys(require $manifest->manifestPath) as $package) {
            $event->getIO()->write("  <info>{$package}</info> discovered");
        }
    }
}
