@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('curated-collections.show', $curatedCollection->handle),
        'title' => $curatedCollection->title,
    ])

    <blueprint-builder
        action="{{ cp_route('curated-collections.blueprint.update', $curatedCollection->handle) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
        :use-tabs="true"
    ></blueprint-builder>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
