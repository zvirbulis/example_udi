<template>
	<div class="handbook-edit-form handbook-grid-settings"
         v-click-outside="close"
         @keyup.esc="close"
    >
        <div class="header-panel">
            <div>
                <div class="tools-buttons">
                    <input class="button-blue action-button border-bt"
                           type="button"
                           @click.stop="save"
                           value="Зберегти"
                    />
                    <input class="button-white action-button border-bt"
                           type="button"
                           @click.stop="close"
                           value="Відмінити"
                    />
                </div>
            </div>
        </div>

        <div class="body-panel">
            <div class="list-group-item header-list-fields">
                <div class="field-label">Cтовпчик</div>
                <div class="field-checkbox">Показувати</div>
            </div>

            <draggable class="list-group" field="ul" v-model="listFields" :options="dragOptions" :move="onMove" @start="isDragging=true" @end="isDragging=false">
                <transition-group type="transition" :name="'flip-listFields'">
                    <li class="list-group-item item-field" v-for="field in listFields" :key="field.order">
                        <div class="field-label">
                            {{ field.name }}
                        </div>
                        <div class="field-checkbox">
                            <b-form-checkbox class="handbook-form-checkbox" v-model="field.showed"></b-form-checkbox>
                        </div>
                    </li>
                </transition-group>
            </draggable>
        </div>

        <div class="footer-panel"></div>
	</div>
</template>

<script type="text/babel">
    import draggable from 'vuedraggable';
    import elCheckbox from 'vueComponents/handbook/forms/elements/Checkbox'; 

    /**
     * Универсальный справочник, настройка внешнего вида таблицы (какие поля отображать и в какой последовательности)
     *
     * @vuedoc
     * @module vue:components/handbooks/grids/Settings
     * @exports vue:components/handbooks/grids/Settings
     */
    export default {
        name: "handbook-grid-settings",

        components: {
            'handbook-form-checkbox': elCheckbox,
            draggable
        },

        /**
         * Входящие параметры
         *
         * @property {array} list_fields - Список полей
         */
        props: {
            list_fields: {type: Array, default: []},
        },

        /**
         * Данные компонента
         *
         * @method data
         * @property {array} listFields - Список полей
         * @property {boolean} isDragging - Признак того, что выполняется перетаскивание поля (за деталями обращаться к
         * {@link https://npmjs.com/package/vuedraggable `vuedraggable`})
         * @property {boolean} editable - Признак того, что список полей можно редактировать (за деталями обращаться к
         * {@link https://npmjs.com/package/vuedraggable `vuedraggable`})
         * @property {boolean} delayedDragging - Х/з зачем надо, просто копипастнул из примера )))) (за деталями обращаться к
         * {@link https://npmjs.com/package/vuedraggable `vuedraggable`})
         */
        data() {
            return {
                listFields: [],
                isDragging: false,
                editable: true,
                delayedDragging:false,
            }
        },

        /**
         * Отслеживаемые данные
         *
         * @property {boolean} isDraggins - Признак того, что выполняется перетаскивание поля
         */
        watch: {
            isDragging (newValue) {
                if (newValue){
                    this.delayedDragging = true;
                    return;
                }
                this.$nextTick( () => {
                    this.delayedDragging = false;
                })
            }
        },

        /**
         * Калькулируемые данные
         *
         * @property {object} dragOptions - Опции для компонента drag-and-drop
         */
        computed: {
            dragOptions () {
                return  {
                    animation: 0,
                    group: 'description',
                    disabled: !this.editable,
                    ghostClass: 'ghost'
                };
            },
        },

        /**
         * Событие вызываемое при монтирвоании компонента
         *
         * @method mounted
         */
        mounted() {
            this.prepareData();
        },

        methods: {
            /**
             * Обрабатываем входящие параметры и сохраняем результаты в this.listFields
             *
             * @method prepareData
             */
            prepareData() {
                this.listFields = this.list_fields.map( (field, index) => {
                    return Object.assign({ order: index + 1, fixed: false }, field);
                });
            },

            /**
             * Закрываем форму настроек
             *
             * @method close
             */
            close() {
                /**
                 * Вызов метода `closeGridSettings` в {@link vue:containers/HandbookIndex родительском компоненте HandbookIndex}, который
                 * обрабатывает запрос на закрытие настроек таблицы
                 *
                 * @event handbook/Settings=>closeGridSettings
                 */
                this.$emit('closeGridSettings');
            },

            /**
             * Сохраняем настройки
             *
             * @method save
             */
            save() {
                /**
                 * Вызов метода `saveGridSettings` в {@link vue:containers/HandbookIndex родительском компоненте HandbookIndex}, который
                 * обрабатывает запрос на изменение данных об отображаемых полях в таблице
                 *
                 * @event handbook/Settings=>saveGridSettings
                 */
                this.$emit('saveGridSettings', this.listFields);
            },

            /**
             * Обработчик события при котором происходит перетаскивание элемента в списке (за деталями обращаться к
             * {@link https://npmjs.com/package/vuedraggable `vuedraggable`})
             *
             * @method save
             */
            onMove ({relatedContext, draggedContext}) {
                const relatedfield = relatedContext.element;
                const draggedfield = draggedContext.element;
                return (!relatedfield || !relatedfield.fixed) && !draggedfield.fixed
            }
        }
    }
</script>

