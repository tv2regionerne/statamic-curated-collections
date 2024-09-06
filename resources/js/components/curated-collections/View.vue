<template>

    <div class="curated-collections-view">

        <header class="mb-6">
            <breadcrumb :url="breadcrumbUrl" :title="__('Curated Collections')" />
            <div class="flex items-center">
                <h1 class="flex-1" v-text="title" />
                <dropdown-list class="mr-2" v-if="!!this.$scopedSlots.twirldown">
                    <slot name="twirldown" />
                </dropdown-list>
            </div>
        </header>

        <div class="tabs-container">
            <div class="publish-tabs tabs">
                <button class="tab-button"
                :class="{ 'active': activeStatus === 'published' }"
                    @click="activeStatus = 'published'"
                    v-text="__('Published')"
                />
                <button class="tab-button"
                :class="{ 'active': activeStatus === 'draft' }"
                    @click="activeStatus = 'draft'"
                    v-text="__('Draft')"
                />
            </div>
        </div>

        <view-tab
            ref="tab"
            :handle="handle"
            :breadcrumbUrl="breadcrumbUrl"
            :collections="collections"
            :entryBlueprint="entryBlueprint"
            :entryDefaults="entryDefaults"
            :entryMeta="entryMeta"
            :displayFormComputed="displayFormComputed"
            :activeStatus="activeStatus"
            :key="activeStatus" />

            <confirmation-modal
                v-if="isDisconnected"
                :cancellable="false"
                :title="__('Websocket Disconnected')"
                :buttonText="__('Reload')"
                @confirm="reload">
                {{ __('Websocket disconnected, please reload the page to resume live updates.') }}
            </confirmation-modal>

    </div>
</template>

<script>
import ViewTab from "./ViewTab.vue";

export default {

    components: {
        ViewTab,
    },

    props: {
        title: { type: String, required: true },
        handle: { type: String, required: true },
        breadcrumbUrl: { type: String, required: true },
        collections: { type: Array, required: true },
        entryBlueprint: { type: Array, required: true },
        entryDefaults: { type: Object, required: true },
        entryMeta: { type: Object, required: true },
        displayFormComputed: { type: Boolean, required: true },
    },

    data() {
        return {
            activeStatus: 'published',
            isConnected: false,
            isDisconnected: false,
        }
    },

    mounted() {
        Statamic.$echo.booted(() => {

            this.$echo
                .private(`curated-collections-private.${this.handle.toLowerCase()}`)
                .listen('.CuratedCollections.CuratedCollectionUpdated', event => this.curatedCollectionPushed(event));

            this.$echo.echo.connector.pusher.connection.bind('state_change', ({ current }) => {
                if (current === 'connected' && !this.isConnected) {
                    this.isConnected = true;
                } else if (current !== 'connected' && this.isConnected) {
                    this.isDisconnected = true;
                }
            });

        });
    },

    methods: {

        curatedCollectionPushed() {
            this.$refs.tab.request();
        },

        reload() {
            window.location.reload();
        },

    },

}
</script>