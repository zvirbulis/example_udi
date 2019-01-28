<template>
    <b-form-group :data-hash="data_hash">
        <b-form-checkbox
                :id="field.code"
                class="handbook-form-checkbox"
                :checked="value === '1' || value === 'Y'"
                v-on:input="$emit('input', $event === true ? '1' : '0')"
        >
            {{ field.name }}
        </b-form-checkbox>
    </b-form-group>
</template>

<script type="text/babel">
    import mixins from './Mixins';

    /**
     * Универсальная форма редактирования, поле триггер (флажок, галочка, называйте как хотите). Компонент работает с двумя
     * значениями v-model 0 или 1. Любое другое значение во входящий параметрах для value будет вызывать состояние `unchecked`
     *
     * @vuedoc
     * @module vue:components/handbooks/forms/elements/Checkbox
     * @exports vue:components/handbooks/forms/elements/Checkbox
     */
    export default {
        name: "handbook-form-checkbox",
        mixins: [mixins]
    }
</script>
