<template>
    <div class="handbook-edit-form handbook-grid-filter"
         v-click-outside="closeFilter"
         @keyup.esc="closeFilter"
    >
        <div class="header-panel">
            <div>
                <div class="tools-buttons">
                    <input class="button-blue action-button border-bt"
                           type="button"
                           @click.stop="setFilter"
                           value="Встановити"
                    />
                    <input class="button-white action-button border-bt"
                           type="button"
                           @click.stop="resetFilter"
                           value="Скинути"
                    />
                </div>
                <div class="close">
                    <div class="close-bt" @click.stop="closeFilter"></div>
                </div>
            </div>
        </div>

        <!--
        <div class="tools-panel"></div>
        -->

        <div class="body-panel">

            <div class="edit-form-panel">
                <div>
                    <div v-for="fieldCode in filter.order" class="handbook-form-element">
                        <template v-if="fields[fieldCode] && (fields[fieldCode].type_element === 'input' || fields[fieldCode].type_element === 'textarea')">
                            <handbook-form-input v-model="fields[fieldCode].value" :field="fields[fieldCode]"></handbook-form-input>
                        </template>
                        <template v-if="fields[fieldCode] && fields[fieldCode].type_element === 'select'">
                            <handbook-form-select v-if="!isSelectAjaxField(fields[fieldCode])" v-model="fields[fieldCode].value" :field="fields[fieldCode]"></handbook-form-select>
                            <handbook-form-select-ajax v-else v-model="fields[fieldCode].value" :field="fields[fieldCode]"></handbook-form-select-ajax>
                        </template>
                        <template v-if="fields[fieldCode] && fields[fieldCode].type_element === 'select_color'">
                            <handbook-form-select-color v-model="fields[fieldCode].value" :field="fields[fieldCode]"></handbook-form-select-color>
                        </template>
                    </div>
                </div>
            </div>

        </div>

        <div class="footer-panel"></div>
    </div>
</template>

<script type="text/babel">
    import hash from 'object-hash';
    import _u from 'jsAssets/utils/index';
    import elInput from 'vueComponents/handbook/grids/filter/Input';
    import elSelect from 'vueComponents/handbook/grids/filter/Select';
    import elSelectAjax from 'vueComponents/handbook/forms/elements/SelectAjax';
    import elSelectColor from 'vueComponents/handbook/forms/elements/SelectColor'; 

    /**
     * Универсальный справочник, форма для выбора условий фильтрации
     *
     * @vuedoc
     * @module vue:components/handbooks/grids/Filter
     * @exports vue:components/handbooks/grids/Filter
     */
    export default {
        name: "handbook-grid-filter",

        components: {
            'handbook-form-input': elInput,
            'handbook-form-select': elSelect,
            'handbook-form-select-ajax': elSelectAjax,
            'handbook-form-select-color': elSelectColor,
        },
        /**
         * Входящие параметры
         *
         * @property {object} fields - Список полей для фильтрации проиндексированых по символьному коду поля
         * @property {array} order - Орядок расположения полей
         */
        props: ['filter'],

        /**
         * Данные компонента
         *
         * @method data
         * @property {object} fields - Список полей
         * @property {object} groups - Описание того как сгруппированы поля
         * @property {object} showRelations - Список зависимостей (ссылки на дочерние формы)
         */
        data() {
            return {
                fields: {}
            }
        },

        /**
         * Отслеживаемые данные
         *
         * @property {object} grid - Отслеживаем изменений в информации о размещении полей в форме и обновление данных
         * компонента
         */
        watch: {
            filter: function (newFilter, oldFilter) {
                if (hash(newFilter) !== hash(oldFilter)) {
                    this.initData();
                }
            }
        },

        /**
         * Событие вызываемое при монтирвоании компонента
         *
         * @method mounted
         */
        mounted() {
            this.initData();
        },

        methods: {
            /**
             * Обработка входящих параметров
             *
             * @method initData
             */
            initData() {
                let fields = {};
                _.forEach(this.filter.order, (fieldCode) => {
                    if (this.filter.fields[fieldCode] !== undefined) {
                        fields[fieldCode] = this.filter.fields[fieldCode];
                    }
                });
                this.fields = fields;
            },

            /**
             * Проверка значения поля
             *
             * @method checkValue
             * @param {object} field - Поле
             * @returns {any} - Значение поля
             */
            checkValue(field) {
                if (field.type_element === 'checkbox') {
                    return field.value === 'Y' || field.value === 1 ? '1' : '0';
                } else {
                    return _u.checkValue(field.value, '');
                }
            },

            /**
             * Закрываем форму
             *
             * @method close
             */
            closeFilter() {
                /**
                 * Вызов метода `close` в {@link vue:containers/HandbookIndex родительском компоненте HandbookIndex},
                 * который обрабатывает запрос на закрытие формы
                 *
                 * @event handbook/BaseEditFrom=>close
                 */
                this.$emit('closeGridFilter');
            },

            /**
             * Закрываем форму
             *
             * @method close
             */
            resetFilter() {
                /**
                 * Вызов метода `close` в {@link vue:containers/HandbookIndex родительском компоненте HandbookIndex},
                 * который обрабатывает запрос на закрытие формы
                 *
                 * @event handbook/BaseEditFrom=>close
                 */
                this.$emit('resetGridFilter');
            },

            /**
             * Сохраняем данные формы
             *
             * @method save
             */
            setFilter() {
                /**
                 * Вызов метода `save` в {@link vue:containers/HandbookIndex родительском компоненте HandbookIndex},
                 * который обрабатывает запрос на сохранение данных формы
                 *
                 * @event handbook/grid/Filter=>setFilter
                 */
                this.$emit('setGridFilter', this.fields);
            },

            /**
             * Признак того что создаётся новая запись
             *
             * @method isNew
             */
            isNew() {
                return this.is_new;
            },

            isSelectAjaxField(field) {
                return field.select_settings !== undefined && field.select_settings.autocomplete !== undefined;
            },

            clickAction(actionIndex) {
                let action = this.grid.actions[actionIndex];
                this.$parent.getList(this.action_link, {action: action.code}, (schema) => {
                    // @todo решить, что делать в случае, если редирект не нужен
                    if (action.redirect == true) {
                        let actions = schema.forms.self.grid.actions;
                        let result = _.find(actions, {code: action.code});
                        if (result !== undefined) {
                            _u.redirect(result._link);
                        } else {
                            this.notifyError('Сталася помилка: невірний URL для дії в схемі');
                        }
                    }
                });
            },

        }
    }
</script>
