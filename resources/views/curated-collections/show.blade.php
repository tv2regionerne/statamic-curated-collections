@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', Statamic::crumb($curatedCollection->title, 'Curated Collection'))
@section('content-class', '')
@section('wrapper_class', 'max-w-full')

@section('content')

    @if ($collections)

        <curated-collection-view
            title="{{ $curatedCollection->title }}"
            handle="{{ $curatedCollection->handle }}"
            breadcrumb-url="{{ cp_route('curated-collections.index') }}"
            :collections='@json($collections)'
            :can-create="@can('create', ['Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollection', $curatedCollection]) true @else false @endcan"
            :display-form-computed='@json($curatedCollection->display_form_computed)'
        >
            <template #twirldown>
                @can('update', $curatedCollection)
                    <dropdown-item :text="__('Edit Curated Collection')" redirect="{{ $curatedCollection->editUrl() }}"></dropdown-item>
                    <dropdown-item :text="__('Edit Blueprint')" redirect="{{ cp_route('curated-collections.blueprint.edit', $curatedCollection->handle) }}"></dropdown-item>
                @endcan
                @can('delete', $curatedCollection)
                    <dropdown-item :text="__('Delete Curated Collection')" class="warning" @click="$refs.deleter.confirm()">
                        <resource-deleter
                            ref="deleter"
                            resource-title="{{ $curatedCollection->title }}"
                            route="{{ cp_route('curated-collections.destroy', $curatedCollection->handle) }}"
                            redirect="{{ cp_route('curated-collections.index') }}"
                        ></resource-deleter>
                    </dropdown-item>
                @endcan
            </template>
        </curated-collection-view>

    @else
        No collections. Please open settings and add collections.
    @endif


@endsection
