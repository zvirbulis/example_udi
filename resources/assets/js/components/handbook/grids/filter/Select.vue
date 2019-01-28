<template>
    <b-form-group
            :description="field.description"
            class="handbook-grid-filter-select"
            :label="field.name"
            :label-for="field.code"
            :class="{'horizontal': field.one_row === true}"
            :invalid-feedback="getErrorMessage()"
            :state="getStateField()"
    >
        <template v-if="isRangeMode">
            <div class="handbook-grid-filter-range-values">
                <div class="handbook-grid-filter-range-value-from">
                    <handbook-form-select v-model="internalValueFrom"
                                          :field="field"
                                          :label="'від'"
                    ></handbook-form-select>
                </div>
                <div class="handbook-grid-filter-range-value-to">
                    <handbook-form-select v-model="internalValueTo"
                                          :field="field"
                                          :label="'до'"
                    ></handbook-form-select>
                </div>
            </div>
        </template>
        <template v-else>
            <handbook-form-select v-model="internalValue" :field="field" :hide_label="true"></handbook-form-select>
        </template>
    </b-form-group>
</template>

<script type="text/babel">
    import mixins from 'vueComponents/handbook/forms/elements/Mixins';
    import elSelect from 'vueComponents/handbook/forms/elements/Select';
    import _u from 'jsAssets/utils/index';

    /**
     * Универсальная форма редактирования, текстовое поле (input). Тип данных может быть следующим:
     * `text`, `password`, `email`, `number`, `date`, `time`, `range` (color исключен и вынесен в
     * {@link vue:components/handbooks/forms/elements/SelectColor отдельный компонент}).
     * См. в сторону {@link https://bootstrap-vue.js.org/docs/components/form-input документации по bootstrap-vue}
     *
     * @vuedoc
     * @module vue:components/handbooks/grids/filter/Select
     * @exports vue:components/handbooks/grids/filter/Select
     */
    export default {
        name: "handbook-grid-filter-select",
        mixins: [mixins],
        components: {
            'handbook-form-select': elSelect
        },
        data() {
            return {
                internalValue: '',
                internalValueFrom: '',
                internalValueTo: '',
            }
        },

        computed: {
            isRangeMode: function() {
                return this.field.type_filter === 'range';
            }
        },

        watch: {
            field() {
                this.prepareData();
            },

            internalValueFrom() {
                this.$emit('input', this.getRangeValue());
            },

            internalValueTo() {
                this.$emit('input', this.getRangeValue());
            },

            internalValue() {
                this.$emit('input', String(_u.checkValue(this.internalValue, '')));
            }
        },

        mounted() {
            this.prepareData();
        },

        methods: {
            prepareData() {
                if (!this.isRangeMode) {
                    this.internalValue = String(this.field.value);
                } else {
                    if (_.isArray(this.field.value) && this.field.value.length == 2) {
                        this.internalValueFrom = String(this.field.value[0]);
                        this.internalValueTo = String(this.field.value[1]);
                    } else {
                        this.internalValueFrom = '';
                        this.internalValueTo = '';
                    }

                }
            },

            getRangeValue() {
                return String(_u.checkValue(this.internalValueFrom, '')) + '|' + String(_u.checkValue(this.internalValueTo, ''));
            }
        }
    }
</script>
