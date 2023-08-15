<template>
    <button
        type="button"
        class="toggle-container"
        :class="{ 'on': value }"
        @click="toggle"
        :aria-pressed="stateLiteral"
        :aria-label="__('Toggle Button')"
    >
        <div class="toggle-slider">
            <div class="toggle-knob" tabindex="0" @keyup.prevent.space.enter="toggle" ref="knob" />
        </div>
    </button>
</template>

<script>

export default {

    props: {
        initialValue: {
            type: Boolean
        },
    },

    data() {
        return {
            value: this.initialValue,
        }
    },

    computed: {
        stateLiteral() {
            if (this.value) {
                return 'true';
            }

            return 'false';
        }
    },

    methods: {
        toggle() {
            this.value = ! this.value;
            if (this.value) {
                this.$emit('on', this.value);
            } else {
                this.$emit('off', this.value);
            }
        }
    }

}
</script>
