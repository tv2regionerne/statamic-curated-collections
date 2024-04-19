import CuratedCollectionPopupFieldtype from './components/fieldtypes/CuratedCollectionPopup.vue'
import CuratedCollectionListing from './components/curated-collections/Listing.vue'
import CuratedCollectionView from './components/curated-collections/View.vue'
import CuratedCollectionEditForm from './components/curated-collections/EditForm.vue'
import CuratedCollectionCreateFrom from './components/curated-collections/CreateForm.vue'

Statamic.booting(() => {
    Statamic.component('curated_collection_popup-fieldtype', CuratedCollectionPopupFieldtype)
    Statamic.component('curated-collection-listing', CuratedCollectionListing)
    Statamic.component('curated-collection-view', CuratedCollectionView)
    Statamic.component('curated-collection-edit-form', CuratedCollectionEditForm)
    Statamic.component('curated-collection-create-form', CuratedCollectionCreateFrom)
})
