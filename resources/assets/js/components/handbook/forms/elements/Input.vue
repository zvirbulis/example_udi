<template>
    <b-form-group
            :description="field.description"
            class="handbook-form-input"
            :horizontal="field.one_row === true"
            :label="getLabel()"
            :label-for="field.code"
            :invalid-feedback="getErrorMessage()"
            :state="getStateField()"
            :data-hash="data_hash"
    >
        <b-form-input
                :id="field.code"
                :type="field.type_data"
                :state="getStateField()"
                :readonly="field.readonly"
                v-bind:value="getValue()"
                v-on:input="$emit('input', $event.toString())"
        ></b-form-input>
    </b-form-group>
</template>

<script type="text/babel">
    import mixins from './Mixins';

    /**
     * Универсальная форма редактирования, текстовое поле (input). Тип данных может быть следующим:
     * `text`, `password`, `email`, `number`, `date`, `time`, `range` (color исключен и вынесен в
     * {@link vue:components/handbooks/forms/elements/SelectColor отдельный компонент}).
     * См. в сторону {@link https://bootstrap-vue.js.org/docs/components/form-input документации по bootstrap-vue}
     *
     * @vuedoc
     * @module vue:components/handbooks/forms/elements/Input
     * @exports vue:components/handbooks/forms/elements/Input
     */
    export default {
        name: "handbook-form-input",
        mixins: [mixins],
    }


</script>
