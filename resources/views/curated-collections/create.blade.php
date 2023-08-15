@extends('statamic::layout')
@section('title', __('Create Navigation'))

@section('content')
    <curated-collection-create-form
        route="{{ cp_route('curated-collections.store') }}">
    </curated-collection-create-form>
@stop
