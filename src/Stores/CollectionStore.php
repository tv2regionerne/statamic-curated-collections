<?php

namespace Tv2regionerne\StatamicCuratedCollection\Stores;

use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
use Symfony\Component\Finder\SplFileInfo;
use Tv2regionerne\StatamicCuratedCollection\Facades\CuratedCollection;

class CollectionStore extends BasicStore
{

    public function key()
    {
        return 'curated-collections';
    }

    public function getItemFilter(SplFileInfo $file)
    {
        $dir = str_finish($this->directory, '/');
        $relative = str_after(Path::tidy($file->getPathname()), $dir);

        return $file->getExtension() === 'yaml' && substr_count($relative, '/') === 0;
    }

    public function makeItemFromFile($path, $contents)
    {
        $data = YAML::file($path)->parse($contents);

        if (! $id = array_pull($data, 'id')) {
            $idGenerated = true;
            $id = app('stache')->generateId();
        }

        $curatedCollection = CuratedCollection::make()
            ->id($id)
            ->handle(array_pull($data, 'handle'))
            ->fill($data);

        if (isset($idGenerated)) {
            $curatedCollection->save();
        }

        return $curatedCollection;
    }
}
