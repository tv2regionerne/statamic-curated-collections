<?php

namespace Tv2regionerne\StatamicCuratedCollection\Commands;

use Illuminate\Console\Command;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollectionEntry;

class RunAutomation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'curated-collections:automation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run automation for curated collections';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->publishDraftEntries();

        $this->newLine();

        $this->expireAutomatedEntries();

    }

    private function publishDraftEntries()
    {
        // Check all draft entries if they are now published.
        // Workaround for dated collections as no event is fired at the time a date is crossed and status changes
        $entriesToBePublished = CuratedCollectionEntry::where('status', 'draft')
            ->get()
            ->filter(function ($curatedCollectionEntry) {
                if (! $entry = $curatedCollectionEntry->entry()) {
                    return;
                }

                return $entry->status() === 'published';
            });

        $this->info('Found '.$entriesToBePublished->count().' entries to be published');

        $this->withProgressBar($entriesToBePublished, function (CuratedCollectionEntry $curatedCollectionEntry) {
            $curatedCollectionEntry->entry()?->publish();
        });

        $this->info('Published');
    }

    private function expireAutomatedEntries()
    {
        // delete any entries which has expired in curated collections with automation enabled
        $expiredEntriesWithAutomation = CuratedCollectionEntry::where('status', 'published')
            ->whereHas('curatedCollection', function ($query) {
                $query->where('automation', true);
            })
            ->whereNotNull('unpublish_at')
            ->where('unpublish_at', '<', now());

        $this->info('Found '.$expiredEntriesWithAutomation->count().' expired entries which should be deleted');

        $this->withProgressBar($expiredEntriesWithAutomation->get(), function (CuratedCollectionEntry $curatedCollectionEntry) {
            $curatedCollectionEntry->delete();
            $curatedCollectionEntry->curatedCollection->reorderEntries();
        });

        $this->info('Deleted');
    }
}
