<template>

    <stack half name="curated-collections-edit-form" @closed="$emit('closed')">
        <div slot-scope="{ close }" class="bg-gray-100 h-full flex flex-col">

            <header class="bg-white pl-6 pr-3 py-2 mb-4 border-b shadow-md text-lg font-medium flex items-center justify-between">
                {{ headerText }}
                <button
                    type="button"
                    class="btn-close"
                    @click="close(close)"
                    v-html="'&times'" />
            </header>

            <div v-if="loading" class="flex-1 overflow-auto relative">
                <div class="absolute inset-0 z-10 bg-white bg-opacity-75 flex items-center justify-center text-center">
                    <loading-graphic />
                </div>
            </div>

            <div v-if="!loading" class="flex-1 overflow-auto px-3">

                <publish-container
                    v-if="blueprint"
                    ref="container"
                    name="curated-collection"
                    reference="curated-collection"
                    :blueprint="blueprint"
                    :values="values"
                    :meta="meta"
                    :errors="errors"
                    @updated="values = $event"
                    v-slot="{ setFieldValue, setFieldMeta }"
                >
                    <div>
                        <publish-sections
                            :sections="blueprint.tabs[0].sections"
                            @updated="setFieldValue"
                            @meta-updated="setFieldMeta" />
                    </div>
                </publish-container>

            </div>

            <div v-if="!loading" class="bg-gray-200 p-4 border-t flex items-center justify-between flex-row-reverse">
                <div>
                    <button @click="close(close)" class="btn mr-2">{{ __('Cancel') }}</button>
                    <button @click="submit" class="btn-primary">{{ __('Submit') }}</button>
                </div>
            </div>

        </div>
    </stack>

</template>

<script>
export default {

    props: {
        handle: String,
        errors: Object,
        mode: String,
        id: String,
        initialValues: Object,
    },

    data() {
        return {
            values: null,
            meta: null,
            blueprint: null,
            loading: false,
            saveKeyBinding: null,
        }
    },

    computed: {

        headerText() {
            return this.mode === 'edit'   
                ? __('Edit Entry')
                : __('Add Entry');
        },

    },

    mounted() {
        this.load();
        this.saveKeyBinding = this.$keys.bindGlobal(['mod+enter', 'mod+s'], e => {
            e.preventDefault();
            this.submit();
        });
    },

    destroyed() {
        this.saveKeyBinding.destroy();
    },

    methods: {

        load() {
            this.loading = true;
            const url = this.mode === 'edit'
                ? cp_url(`curated-collections/api/collections/${this.handle}/entries/${this.id}`)
                : cp_url(`curated-collections/api/collections/${this.handle}/entries/create?entry=${this.initialValues.entry[0]}`);
            this.$axios.get(url)
                .then(response => {
                    this.values = {...response.data.data.values, ...this.initialValues};
                    this.meta = response.data.data.meta;
                    this.blueprint = response.data.data.blueprint;
                    this.loading = false;
                }).catch(e => {
                    console.log(e);
                    this.$toast.error(__('Something went wrong'));
                });
        },

        submit() {
            this.$emit('submit', this.mode, this.values);
        },

        close(close) {
            close();
        },

    },

}
</script>
