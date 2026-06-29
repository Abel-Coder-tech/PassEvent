<?php

namespace App\Console\Commands;

use App\Models\Evenement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate the sitemap.xml';

    public function handle()
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create();

        $base = config('app.url');

        $sitemap->add(Url::create($base)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)->setPriority(1.0));
        $sitemap->add(Url::create($base.'/evenements')->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)->setPriority(0.9));
        $sitemap->add(Url::create($base.'/aide')->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)->setPriority(0.5));
        $sitemap->add(Url::create($base.'/contact')->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)->setPriority(0.5));
        $sitemap->add(Url::create($base.'/confidentialite')->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)->setPriority(0.3));
        $sitemap->add(Url::create($base.'/cgu')->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)->setPriority(0.3));
        $sitemap->add(Url::create($base.'/mentions-legales')->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)->setPriority(0.3));
        $sitemap->add(Url::create($base.'/politique-remboursement')->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)->setPriority(0.3));

        if (Schema::hasTable('evenements')) {
            try {
                $evenements = Evenement::where('statut', 'publie')->orWhereNull('statut')->get();
                foreach ($evenements as $event) {
                    $sitemap->add(
                        Url::create($base.'/evenements/'.$event->id)
                            ->setLastModificationDate($event->updated_at ?? $event->created_at ?? now())
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                }
            } catch (\Exception $e) {
                $this->warn('Could not fetch events: '.$e->getMessage());
            }
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated at '.public_path('sitemap.xml'));
    }
}
