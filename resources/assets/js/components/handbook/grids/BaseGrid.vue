<template>
    <div class="handbook-table">
        <table>
            <thead>
                <tr>
                    <td class="items-selector">
                        <b-form-checkbox :indeterminate="indeterminate" v-model="selectedAllItems" @change="toggleAllItems"/>
                    </td>

                    <th v-for="field in fields"
                        v-if="!columnIsHidden(field)"
                        :key="getHtmlId('field_code', field.code)"
                        :class="[(sort.field === field.code) ? 'sorted ' + sort.order: '', (field.sortable === true) ? 'sortable' : '']"
                        @click="toggleSort(field)"
                    >
                        {{ field.name }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, indexItem) in items"
                    :key="getHtmlId('item_id', getItemId(item))"
                    :class="{edited: open_edit_form && editedItemId === getItemId(item)}"
                >
                    <td class="item-selector">
                        <b-checkbox v-model="selectedItems[getItemId(item)]" @change="toggleItem"/>
                    </td>
                    <td v-for="field in fields"
                        v-if="!columnIsHidden(field)"
                        :key="getHtmlId('field_item', field.code + item.id)"
                        @click.stop="editItem(indexItem)">
                        {{ item[field.code] }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script type="text/babel">
    import confirmAction from 'vueComponents/ConfirmAction';

    /**
     * Универсальный справочник, сетка для отображения табличных данных
     *
     * @vuedoc
     * @module vue:components/handbook/grids/BaseGrid
     * @exports vue:components/handbook/grids/BaseGrid
     */
    export default {
        name: "handbook-base-grid",

        components: {
            'confirm-action': confirmAction,
        },

        /**
         * Входящие параметры
         *
         * @property {object} metadata - Метаданные схемы справочника (название, ...)
         * @property {array} fields - Список полей
         * @property {array} items - Список записей
         * @property {object} sort - Информация о сортировке записей
         * @property {boolean} open_edit_form - Признак того, что открыта форма редактирования
         */
        props: {
            metadata: {type: Object, default: {}},
            fields: {type: Array, default: []},
            items: {type: Array, default: []},
            sort: {type: Object, default: {}},
            open_edit_form: {type: Boolean, default: false},
        },

        /**
         * Данные компонента
         *
         * @method data
         * @property {boolean} indeterminate - Признак того, что отмечена часть или все записи на текущей странице
         * (см. {@link https://bootstrap-vue.js.org/docs/components/form-checkbox описание компонента `b-form-checkbox`})
         * @property {boolean} selectedAllItems - Признак того, что выбраны все записи (работает в тандеме с indeterminate)
         * @property {object} selectedItems - Список отмеченных записей
         * @property {numeric} editedItemId - Идетификатор редактируемой записи
         */
        data() {
            return {
                indeterminate: false,
                selectedAllItems: false,
                selectedItems: {},
                editedItemId: null,
            }
        },

        /**
         * Отслеживаемые данные
         *
         * @property {array} items - Список записей. На основании полученного списка записей мы обрабатываем данные
         * о выбранных эелемнтах
         */
        watch: {
            items: function(items) {
                let selectedItems = {};
                _.map(items, (item) => {
                    if (this.selectedItems[item.id] !== undefined) {
                        selectedItems[item.id] = true;
                    }
                });
                this.selectedItems = selectedItems;
                this.checkSelectedItems();
            }
        },
        methods: {
            /**
             * Получаем идентификатор записи (определяем по признаку primary_key)
             *
             * @method getItemId
             * @param {object} item - Запись в таблице
             * @returns {numeric} - Идентификатор записи
             */
            getItemId(item) {
                let result = 0;
                _.map(this.fields, (field) => {
                    if (field.primary_key === true) {
                        result = item[field.code];
                    }
                });
                return result;
            },

            /**
             * Редактируем запись
             *
             * @method editItem
             * @param {numeric} itemIndex - Индекс записи в общем списке
             */
            editItem(indexItem) {
                this.editedItemId = this.getItemId(this.items[indexItem]);
                /**
                 * Вызов метода `editItem` в {@link vue:containers/HandbookIndex родительском компоненте HandbookIndex}, который
                 * обрабатывает запрос на редактирование записи
                 *
                 * @event handbook/BaseGrid=>editItem
                 */
                this.$emit('editItem', this.items[indexItem]);
            },

            /**
             * Выделяем (или снимаем выделение) все записи
             *
             * @method toggleAllItems
             * @param {boolean} checked - Признак того выделены (или нет) все записи
             */
            toggleAllItems(checked) {
                let selectedItems = Object.assign({});
                if (checked) {
                    _.map(this.items, (item) => {
                        selectedItems[this.getItemId(item)] = true;
                    });
                }
                this.selectedItems = selectedItems;
                this.indeterminate = false;
                this.$emit('selectItems', this.getSelectedItemIds());
            },

            /**
             * Возвращаем список идентификаторов отмеченных записей
             *
             * @method getSelectedItemIds
             * @returns {array} - Список идентификаторов отмеченных записей
             */
            getSelectedItemIds() {
                let result = [];
                _.map(this.selectedItems, (state, itemId) => {
                    if (state === true) {
                        result.push(itemId);
                    }
                });
                return result;
            },

            /**
             * Отмечаем/снимаем выделение с записи
             *
             * @method toggleItem
             */
            toggleItem() {
                this.$nextTick(() => {
                    this.checkSelectedItems();

                    /**
                     * Вызов метода `selectItems` в {@link vue:containers/HandbookIndex родительском компоненте HandbookIndex}, который
                     * обрабатывает список отмеченных записей
                     *
                     * @event handbook/BaseGrid=>selectItems
                     */
                    this.$emit('selectItems', this.getSelectedItemIds());
                });
            },

            /**
             * Проверяем список идентификаторв выделнных записей со списокм записей и меняем вслучае необходимости
             * состояние глобального checkbox'а
             *
             * @method checkSelectedItems
             */
            checkSelectedItems() {
                let countBy = _.countBy(this.selectedItems, (value) => value);
                let totalCountItems = this.items.length;
                if (countBy[true] === totalCountItems) {
                    this.selectedAllItems = true;
                    this.indeterminate = false;
                } else if (countBy[true] !== undefined && countBy[true] < totalCountItems) {
                    this.selectedAllItems = false;
                    this.indeterminate = true;
                } else {
                    this.selectedAllItems = false;
                    this.indeterminate = false;
                }
            },

            /**
             * Меняем сортировку
             *
             * @method toggleSort
             */
            toggleSort(field) {
                if (field.sortable === true) {
                    let sort = Object.assign({}, this.sort);
                    if (field.code === this.sort.field) {
                        sort.order = sort.order === 'asc' ? 'desc' : 'asc';
                    } else {
                        sort.field = field.code;
                        sort.order = 'asc';
                    }

                    /**
                     * Вызов метода `changeSort` в {@link vue:containers/HandbookIndex родительском компоненте HandbookIndex}, который
                     * обрабатывает информацию о смене сортировке
                     *
                     * @event handbook/BaseGrid=>changeSort
                     */
                    this.$emit('changeSort', sort);
                }
            },

            /**
             * Возвращаем признак того что поле является скрытым или нет
             *
             * @method columnIsHidden
             * @param {object} field - Поле
             * @returns {boolean} - Признак того что поле является скрытым или нет
             */
            columnIsHidden(field) {
                return field.showed !== true;
            },
        }
    }
</script>
