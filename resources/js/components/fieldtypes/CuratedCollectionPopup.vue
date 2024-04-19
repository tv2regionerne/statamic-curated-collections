<template>

    <div>

        <button
            class="btn w-full"
            :class="{ 'btn-primary': entrySaved, 'cursor-not-allowed': !entrySaved }"
            :disabled="!entrySaved"
            @click="openCollctions"
        >
            <span v-if="entrySaved">{{ __('statamic-curated-collections::fieldtype.display') }}</span>
            <span v-else>Gem først</span>
        </button>        

        <entries-panel
            v-if="showCollections"
            :entryId="entryId"
            :entryPublished="entryPublished"
            @closed="closeCollections"
        />
        
    </div>

</template>

<script>
import EntriesPanel from './EntriesPanel.vue';

export default {

    mixins: [
        Fieldtype
    ],

    inject: ['storeName'],

    components: {
        EntriesPanel,
    },
    
    data() {
        return {
            showCollections: false,
        };
    },

    computed: {

        store() {           
            return this.$store.state.publish[this.storeName];
        },
        
        entryId() {           
            return this.store.values.id;
        },
        
        entrySaved() {           
            return this.store.values.id;
        },
        
        entryPublished() {           
            return this.store.values.published;
        },

    },
    
    methods: {

        closeCollections() {
            this.showCollections = false;
        },

        openCollctions() {
            this.showCollections = true;
        },

    }

};
</script>
