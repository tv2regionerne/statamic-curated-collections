<template>
    <table class="data-table curated-collections-field-table">
        <thead>
            <tr>
                <th></th>
                <th>List</th>
                <th class="text-center">#</th>
                <th>Fjern fra listen den</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(row, index) in rows" :key="row.id" class="outline-none">
                <td class="pt-0 pb-0 w-1">
                    <entries-panel-toggle
                        :initial-value="row.entry !== null"
                        @on="$emit('added', row.curatedCollection.handle, row.curatedCollection.display_form_computed)"
                        @off="$emit('deleted', row.curatedCollection.handle, row.entry)"
                    />
                </td>
                <td class="w-full">
                    {{ row.curatedCollection.title }}
                </td>
                <td class="text-center">
                    <template v-if="row.entry">
                        {{ row.entry.status === 'published' ? row.entry.order_column : row.entry.publish_order }}
                    </template>
                </td>
                <td class="whitespace-nowrap">
                    <template v-if="row.entry">
                        {{ row.entry.status === 'published' ? $moment(row.entry.unpublish_at).format('lll') : '' }}
                    </template>
                </td>
                <td class="pt-0 pb-0">
                    <div class="flex justify-end" v-if="row.entry">
                        <button @click="$emit('edited', row.curatedCollection.handle, row.entry)" class="w-4 h-4 ml-2 opacity-50 hover:opacity-100" v-tooltip="__('Edit')">
                            <svg-icon name="micro/pencil" class="h-4 w-4" />
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
import EntriesPanelToggle from './EntriesPanelToggle.vue';

export default {

    inject: ['sharedState'],

    components: {
        EntriesPanelToggle,
    },

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