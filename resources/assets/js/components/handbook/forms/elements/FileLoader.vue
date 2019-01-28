<template>
    <b-form-group
            :description="field.description"
            class="handbook-form-textarea"
            :label="field.name"
            :label-for="field.code"
            :invalid-feedback="getErrorMessage()"
            :state="getStateField()"
    >
        <div class="handbook-form-file-loader">
            <file-loader :id="field.code" :select_values="select_values" :uploader="field.uploader.url" :multiple="field.multiple" @input="inputEventHandler" :value="value"></file-loader>
        </div>
    </b-form-group>
</template>

<script type="text/babel">
    import mixins from './Mixins';
    import fileLoaderComponent from 'vueComponents/FileLoader';
    /**
     * @TODO причесать код
     */
    export default {
        name: 'handbook-form-file-loader',
        components: {
            'file-loader': fileLoaderComponent
        },

        mixins: [mixins],

        props: ['field'],

        data(){
            return {

            }
        },

        computed: {
            select_values: function(){
                let fileSettings = _.invert(this.field.file_settings);
                let selectedValues = [];
                _.each(this.field.file_values, function(fieldValue, i){
                    selectedValues[i] = [];
                    _.each(fieldValue, function(value, field){
                        selectedValues[i][fileSettings[field]] = value;
                    });
                });

                return selectedValues;
            }
        },

        methods: {
            inputEventHandler(value) {
                this.$emit('input', value);
            }
        }
    }
</script>
<style>
    .handbook-form-file-loader button {
        margin-left: 10px;
        margin-top: 3px;
        border: 1px solid #29a2ff;
        border-radius: 3px;
        background-color: #29a2ff;
        color: white;
    }
</style>
