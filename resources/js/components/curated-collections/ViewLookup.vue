<template>
    <div>
        <div v-if="initializing" class="flex items-center justify-center text-center">
            <loading-graphic />
        </div>
        <data-list
            v-if="!initializing"
            class="curated-collections-view-lookup"
            :sort="false"
            :rows="itemsRestructured"
        >
            <div slot-scope="{ hasSelections }">
                <DropZone group="view">
                    <div class="card overflow-hidden p-0 relative">
                        <div class="flex flex-wrap items-center justify-between px-2 pb-2 text-sm border-b">
                            <data-list-search class="h-8 mt-2 w-full" ref="search" v-model="searchQuery" :placeholder="searchPlaceholder" />
                        </div>
                        <div>
                            <data-list-filters
                                ref="filters"
                                :filters="availableFilters"
                                :active-filters="activeFilters"
                                :active-filter-badges="activeFilterBadges"
                                :active-count="activeFilterCount"
                                :search-query="searchQuery"
                                @changed="filterChanged"
                            />
                        </div>
                        <div class="overflow-x-auto overflow-y-hidden">
                            <view-table
                                tableType="lookup"
                                :activeStatus="activeStatus"
                                :draggableProps="{
                                    group: { name: 'view', put: false, pull: 'clone' },
                                    sort: false,
                                }"
                            >
                                <!-- <template slot="cell-title" slot-scope="{ row: entry }">
                                    <div class="relationship-index-field">
                                        <div class="flex flex-wrap">
                                            <div class="relationship-index-field-item">
                                                <div class="flex items-center shrink">
                                                    <div class="little-dot h-1 w-1 mr-1" :class="[entry.published ? 'bg-green-600' : 'bg-gray-400']" />
                                                    <a :href="entry.edit_url" :title="entry.title" v-text="entry.title" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template> -->
                            </view-table>
                        </div>
                    </div>
                </DropZone>
                <data-list-pagination
                    class="mt-4"
                    :resource-meta="meta"
                    @page-selected="setPage"
                />                
            </div>
        </data-list>
    </div>
</template>

<script>
import qs from "qs";
import ViewTable from "./ViewTable.vue";
import DropZone from "./DropZone.vue";

export default {

    mixins: [
        Listing
    ],

    components: {
        ViewTable,
        DropZone,
    },

    props: {
        handle: { type: String, required: true },
        collections: { type: Array, required: true },
        exclusions: { type: Array, required: false },
        activeStatus: { type: String, required: true },
    },

    data() {
        return {
            sortColumn: 'date',
            sortDirection: 'desc',
            availableFilters: [],
        }
    },

    watch: {
        exclusions: function() {
            this.request();
        },
        availableFilters: function(filters) {
            const filteredFilters = filters.filter(filter => filter.handle !== 'status');
            if (filteredFilters.length !== filters.length) {
                this.availableFilters = filteredFilters;
            }
        },
        activeFilterBadges: function(badges) {
            const originalBadges = Object.entries(badges);
            const filteredBadges = originalBadges.filter(([handle, badge]) => handle !== 'active_status');
            if (filteredBadges.length !== originalBadges.length) {
                this.activeFilterBadges = Object.fromEntries(filteredBadges);
            }
        },
    },
    
    mounted() {
        this.getFilters();
        this.applyActiveStatusFilter();
    },
    
    computed: {

        configParameter() {
            return utf8btoa(JSON.stringify({
                type: 'entries',
                collections: this.collections,
            }));
        },

        requestUrl() {
            return cp_url('fieldtypes/relationship') + '?' + qs.stringify({
                config: this.configParameter,
                collections: this.collections,
                exclusions: this.exclusions,
            });
        },

        filtersUrl() {
            return cp_url('fieldtypes/relationship/filters') + '?' + qs.stringify({
                config: this.configParameter,
                collections: this.collections,
            });
        },

        // Maps the entry results to the same data structre as the curated collection items.
        // Allows us to use the same view table component for both lists and simply toggle the columns.
        itemsRestructured() {
            return this.items.map(item => ({
                order: null,
                entry: [item],
                unpublish_at: null,
                collection: item.collection,
                published: item.status === 'published',
            }));
        },

    },

    methods: {

        applyActiveStatusFilter() {
            this.activeFilters = {
                active_status: { status: this.activeStatus }
            };
        },

        getFilters() {
            return this.$axios.get(this.filtersUrl).then(response => {
                this.availableFilters = response.data;
            });
        },

        setPage(page) {
            this.page = page;
        },

        getStatusLabel(entry) {
            if (entry.status === 'published') {
                return __('Published');
            } else if (entry.status === 'scheduled') {
                return __('Scheduled');
            } else if (entry.status === 'expired') {
                return __('Expired');
            } else if (entry.status === 'draft') {
                return __('Draft');
            }
        },

        getStatusTooltip(entry) {
            if (entry.status === 'published') {
                return entry.collection.unpublish_at
                    ? __('messages.status_published_with_date', {date: entry.date})
                    : null; // The label is sufficient.
            } else if (entry.status === 'scheduled') {
                return __('messages.status_scheduled_with_date', {date: entry.date})
            } else if (entry.status === 'expired') {
                return __('messages.status_expired_with_date', {date: entry.date})
            } else if (entry.status === 'draft') {
                return null; // The label is sufficient.
            }
        },

    },

    watch: {
        searchQuery: function(value) {
            if (!value) {
                this.sortColumn = 'date';
                this.sortDirection = 'desc';
            }
        },
    }

}
</script>

<style>
.curated-collections-view-lookup .actions-column {
    display: none;
}
.curated-collections-view-lookup .order-column {
    display: none;
}
</style>