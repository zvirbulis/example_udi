<template>
    <b-form-group
            :description="field.description"
            class="handbook-form-textarea"
            :label="field.name"
            :label-for="field.code"
            :invalid-feedback="getErrorMessage()"
            :state="getStateField()"
            :data-hash="data_hash"
    >
        <b-form-textarea
                :id="field.code"
                :state="getStateField()"
                :readonly="field.readonly"
                :rows="field.rows ? field.rows : 3"
                v-bind:value="value"
                v-on:input="$emit('input', $event.toString())"
        ></b-form-textarea>
    </b-form-group>
</template>

<script type="text/babel">
    import mixins from './Mixins';

    /**
     * Универсальная форма редактирования, многострочное текстовое поле (textarea)
     *
     * @vuedoc
     * @module vue:components/handbooks/forms/elements/Textarea
     * @exports vue:components/handbooks/forms/elements/Textarea
     */
    export default {
        name: "handbook-form-textarea",
        mixins: [mixins]
    }
</script>
