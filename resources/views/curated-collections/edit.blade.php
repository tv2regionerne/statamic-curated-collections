@extends('statamic::layout')
@section('title', __('Edit Curated Collection'))

@section('content')

    <header class="mb-6">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('curated-collections.show', $curatedCollection->handle),
            'title' => $curatedCollection->title
        ])
        <h1>@yield('title')</h1>
    </header>

    <curated-collection-edit-form
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ $curatedCollection->showUrl() }}"
    ></curated-collection-edit-form>

@endsection
