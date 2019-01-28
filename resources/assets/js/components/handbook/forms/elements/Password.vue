<template>
    <b-form-group
            :description="field.description"
            class="handbook-form-input"
            :label="field.name"
            :label-for="field.code"
            :invalid-feedback="getErrorMessage()"
            :state="getStateField()"
    >
        <b-input-group>
            <b-form-input
                    :ref="field.code"
                    :id="field.code"
                    :type="getTypeField()"
                    :state="getStateField()"
                    :readonly="field.readonly"
                    v-bind:value="value"
                    v-on:input="$emit('input', $event.toString())"
            ></b-form-input>

            <b-input-group-append v-if="field.code == 'User.password'" class="button-generate-password" @click.stop="generatePassword">
                <b-btn variant="generate-password" title="Згенерувати пароль"></b-btn>
            </b-input-group-append>
        </b-input-group>
    </b-form-group>
</template>

<script type="text/babel">
    import mixins from './Mixins';
    import passwordGenerator from "generate-password";

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
        name: "handbook-form-password",
        mixins: [mixins],

        methods: {
            getTypeField() {
                return this.field.show_password === true ? 'text' : 'password';
            },

            generatePassword() {
                let password = passwordGenerator.generate({
                    length: 10,
                    numbers: true,
                    strict: true
                });

                this.$parent.fields['User.password']['show_password'] = true;
                this.$parent.fields['User.password']['value'] = password;
                this.$parent.fields['User.password_confirmation']['show_password'] = true;
                this.$parent.fields['User.password_confirmation']['value'] = password;
                this.$refs[this.field.code].focus();
            }
        }
    }
</script>
