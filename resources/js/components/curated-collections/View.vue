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
            :handle="handle"
            :breadcrumbUrl="breadcrumbUrl"
            :collections="collections"
            :entryBlueprint="entryBlueprint"
            :entryDefaults="entryDefaults"
            :entryMeta="entryMeta"
            :displayFormComputed="displayFormComputed"
            :activeStatus="activeStatus"
            :key="activeStatus" />

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
        }
    },

}
</script>