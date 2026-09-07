<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CatholiqueVersionCommand extends Command
{
    protected $signature = 'catholique:version';

    protected $description = 'Affiche la version applicative Catholique et la date de release';

    public function handle(): int
    {
        $name = config('app.name', 'Catholique');
        $version = config('catholique.version', '?.?.?');
        $releasedAt = config('catholique.released_at');
        $env = config('app.env');

        $this->line("{$name} {$version} ({$env})");

        if ($releasedAt) {
            $this->line("Release : {$releasedAt}");
        }

        return self::SUCCESS;
    }
}
