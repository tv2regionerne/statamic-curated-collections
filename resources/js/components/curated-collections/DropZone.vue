<template>
  <div class="DropZone-container">
    <slot></slot>
    <draggable
      class="DropZone-overlay"
      tag="div"
      :group="{ name: 'view', put: true, pull: false }"
      v-if="dragOngoing">
      <div class="DropZone-overlay-inner">
        <svg v-if="over" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30.1 38.2" class="mb-4 h-8 fill-white">
          <path d="M4.1,16.2L6,34.6c0.2,2,1.9,3.6,4,3.6c2.9,0,7.6,0,10.6,0c2.1,0,3.8-1.6,4-3.6l1.9-18.4L4.1,16.2z M24.3,18.2 l-1.7,16.2c-0.1,1-1,1.8-2,1.8c-2.9,0-7.6,0-10.6,0c-1,0-1.9-0.8-2-1.8L6.3,18.2H24.3z M9.5,23.3l0.8,8c0.1,0.5,0.5,1,1.1,0.9 c0.5-0.1,1-0.5,0.9-1.1l-0.8-8c-0.1-0.6-0.5-1-1.1-0.9C9.9,22.3,9.5,22.7,9.5,23.3z M19.1,23.1l-0.8,8c-0.1,0.5,0.4,1,0.9,1.1 c0.5,0.1,1-0.4,1.1-0.9l0.8-8c0.1-0.5-0.4-1-0.9-1.1C19.6,22.1,19.1,22.6,19.1,23.1z M14.3,23.2v8c0,0.6,0.4,1,1,1s1-0.4,1-1v-8 c0-0.6-0.4-1-1-1C14.8,22.2,14.3,22.6,14.3,23.2z"/>
          <path d="M26.1,14.6c1,0.4,2.2-0.2,2.6-1.2l1-2.9c0.4-1-0.2-2.2-1.2-2.6l-5.6-2L23.2,5c0.2-0.5,0.1-1-0.1-1.6 c-0.2-0.5-0.7-0.9-1.1-1l-5.8-2c-0.4-0.3-1-0.2-1.5,0c-0.5,0.2-0.9,0.7-1,1.1l-0.4,1l-5.6-2c-1-0.4-2.2,0.2-2.6,1.2 c-0.3,0.8-0.7,1.9-1,2.9c-0.4,1,0.2,2.2,1.2,2.6L26.1,14.6z M15.6,2.3l5.6,2L21,5.3l-5.7-2.1L15.6,2.3z M6.1,5.3l1-2.9l20.7,7.3 l-1,2.9L6.1,5.3z"/>
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30.1 38.2" class="mb-4 h-8 fill-white">
          <path class="st0" d="M4.1,16.2L6,34.6c0.2,2,1.9,3.6,4,3.6c2.9,0,7.6,0,10.6,0c2.1,0,3.8-1.6,4-3.6l1.9-18.4L4.1,16.2z M24.3,18.2 l-1.7,16.2c-0.1,1-1,1.8-2,1.8c-2.9,0-7.6,0-10.6,0c-1,0-1.9-0.8-2-1.8L6.3,18.2H24.3z M9.5,23.3l0.8,8c0.1,0.5,0.5,1,1.1,0.9 c0.5-0.1,1-0.5,0.9-1.1l-0.8-8c-0.1-0.6-0.5-1-1.1-0.9C9.9,22.3,9.5,22.7,9.5,23.3z M19.1,23.1l-0.8,8c-0.1,0.5,0.4,1,0.9,1.1 c0.5,0.1,1-0.4,1.1-0.9l0.8-8c0.1-0.5-0.4-1-0.9-1.1C19.6,22.1,19.1,22.6,19.1,23.1z M14.3,23.2v8c0,0.6,0.4,1,1,1s1-0.4,1-1v-8 c0-0.6-0.4-1-1-1C14.8,22.2,14.3,22.6,14.3,23.2z"/>
          <path class="st0" d="M26.3,18.2c1.1,0,2-0.9,2-2v-3c0-1.1-0.9-2-2-2h-6v-1c0-0.5-0.2-1-0.6-1.4c-0.4-0.4-0.9-0.6-1.4-0.6h-6 c-0.5,0-1,0.2-1.4,0.6c-0.4,0.4-0.6,0.9-0.6,1.4v1h-6c-1.1,0-2,0.9-2,2c0,0.9,0,2.1,0,3c0,1.1,0.9,2,2,2H26.3z M12.3,10.2h6v1h-6 V10.2z M4.3,16.2v-3h22v3H4.3z"/>
        </svg>
        <p>Træk element her til for at fjerne det fra listen</p>
      </div>
    </draggable>
  </div>
</template>

<script>
  export default {
    props: [
      'group',
    ],
    data () {
      return {
        dragOngoing: false,
        over: false,
      }
    },
    mounted() {
      this.$events.$on('curated-list-drag-start', this.onDragStart);
      this.$events.$on('curated-list-drag-end', this.onDragEnd);
      this.$events.$on('curated-list-drag-move', this.onDragMove);
    },
    methods: {
      onDragStart(event, group) {
        if (this.group === group) {
          this.dragOngoing = true;
        }
      },
      onDragEnd(event, group) {
        if (this.group === group) {
          this.dragOngoing = false;
        }
        this.over = false;
      },
      onDragMove(event) {
        if (event.to.classList.contains('DropZone-overlay')) {
          this.over = true;
        }
      }
    }
  }
</script>

<style>
  .DropZone-container {
    position: relative;
  }
  .DropZone-overlay {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    border-radius: 6px;
    background-color: #529DF8CC;
  }
  .DropZone-overlay-inner {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
  }
  .DropZone-overlay tr {
    display: none;
  }
  .DropZone-overlay p {
    text-align: center;
    color: #fff;
    font-size: 1.2rem;
    font-weight: bold;
    margin: 0;
    padding: 0;
  }
  .fill-white path {
    fill: #fff;
  }
</style>