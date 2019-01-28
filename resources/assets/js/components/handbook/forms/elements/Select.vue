<template>
    <b-form-group
            :description="field.description"
            :horizontal="field.one_row === true"
            :label-cols="field.label_cols"
            class="handbook-form-select"
            :class="{'horizontal': field.one_row === true}"
            :label="getLabel()"
            :label-for="field.code"
            :invalid-feedback="getErrorMessage()"
            :state="getStateField()"
            :data-hash="data_hash"
    >
        <div>
            <v-select
                    :id="field.code"
                    :close-on-select="field.multiple !== true"
                    :multiple="field.multiple === true"
                    :class="{'multiple': field.multiple === true}"
                    :state="getStateField()"
                    :readonly="field.readonly"
                    :options="options"
                    :value="getSelectedValue()"
                    @input="onInput"
            >
                <template slot="no-options">
                    Дані відсутні
                </template>
                <template slot="option" slot-scope="option">
                    <span class="v-select-item">
                        {{ option.label }}
                    </span>
                </template>
                <template slot="selected-option" slot-scope="option">
                    <span class="selected v-select-item">
                        {{ option.label }}
                    </span>
                </template>
            </v-select>
            <div v-if="field.readonly" class="v-select-readonly">
                <div></div>
            </div>
        </div>
    </b-form-group>
</template>

<script type="text/babel">
    import vSelect from 'vue-select';
    import hash from 'object-hash';
    import mixins from './Mixins';

    /**
     * Универсальная форма редактирования, выпадающий список (select)
     *
     * @vuedoc
     * @module vue:components/handbooks/forms/elements/Select
     * @exports vue:components/handbooks/forms/elements/Select
     */
    export default {
        name: "handbook-form-select",

        mixins: [mixins],

        components: {
            'v-select': vSelect
        },

        data() {
            return {
                select_values: [],
                options: [],
                values: [],
                users: [],
                initMode: false
            }
        },

        watch: {
            field(newValue, oldValue) {
                if (hash(newValue) !== hash(oldValue)) {
                    this.prepareData();
                }
            }
        },

        mounted() {
            this.prepareData();
        },

        methods: {
            prepareData() {
                let options = this.field.select_values ? this.field.select_values : [];
                _.forEach(options, (item) => {
                    item.label = String(item.label);
                });
                this.options = options;
            },
            getSelectedValue() {
                if(this.field.multiple === true) {
                    let selectedValues = [];
                    if (_.isArray(this.value)) {
                        _.forEach(this.value, (itemId) => {
                            _.forEach(this.field.select_values, (item) => {
                                if (item.id == itemId) {
                                    selectedValues.push({
                                        'id': item.id, 'label': String(item.label)
                                    });
                                }
                            });
                        });
                    }

                    this.initMode = true;
                    this.$nextTick(() => {
                        this.initMode = false;
                    });
                    return selectedValues;
                } else {

                    let selectedValue = null;
                    _.forEach(this.field.select_values, (item) => {
                        if (item.id == this.value) {
                            selectedValue = item;
                        }
                    });
                    return selectedValue;
                }
            },

            onInput(event) {
                if (this.field.multiple === true) {
                    let values = []

                    if (_.isArray(event)) {
                        _.forEach(event, (item) => {
                            values.push(item.id);
                        });
                    }

                    if (!this.initMode)
                        this.$emit('input', values);
                } else {
                    let value = '';

                    if (_.isObject(event) && event.id !== undefined) {
                        value = event.id;
                    } else {
                        value = event === null ? '' : event;
                    }

                    /*
                        if (!_.isNull(value)) value = value.toString();
                    */
                    this.$emit('input', value);
                }
            }
        }
    }

</script>
