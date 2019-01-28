<template>
    <div class="action-button create-button">
        <div>{{ title }}</div>
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
        name: "create-button",
        props: ['title'],
    }
</script>
