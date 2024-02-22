<?php

uses(\Tv2regionerne\StatamicCuratedCollection\Tests\TestCase::class)->in('*');

use Statamic\Facades;

function tag($tag, $data = [])
{
    return (string) Facades\Parse::template($tag, $data);
}

function setupDummyCollectionAndEntries()
{
    $collection = Facades\Collection::make()
        ->handle('articles')
        ->save();

    $entry1 = Facades\Entry::make()
        ->collection('articles')
        ->data([
            'title' => 'Entry 1',
            'sort_field' => 99,
        ])
        ->save();

    $entry3 = Facades\Entry::make()
        ->collection('articles')
        ->data([
            'title' => 'Entry 2',
            'sort_field' => 66,
        ])
        ->save();

    $entry3 = Facades\Entry::make()
        ->collection('articles')
        ->data([
            'title' => 'Entry 3',
            'sort_field' => 33,
        ])
        ->save();
}
