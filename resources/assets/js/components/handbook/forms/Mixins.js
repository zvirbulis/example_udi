/**
 * "Примеси" для форм редактирования справочника
 * @namespace vue:components/handbooks/forms/Mixins
 */

import hash from 'object-hash';
import _u from 'jsAssets/utils/index';
import passwordGenerator from "generate-password";

export default {
    /**
     * Входящие параметры
     *
     * @property {object} grid - Информация о размещении полей в форме
     * @property {object} metadata - Метаданные формы (название, ...)
     * @property {boolean} readonly - Признак того, что форма доступна только в режиме для чтения
     * @property {boolean} is_parent - Признак того, что это родительская форма (только для чтения)
     * @property {boolean} is_new - Признак того, что создаётся новая запись
     */
    props: {
        grid: {type: Object, default: []},
        action_link: {type: String, default: ''},
        metadata: {type: Object, default: {}},
        readonly: {type: Boolean, default: false},
        is_parent: {type: Boolean, default: false},
        is_new: {type: Boolean, default: false},
    },

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
            fieldsHash: '',
            fields: {},
            groups: {},
            showRelations: {},
            isShowedConfirmAction: false,
            messageConfirmAction: '',
            actionConfirmAction: () => {},
            needCloseConfirmAction: false,
            expectedAnswerConfirmAction: '',
            questionConfirmAction: ''
        }
    },

    /**
     * Отслеживаемые данные
     *
     * @property {object} grid - Отслеживаем изменений в информации о размещении полей в форме и обновление данных
     * компонента
     */
    watch: {
        grid: function(newGrid, oldGrid) {
            if (hash(newGrid) !== hash(oldGrid)) {
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
            // Формируем список панелей или создаём одну общую
            let groups = [];
            if (_u.isEmpty(this.grid.groups)) {
                groups.push({
                    class_name: 'common-panel',
                    order: this.grid.order
                });
            } else {
                _.forEach(this.grid.groups, (group) => {
                    if (group.one_row === true) {
                        group.class_name = _u.checkValue(group.class_name, '');
                        group.class_name = group.class_name.concat(' edit-panel-table edit-panel-col-', group.order.length);
                    }
                    groups.push(group);
                });
            }

            this.groups = groups;

            // Формируем список полей проиндексированных по символьным кодам
            let fields = {};
            _.forEach(this.grid.order, (fieldCode) => {
                let field = this.grid.fields[fieldCode];
                fields[fieldCode] = Object.assign({}, field,
                    {
                        value: this.checkValue(field),
                        required: _u.checkValue(field.required, false),
                        class_name: _u.checkValue(field.class_name, '') + (field.required === true ? ' required' : ''),
                        readonly: this.readonly ? true : !!field.readonly
                    }
                );
            });
            this.fields = fields;
            this.$nextTick(() => {
                this.fieldsHash = hash(this.fields);
            });
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
            } else if (field.type_element === 'select' && field.value === null) {
                return null;
            } else {
                return _u.checkValue(field.value, '');
            }
        },

        /**
         * Закрываем форму
         *
         * @method close
         */
        clickOutside() {
            if (this.fieldsHash == hash(this.fields)) {
                this.close();
            } else {
                this.isShowedConfirmAction = true;
                this.messageConfirmAction = 'Ви внесли зміни, але забули їх зберегти. Бажаєте зберегти дані?';
                this.actionConfirmAction = () => { this.save(); };
                this.needCloseConfirmAction = true;
            }
        },

        /**
         * Закрываем форму
         *
         * @method close
         */
        close() {
            /**
             * Вызов метода `close` в {@link vue:containers/HandbookIndex родительском компоненте HandbookIndex},
             * который обрабатывает запрос на закрытие формы
             *
             * @event handbook/BaseEditFrom=>close
             */
            this.$emit('close');
        },


        /**
         * Сохраняем данные формы
         *
         * @method save
         */
        save() {
            let result = { isNew: this.isNew(), fields: this.fields };
            /**
             * Вызов метода `save` в {@link vue:containers/HandbookIndex родительском компоненте HandbookIndex},
             * который обрабатывает запрос на сохранение данных формы
             *
             * @event handbook/BaseEditFrom=>save
             */
            this.$emit('save', result);
        },

        /**
         * Закрываем родительскую форму в режиме только для чтения (ака слева) и возвращаемся на шаг назад в режим
         * редактирования экс-родительской формы
         *
         * @method closeParent
         */
        closeParent() {
            /**
             * Вызов метода `closeParent` в {@link vue:containers/HandbookIndex родительском компоненте HandbookIndex},
             * который обрабатывает запрос на возвращение на шаг назад (т.е. переходим в режим редактирования
             * родительской формы)
             *
             * @event handbook/BaseEditFrom=>closeParent
             */
            this.$emit('closeParent');
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
            if (action.type === 'frontend') {
                switch(action.code) {
                    case 'clone_item':
                        this.cloneItem(action);
                        break;
                    case 'delete_item':
                        this.deleteItem();
                        break;
                }
            } else {
                this.doAction(action);
            }
        },

        doAction(action, params, need_confirm) {
            params = _.extend({}, {action: action.code}, params || {});

            let _doAction = () => { this.$parent.getList(this.action_link, params, (schema) => {

                if(schema.in_progress){
                    this.$parent.overlayLoading = true;
                    this.continueAction(action, schema.progress, schema.params);
                }
                else{
                    this.$parent.labelLoading = '';
                    this.$parent.overlayLoading = false;
                    let actions = schema.forms.self.grid.actions;
                    let result = _.find(actions, {code: action.code});

                    if(result.success){
                        this.$notify({
                            type: 'success',
                            text: result.success,
                            duration: 5000,
                        });
                    }
                    else if(result.error){
                        this.notifyError(result.error);
                    }
                    // @todo решить, что делать в случае, если редирект не нужен.
                    if (action.redirect == true) {
                        if (result !== undefined) {
                            _u.redirect(result._link);
                        } else {
                            this.notifyError('Сталася помилка: невірний URL для дії в схемі');
                        }
                    }
                }
            })};


            if (action.need_confirm && need_confirm === undefined) {
                this.isShowedConfirmAction = true;
                this.messageConfirmAction = action.message_confirm;
                this.actionConfirmAction = () => { _doAction(); };
                this.needCloseConfirmAction = action.close_confirm ? action.close_confirm : false;
                this.expectedAnswerConfirmAction =  action.expected_answer_confirm ? action.expected_answer_confirm : '';
                this.questionConfirmAction =  action.question_confirm ? action.question_confirm : '';
            } else {
                _doAction();
            }

        },

        continueAction(action, progress, params){
            let process = (100 * progress.step / progress.total);
            this.$parent.labelLoading = 'Виконано: ' + process.toFixed(2) + '%';
            _.extend(params, progress);
            this.doAction(action, params, false);
        },

        deleteItem() {
            /**
             * Вызов метода `delete` в {@link vue:containers/HandbookIndex родительском компоненте HandbookIndex},
             * который обрабатывает запрос на сохранение данных формы
             *
             * @event handbook/BaseEditFrom=>delete
             */
            this.$emit('delete', this.getItemId());
        },

        cloneItem(action) {
            let fields = this.fields;
            if (action.unique_fields) {
                console.log('cloneItem', action, fields);
                let password = passwordGenerator.generate({
                    length: 10,
                    numbers: true,
                    strict: true
                });

                _.each(action.unique_fields, (fieldCode) => {
                    if (fields[fieldCode]) {
                        fields[fieldCode].value = 'NEW_' + password + '_!!!_' +fields[fieldCode].value;
                    }
                });
            }
            let result = { isNew: true, fields: fields };
            /**
             * Вызов метода `save` в {@link vue:containers/HandbookIndex родительском компоненте HandbookIndex},
             * который обрабатывает запрос на сохранение данных формы
             *
             * @event handbook/BaseEditFrom=>save
             */
            this.$emit('save', result);
        },

        /**
         * Скрываем диалог на подтвержедние удаления данных
         *
         * @method hideConfirmDeleteItems
         */
        hideConfirmAction() {
            this.isShowedConfirmAction = false;
            this.messageConfirmAction = '';
            this.actionConfirmAction = () => {};
            if (this.needCloseConfirmAction) {
                this.close();
            }
            this.needCloseConfirmAction = false;
            this.expectedAnswerConfirmAction = '';
            this.questionConfirmAction = '';
        },

        /**
         * Получаем значение первичного ключа (определяем по признаку primary_key)
         *
         * @method getItemId
         * @returns {numeric} - Значение первичного ключа
         */
        getItemId() {
            let result = 0;
            _.map(this.fields, (field) => {
                if (result === 0 && field.primary_key === true) {
                    result = field.value;
                }
            });
            return result;
        },

    }

}
