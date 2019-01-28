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
        <vue-editor
                @input="inputEventHandler"
                :value="value"
                :editorToolbar="customToolbar"
                useCustomImageHandler
                @imageAdded="imageAddHandler"
        ></vue-editor>
    </b-form-group>
</template>

<script type="text/babel">
    import mixins from './Mixins';
    import { VueEditor } from 'vue2-editor'

    /**
     * Универсальная форма редактирования, WYSWYG-редактор.
     *
     * @vuedoc
     * @module vue:components/handbooks/forms/elements/TextEditor
     * @exports vue:components/handbooks/forms/elements/TextEditor
     */
    export default {
        name: "handbook-form-text-editor",

        mixins: [mixins],

        components: {
            VueEditor
        },

        data() {
            return {
                customToolbar: [
                    [{"header": [false, 1, 2, 3, 4, 5, 6]}],
                    ["bold", "italic", "underline", "strike"],
                    [{"align": ""}, {"align": "center"}, {"align": "right"}, {"align": "justify"}],
                    ["blockquote", "code-block"/*, "image"*/], [{"list": "ordered"}, {"list": "bullet"}, {"list": "check"}],
                    [{"indent": "-1"}, {"indent": "+1"}], [{"color": []}, {"background": []}],
                    ["link"], ["clean"]
                ],
            };
        },

        methods: {
            inputEventHandler(value) {
                this.$emit('input', value);
            },

            imageAddHandler(file, Editor, cursorLocation) {
                let formData = new FormData();
                formData.append('files[0]', file)
                axios({
                    url: '/files',
                    method: 'POST',
                    data: formData
                })
                    .then((response) => {
                        let url = response.data.data[0].src;
                        Editor.insertEmbed(cursorLocation, 'image', url);
                    })
                    .catch((err) => {
                        console.log(err);
                    })
            }
        }
    }
</script>
