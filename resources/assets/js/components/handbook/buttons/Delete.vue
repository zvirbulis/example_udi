<template>
    <div class="action-button delete-button" :class="{inactive: !count > 0}" @click="clickHandler">
        <div>{{ title }}<sup v-if="count">({{ count }})</sup></div>
    </div>
</template>

<script type="text/babel">
    /**
     * Кнопка удаления данных
     *
     * @vuedoc
     * @module vue:components/buttons/Delete
     * @exports vue:components/buttons/Delete
     */
    export default {
        name: "delete-button",

        props: ['title', 'count'],

        methods: {
            clickHandler() {
                if (this.count > 0) this.$emit('delete');
            }
        }
    }
</script>
