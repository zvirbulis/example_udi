<template>
    <b-form-group
            :description="field.description"
            :horizontal="field.one_row === true"
            :label-cols="field.label_cols"
            class="handbook-form-select"
            :class="{'horizontal': field.one_row === true}"
            :label="getLabel()"
            :label-for="fieldId"
            :invalid-feedback="getErrorMessage()"
            :state="getStateField()"
            :data-hash="data_hash"
    >
        <div style="position: relative">
            <v-select
                    :id="fieldId"
                    :close-on-select="field.multiple !== true"
                    :multiple="field.multiple === true"
                    :class="{'multiple': field.multiple === true}"
                    :state="getStateField()"
                    :readonly="field.readonly"
                    :options="results"
                    @search="search"
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
                    <a v-if="option.url" class="selected v-select-item" :href="option.url">
                        {{ option.label }}
                    </a>
                    <span v-else class="selected v-select-item">
                        {{ option.label }}
                    </span>
                </template>
            </v-select>
            <div v-if="field.readonly" class="v-select-readonly">
                <div></div>
            </div>
            <div v-for="button in buttons">
                <handbook-link-button :initLink="button._link" :initTarget="button.target" :initTitle="button.title"  />
            </div>
        </div>
    </b-form-group>
</template>

<script type="text/babel">
    import hash from 'object-hash';
    import urlParse from 'url-parse';
    import queryString from 'query-string';
    import vSelect from 'vue-select';
    import mixins from './Mixins';
    import _u from 'jsAssets/utils/index';
    import HandbookLinkButton from "vueComponents/handbook/buttons/LinkButton";

    /**
     * Универсальная форма редактирования, выпадающий список (select) с автозаполнением (ajax)
     *
     * @vuedoc
     * @module vue:components/handbooks/forms/elements/Select
     * @exports vue:components/handbooks/forms/elements/Select
     */
    export default {
        name: "handbook-form-select-ajax",

        mixins: [mixins],

        components: {
            'handbook-link-button': HandbookLinkButton,
            'v-select': vSelect
        },

        data() {
            return {
                results: [],
                select_values: [],
                values: [],
                users: [],
                initMode: false,
                _link: '',
                parsedQuery: {},
                idFieldCode: '',
                labelFieldCode: '',
                buttons: [],
            }
        },

        computed: {
            fieldId() {
                return 'id_' + hash([this.field.code]);
            },
        },

        watch: {
            field: function() {
                this.prepareData();
            }
        },

        mounted() {
            this.prepareData();
        },

        methods: {
            prepareData() {
                let parsedQuery = {};
                let url = {};
                let _link = '';

                if(!_u.isEmpty(this.field.select_settings.autocomplete._link)) {
                    url = new urlParse(this.field.select_settings.autocomplete._link);
                    parsedQuery = queryString.parse(url.query);
                    _link = url.origin + url.pathname;
                    this.idFieldCode = this.field.select_settings.id;
                    this.labelFieldCode = this.field.select_settings.label;
                }

                parsedQuery.filter = _u.checkValue(parsedQuery.filter, '');
                this._link = _link;
                this.parsedQuery = parsedQuery;
                this.results = _.union([], this.field.select_values);
                if (_.isArray(this.field.select_settings.buttons)) {
                    this.buttons = this.field.select_settings.buttons;
                }
                else{
                    this.buttons = [];
                }
            },

            search(search, loading) {
                loading(true);
                this.getRepositories(search, loading, this);
            },

            getRepositories: _.debounce((search, loading, vm) => {
                let params = Object.assign({}, vm.parsedQuery);
                let searchFilter = vm.field.select_settings.label + '::^$' + search;
                params.filter = _u.isEmpty(params.filter) ? searchFilter : params.filter + ';' + searchFilter;
                _u.requestGet(vm._link, params, (response) => {
                    loading(false);
                    let results = _.union([], vm.field.select_values);
                    _.forEach(response.data.data, (item) => {
                        if (_u.getItemById(results, item[vm.idFieldCode]) === undefined)
                            results.push({
                                id: item[vm.idFieldCode],
                                label: item[vm.labelFieldCode]
                            });
                    });
                    vm.results = results;
                });
            }, 250),

            getSelectedValue() {
                if(this.field.multiple === true) {
                    let selectedValues = [];
                    if (_.isArray(this.value)) {
                        _.forEach(this.value, (itemId) => {
                            _.forEach(this.results, (item) => {
                                if (item.id == itemId) {
                                    if (_u.getItemById(selectedValues, item.id) === undefined) {
                                        selectedValues.push(item);
                                    }
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

                    if (!this.initMode) {
                        console.log('emit onInput multi', values, this.results);
                        this.$emit('input', values);
                    }

                } else {
                    let value = ''
                    if (_.isObject(event) && event.id !== undefined) {
                        value = event.id;
                    } else {
                        value = event === null ? '' : event;
                    }
                    console.log('emit onInput single', value);
                    this.$emit('input', value.toString());
                }
            },

            pushItem(list, item) {
                if (_u.getItemById(list, item.id) === undefined)
                    list.push(item);
            }

        }
    }

</script>
