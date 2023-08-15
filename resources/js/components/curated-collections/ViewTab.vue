<template>

    <div class="curated-collections-view-tab">

        <div class="flex">
            <div class="w-1/2">

                <div v-if="initializing" class="flex items-center justify-center text-center">
                    <loading-graphic />
                </div>

                <data-list
                    v-if="!initializing"
                    class="curated-collections-listing"
                    :sort="false"
                    :rows="items"
                >
                    <div>
                        <div class="card overflow-hidden p-0 relative">
                            <div class="overflow-x-auto overflow-y-hidden">
                                <view-table
                                    tableType="listing"
                                    :activeStatus="activeStatus"
                                    :draggableProps="{
                                        group: { name: 'view', put: true, pull: true },
                                    }"
                                    :draggableEvents="{
                                        change: entryDropped,
                                    }"
                                    @edited="editEntry"
                                    @deleted="deleteEntry"
                                />
                            </div>
                        </div>
                    </div>
                </data-list>

            </div>
            <div class="w-1/2 pl-6">

                <view-lookup
                    ref="lookup"
                    :activeStatus="activeStatus"
                    :exclusions="exclusions"
                    :handle="handle"
                    :collections="collections"
                />

            </div>
        </div>

        <entry-form
            v-if="showEntry"
            :handle="handle"
            :mode="formMode"
            :id="formId"
            :initial-values="formValues"
            :errors="formErrors"
            @closed="closeEntry"
            @submit="saveEntry"
        />

    </div>

</template>

<script>
import qs from "qs";
import EntryForm from "./EntryForm.vue";
import ViewTable from "./ViewTable.vue";
import ViewLookup from "./ViewLookup.vue";

export default {

    mixins: [
        Listing
    ],

    components: {
        EntryForm,
        ViewLookup,
        ViewTable,
    },

    props: {
        handle: { type: String, required: true },
        breadcrumbUrl: { type: String, required: true },
        collections: { type: Array, required: true },
        displayFormComputed: { type: Boolean, required: true },
        activeStatus: { type: String, required: true },
    },

    data() {
        return {
            showEntry: false,
            formMode: null,
            formId: null,
            formValues: {},
            formErrors: [],
        }
    },

    computed: {

        requestUrl() {
            return cp_url(`curated-collections/api/collections/${this.handle}/entries`) + '?' + qs.stringify({
                status: this.activeStatus,
            });
        },

        exclusions() {
            return this.items.map(item => item.entry ? item.entry[0].id : null);
        },

    },

    methods: {

        closeEntry() {
            this.request();
            this.formMode = null;
            this.formId = null;
            this.formValues = {};
            this.showEntry = false;
        },

        openEntry() {
            this.showEntry = true;
        },

        createEntry(entry) {
            this.formMode = 'create';
            this.formValues = entry;
            if (this.displayFormComputed) {
                this.openEntry();
            } else {
                this.saveEntry('create', entry);
            }
        },

        editEntry(entry) {
            this.formMode = 'edit';
            this.formId = entry.id;
            this.openEntry();
        },

        entryDropped(event) {
            if (event.moved) {
                const { oldIndex, newIndex } = event.moved;
                this.entryMoved(oldIndex, newIndex);
            } else if (event.added) {
                const { element, newIndex } = event.added;
                this.entryAdded(element, newIndex);
            } else if (event.removed) {
                console.log('event.removed', event.removed);
                const { element } = event.removed;
                console.log('event.removed.element', element);
                this.deleteEntry(element);
            }
        },

        entryMoved(oldIndex, newIndex) {
            const items = [...this.items]
            const removed = [
                ...items.slice(0, oldIndex),
                ...items.slice(oldIndex + 1)
            ];
            const added = [
                ...removed.slice(0, newIndex),
                items[oldIndex],
                ...removed.slice(newIndex)
            ];
            this.reorderEntries(added);
        },

        entryAdded(element, index) {
            const entry = {
                ...(this.activeStatus === 'published' ? { order: index + 1 } : { publish_order: index + 1 }),
                entry: element.entry.map(entry => entry.id),
                published: element.published,
            };
            this.createEntry(entry);
        },

        saveEntry(mode, entry) {
            const method = mode === 'edit' ? 'patch' : 'post';
            const url = mode === 'edit'
                ? cp_url(`curated-collections/api/collections/${this.handle}/entries/${entry.id}`)
                : cp_url(`curated-collections/api/collections/${this.handle}/entries`);
            const payload = {
                ...entry,
                curated_collection: this.handle,
            };
            this.$axios[method](url, payload)
                .then(response => {
                    this.closeEntry();
                    this.$toast.success(mode === 'edit'
                        ? __('Entry successfully updated')
                        : __('Entry successfully created'))
                }).catch(e => {
                    if (e.response && e.response.status === 422) {
                        if (!this.showEntry) {
                            this.openEntry();
                        } else {
                            const { message, errors } = e.response.data;
                            this.formErrors = errors;
                            this.$toast.error(message);
                        }
                    } else if (e.response) {
                        this.$toast.error(e.response.data.message);
                    } else {
                        console.log(e);
                        this.$toast.error(__('Something went wrong'));
                    }
                });
        },

        deleteEntry(entry) {
            console.log('deleteEntry', entry);
            const url = cp_url(`curated-collections/api/collections/${this.handle}/entries/${entry.id}`);
            this.$axios.delete(url)
                .then(response => {
                    this.request();
                    this.$toast.success(__('Entry successfully deleted'))
                }).catch(e => {
                    console.log(e);
                    this.$toast.error(__('Something went wrong'));
                });
        },

        reorderEntries(entries) {
            const url = cp_url(`curated-collections/api/collections/${this.handle}/entries/reorder`);
            const payload = {
                ids: entries.map(item => item.id),
                curated_collection: this.handle,
            };
            this.$axios.post(url, payload)
                .then(response => {
                    this.request();
                    this.$toast.success(__('Entries successfully reordered'))
                })
                .catch(e => {
                    console.log(e);
                    this.$toast.error(__('Something went wrong'));
                });
        },

    }

}
</script>