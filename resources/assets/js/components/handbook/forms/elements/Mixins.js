/**
 * "Примеси" для компонент формы редактирвоания справочника
 * @namespace vue:components/handbooks/forms/elements/Mixins
 */

export default {
    props: ['value', 'field', 'label', 'hide_label', 'data_hash'],

    methods: {
        /**
         * Возвращаем сообщение об ошибке (как правило ошибка валидации)
         *
         * @memberOf vue:components/handbooks/forms/elements/Mixins
         * @returns {string}
         */
        getErrorMessage() {
            let errors = this.field.errors;
            if (_.isEmpty(errors)) {
                return '';
            } else {
                return errors.join(' ');

            }
        },

        /**
         * Возвращаем состояние компонента: true -- всё в порядке, false -- ошибка. Для этого проверяем массив сообщений
         * об ошибках
         *
         * @memberOf vue:components/handbooks/forms/elements/Mixins
         * @returns {boolean}
         */
        getStateField() {
            return _.isEmpty(this.field.errors);
        },

        /**
         * Возвращаем подпись к компоненту
         *
         * @memberOf vue:components/handbooks/forms/elements/Mixins
         * @returns {string}
         */
        getLabel() {
            if (!this.hide_label) {
                let label = this.label ? this.label : this.field.name;
                return this.field.one_row === true ? label.concat(':') : label;
            } else {
                return '';
            }
        },

        /**
         * Возвращаем CSS класс компонента
         *
         * @memberOf vue:components/handbooks/forms/elements/Mixins
         * @returns {string}
         */
        getClassName() {
            return this.field.class_name ? this.field.class_name : '';
        },

        /**
         * Возвращаем значение поля (при возврате типа datetime-local изменяем формат даты)
         *
         * @memberOf vue:components/handbooks/forms/elements/Mixins
         * @returns {string}
         */
        getValue() {
            if(this.field.type_data && this.field.type_data === 'datetime-local' && this.value) {
                return this.value.replace(' ', 'T');
            }
            if(this.field.type_element && this.field.type_element === 'input' && this.value === 'null') {
                return '';
            }
            return this.value;
        },

    }
}
