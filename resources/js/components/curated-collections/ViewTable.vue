<template>
    <table class="data-table curated-collections-view-table" :data-type="`${activeStatus}-${tableType}`">
        <thead v-if="tableType === 'listing'">
            <tr>
                <th data-show="published draft-lookup" class="px-0"></th>
                <th data-show="published-listing" class="text-center pr-0">#</th>
                <th>Entry</th>
                <th data-show="published-listing">Fjern fra listen den</th>
                <th data-show="lookup"></th>
                <th data-show="listing"></th>
            </tr>
        </thead>
        <draggable
            tag="tbody"
            v-model="rows"
            @start="dragStart"
            @end="dragEnd"
            :move="onMove"
            handle=".table-drag-handle"
            v-bind="draggableProps"
            v-on="draggableEvents">
            <tr v-for="(row, index) in rows" :key="row.id" class="outline-none" :data-id="row.id">
                <td data-show="published draft-lookup" class="table-drag-handle"></td>
                <td data-show="published-listing" class="text-center pr-0">
                    {{ row.order || ' ' }}
                </td>
                <td class="pr-0">
                    <div class="relationship-index-field">
                        <div class="flex flex-wrap">
                            <div class="relationship-index-field-item">
                                <div class="flex items-center shrink">
                                    <div class="little-dot h-1 w-1 mr-1" :class="[row.entry[0].published ? 'bg-green-600' : 'bg-gray-400']" />
                                    <a :href="row.entry[0].edit_url" :title="row.entry[0].title" v-text="row.entry[0].title" />
                                </div>
                            </div>
                        </div>
                    </div>    
                </td>
                <td data-show="published-listing" class="whitespace-nowrap pr-0">
                    {{ row.status === 'published' ? $moment(row.unpublish_at).format('lll') : ' ' }}
                </td>
                <td data-show="lookup" class="text-right">
                    <span class="rounded px-1 py-px text-2xs uppercase bg-gray-200 text-gray">
                        {{ row.collection.title }}
                    </span>
                </td>
                <td data-show="listing" class="pt-0 pb-0">
                    <div class="flex justify-end">
                        <button @click="$emit('edited', row)" class="w-4 h-4 ml-2 opacity-50 hover:opacity-100" v-tooltip="__('Edit')">
                            <svg-icon name="micro/pencil" class="h-4 w-4" />
                        </button>
                        <button @click="$emit('deleted', row)" class="w-4 h-4 ml-2 opacity-50 hover:opacity-100" v-tooltip="__('Delete')">
                            <svg-icon name="micro/circle-with-cross" class="h-4 w-4" />
                        </button>
                    </div>
                </td>
            </tr>
            <tr v-if="rows.length === 0">
                <td colspan="6">
                    <div class="p-6 text-center text-gray-500" v-text="__('No items')" />
                </td>
            </tr>
        </draggable>
    </table>
</template>

<script>
import draggable from 'vuedraggable';

export default {

    components: {
        draggable,
    },

    data() {
        return {
            dragOngoing: false,
        }
    },

    props: {
        tableType: {
            type: String
        },
        activeStatus: {
            type: String
        },
        draggableProps: {
            type: Object
        },
        draggableEvents: {
            type: Object
        },
    },

    methods : {
        dragStart(event) {
            if (this.tableType === 'listing') {
                this.$events.$emit('curated-list-drag-start', event, this.draggableProps.group.name);
            }
        },
        dragEnd(event) {
            if (this.tableType === 'listing') {
                this.$events.$emit('curated-list-drag-end', event, this.draggableProps.group.name);
            }
        },
        onMove (event) {
            if (this.tableType === 'listing') {
                this.$events.$emit('curated-list-drag-move', event, this.draggableProps.group.name);
            }
        }
    },

    inject: ['sharedState'],

    computed: {

        rows: {
            get() {
                return this.sharedState.rows;
            },
            set(rows) {
                this.sharedState.rows = rows;
            }
        },

    },

}
</script>

<style>
.curated-collections-view-table td.table-drag-handle {
    width: 1.25rem !important;
}
.curated-collections-view-table tbody {
    min-height: 5rem;
}
.curated-collections-view-table th[data-show],
.curated-collections-view-table td[data-show] {
    display: none;
}
.curated-collections-view-table[data-type="published-listing"] [data-show~="listing"],
.curated-collections-view-table[data-type="published-lookup"] [data-show~="lookup"],
.curated-collections-view-table[data-type="published-listing"] [data-show~="published"],
.curated-collections-view-table[data-type="published-lookup"] [data-show~="published"],
.curated-collections-view-table[data-type="published-listing"] [data-show~="published-listing"],
.curated-collections-view-table[data-type="published-lookup"] [data-show~="published-lookup"],
.curated-collections-view-table[data-type="draft-listing"] [data-show~="listing"],
.curated-collections-view-table[data-type="draft-lookup"] [data-show~="lookup"],
.curated-collections-view-table[data-type="draft-listing"] [data-show~="draft"],
.curated-collections-view-table[data-type="draft-lookup"] [data-show~="draft"],
.curated-collections-view-table[data-type="draft-listing"] [data-show~="draft-listing"],
.curated-collections-view-table[data-type="draft-lookup"] [data-show~="draft-lookup"] {
    display: table-cell;
}
.curated-collections-view-table .sortable-ghost {
    background-color: #e7f4ff;
}

.cc-blue {
    background-color: #1f67a3;
}

</style>