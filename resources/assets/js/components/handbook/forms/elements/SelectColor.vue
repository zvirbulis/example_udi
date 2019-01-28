<template>
    <b-form-group
            :description="field.description"
            :class="getClassName()"
            class="handbook-form-select-color"
            :label="field.name"
            :label-for="field.code"
            :invalid-feedback="getErrorMessage()"
            :state="getStateField()"
            :data-hash="data_hash"
    >

        <div class="selected-color">
            <ul class="handbook-field-colorpicker">
                <li v-for="color in listColors"
                    :key="color"
                    class="handbook-field-colorpicker-color-item"
                    :class="{active: color == value}"
                    :style="{'background-color': color}"
                    @click="selectColor(color)"
                >
                    <span class="handbook-field-colorpicker-color">
                        <div></div>
                    </span>
                </li>
                <li class="handbook-field-select-other-color" v-if="field.hide_others_colors !== true">
                    <a @click="isShowedColorPicker = !isShowedColorPicker">інший колір</a>
                    <div class="color-picker" v-if="isShowedColorPicker" v-click-outside="closeColorPicker">
                        <sketch-picker v-model="customColor" />
                    </div>
                </li>
            </ul>
        </div>

    </b-form-group>
</template>

<script type="text/babel">
    import { Sketch } from 'vue-color'
    import mixins from './Mixins';

    const presetColors = [
        '#fecfcf', '#fce4cb', '#fbf0cd', '#d4f0ce', '#cbeafa', '#f1dbf5'
    ]

    /**
     * Универсальная форма редактирования, компонент для выбора цвета. По умолчанию выводится 7 цветов (см. в сторону
     * presetColors). Можно передать свой набор в preset_colors. Проверки на ограничение кол-ва предустановленных
     * цветов нет. Кроме предустановленных цветов можно выбрать свой собственный цвет. Если выбор будет не нужен,
     * просто скройте при помощи css
     *
     * @vuedoc
     * @module vue:components/handbooks/forms/elements/SelectColor
     * @exports vue:components/handbooks/forms/elements/SelectColor
     */
    export default {
        name: "handbook-form-select-color",

        components: { "sketch-picker": Sketch },

        props: ['preset_colors'],

        mixins: [mixins],

        data() {
            return {
                listColors: [],
                isShowedColorPicker: false,
                customColor: {},
            }
        },

        mounted() {
            this.prepareData();
        },

        watch: {
            value() {
                this.prepareData();
            },
            customColor() {
                this.selectColor(this.customColor.hex);
            }
        },
        methods: {
            prepareData() {
                let listColors = this.preset_colors ? this.preset_colors : presetColors;
                if (_.findIndex(listColors, (value) => { return value == this.value }) === -1 && !_.isEmpty(this.value))
                    listColors = _.union(listColors, [this.value]);
                this.listColors = listColors;
            },
            selectColor(color) {
                color = this.value === color ? '' : color;
                this.$emit('input', color);
            },
            closeColorPicker() {
                this.isShowedColorPicker = false;
            }
        }
    }
</script>
