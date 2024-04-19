<template>

    <div>
        <stack half name="curated-collections-entries-panel" @closed="$emit('closed')">
            <div slot-scope="{ close }" class="bg-gray-100 h-full flex flex-col">

                <header class="bg-white px-6 py-2 border-b shadow-md text-lg font-medium flex items-center justify-between">
                    {{ headerText }}
                    <button
                        type="button"
                        class="btn-close"
                        @click="close(close)"
                        v-html="'&times'" />
                </header>

                <div v-if="initializing" class="flex-1 overflow-auto relative">
                    <div class="absolute inset-0 z-10 bg-white bg-opacity-75 flex items-center justify-center text-center">
                        <loading-graphic />
                    </div>
                </div>

                <div v-if="!initializing" class="flex-1 overflow-auto">

                    <data-list
                        class="curated-collections-field-list"
                        :sort="false"
                        :rows="items"
                    >
                        <div>
                            <entries-panel-table
                                @added="entryAdded"
                                @edited="editEntry"
                                @deleted="deleteEntry"
                            />
                        </div>
                    </data-list>

                </div>

            </div>

        </stack>

        <entry-form
            v-if="showEntry"
            :key="formHandle"
            :handle="formHandle"
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
import EntriesPanelTable from './EntriesPanelTable.vue';
import EntryForm from "../curated-collections/EntryForm.vue";

export default {

    mixins: [
        Listing,
    ],

    components: {
        EntriesPanelTable,
        EntryForm,
    },

    props: {
        entryId: { type: String, required: true },
        entryPublished: { type: Boolean, required: true },
    },

    data() {
        return {
            loading: false,
            showEntry: false,
            formHandle: null,
            formMode: null,
            formId: null,
            formValues: {},
            formErrors: [],
        }
    },

    computed: {

        headerText() {
            return __('statamic-curated-collections::messages.title');
        },

        requestUrl() {
            return cp_url(`curated-collections/api/entry-relations/${this.entryId}`);
        },

    },

    methods: {

        closeEntry() {
            this.request();
            this.formHandle = null;
            this.formMode = null;
            this.formId = null;
            this.formValues = {};
            this.showEntry = false;
        },

        openEntry() {
            this.showEntry = true;
        },

        createEntry(handle, entry) {
            this.formHandle = handle;
            this.formMode = 'create';
            this.formValues = entry;
            this.openEntry();
        },

        editEntry(handle, entry) {
            this.formHandle = handle;
            this.formMode = 'edit';
            this.formId = entry.id;
            this.openEntry();
        },

        entryAdded(handle, displayFormComputed) {
            const entry = {
                entry: [this.entryId],
                published: this.entryPublished,
            };
            // Form has to show as the position is required
            // if (displayFormComputed) {
                this.createEntry(handle, entry);
            // } else {
            //     this.saveEntry('create', entry);
            // }
        },

        saveEntry(mode, entry) {
            const handle = this.formHandle;
            const method = mode === 'edit' ? 'patch' : 'post';
            const url = mode === 'edit'
                ? cp_url(`curated-collections/api/collections/${handle}/entries/${entry.id}`)
                : cp_url(`curated-collections/api/collections/${handle}/entries`);
            const payload = {
                ...entry,
                curated_collection: handle,
            };
            this.$axios[method](url, payload)
                .then(response => {
                    this.closeEntry();
                    this.$toast.success(mode === 'edit'
                        ? __('Entry successfully updated')
                        : __('Entry successfully created'))
                }).catch(e => {
                    if (e.response && e.response.status === 422) {
                        const { message, errors } = e.response.data;
                        this.formErrors = errors;
                        this.$toast.error(message);
                    } else if (e.response) {
                        this.$toast.error(e.response.data.message);
                    } else {
                        console.log(e);
                        this.$toast.error(__('Something went wrong'));
                    }
                });
        },

        deleteEntry(handle, entry) {
            const url = cp_url(`curated-collections/api/collections/${handle}/entries/${entry.id}`);
            this.$axios.delete(url)
                .then(response => {
                    this.request();
                    this.$toast.success(__('Entry successfully deleted'))
                }).catch(e => {
                    console.log(e);
                    this.$toast.error(__('Something went wrong'));
                });
        },

        close(close) {
            close();
        },

    },

}
</script>
