@extends('statamic::layout')
@section('title', __('statamic-curated-collections::messages.title'))

@section('content')

    @unless($curatedCollections->isEmpty())

        <header class="flex items-center justify-between mb-6">
            <h1>{{ __('statamic-curated-collections::messages.title_plural') }}</h1>

            @can('create', 'Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection')
                <a href="{{ cp_route('curated-collections.create') }}" class="btn-primary">{{ __('statamic-curated-collections::messages.create_button') }}</a>
            @endcan
        </header>

        <curated-collection-listing
            :initial-rows="{{ json_encode($curatedCollections) }}">
        </curated-collection-listing>

    @else

        @include('statamic::partials.empty-state', [
            'title' => __('statamic-curated-collections::messages.title'),
            'description' => __('statamic-curated-collections::messages.description'),
            'svg' => 'empty/navigation',
            'button_text' => __('statamic-curated-collections::messages.create_button'),
            'button_url' => cp_route('curated-collections.create'),
            'can' => auth()->user()->can('create', 'Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection')
        ])

    @endunless

@endsection
