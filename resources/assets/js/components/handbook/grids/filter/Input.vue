<template>
    <b-form-group
            class="handbook-grid-filter-input"
            :label="field.name"
            :label-for="field.code"
            :class="{'horizontal': field.one_row === true}"
            :invalid-feedback="getErrorMessage()"
            :state="getStateField()"
    >
        <template v-if="isRangeMode">
            <div class="handbook-grid-filter-range-values">
                <div class="handbook-grid-filter-range-value-from">
                    <handbook-form-input v-model="internalValueFrom"
                                          :field="field"
                                          :label="'від'"
                    ></handbook-form-input>
                </div>
                <div class="handbook-grid-filter-range-value-to">
                    <handbook-form-input v-model="internalValueTo"
                                          :field="field"
                                          :label="'до'"
                    ></handbook-form-input>
                </div>
            </div>
        </template>
        <template v-else>
            <handbook-form-input v-model="internalValue" :field="field" :hide_label="true"></handbook-form-input>
        </template>
    </b-form-group>
</template>

<script type="text/babel">
    import mixins from 'vueComponents/handbook/forms/elements/Mixins';
    import elInput from 'vueComponents/handbook/forms/elements/Input';
    import _u from 'jsAssets/utils/index';

    export default {
        name: "handbook-grid-filter-input",
        mixins: [mixins],
        components: {
            'handbook-form-input': elInput
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
