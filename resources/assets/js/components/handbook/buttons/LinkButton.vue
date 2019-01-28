<template>
    <span>
        <template v-if="isPopup">
            <div :id="uniqueId" class="handbook-link-popover">
                <span class="trigger-popover-edit-form">
                    <span class="anchor-popover-edit-form" id="popoverEditFormEvent">&nbsp;</span>
                    <a href="javascript:;" @click.prevent="toggleEditForm">{{ title }}</a>
                </span>

                <b-popover ref="popoverEditForm"
                           target="popoverEditFormEvent"
                           placement="bottomright"
                           :container="uniqueId"
                           :show="isShowedEditForm"
                           triggers="focus"
                >
                    <!-- грязный хак для b-popover /ak@/ -->
                    <span class="handbook-edit-form-b-popover-hack">.</span>
                    <handbook-popover-edit-form v-if="isShowedEditForm && schema"
                            :metadata="schema.forms.self.metadata"
                            :action_link="schema.forms.self._link"
                            :grid="schema.forms.self.grid"
                            :is_new="true"
                            v-on:close="closeEditForm"
                            v-on:save="saveItem"
                    ></handbook-popover-edit-form>

                    <loading :overlay="overlayLoading"
                             :show="showLoading"
                             :label="labelLoading">
                    </loading>

                </b-popover>

            </div>
        </template>
        <template v-else>
            <a :href="link" :target="target"> {{ title }}</a>
        </template>
    </span>
</template>

<script type="text/babel">
    import hash from 'object-hash';
    import loading from 'vue-full-loading';
    import _u from 'jsAssets/utils/index';
    import handbookPopoverEditForm from 'vueComponents/handbook/forms/PopoverEditForm';

    export default {
        name: "handbook-link-button",
        props: ['initLink', 'initTarget', 'initTitle'],

        components: {
            loading,
            'handbook-popover-edit-form': handbookPopoverEditForm,
        },

        data() {
            return {
                showLoading: false,
                labelLoading: '',
                link: this.initLink,
                target: this.initTarget,
                title: this.initTitle,
                node: null,
                isShowedEditForm: false,
                schema: null,
            }
        },

        mounted() {
            let target, node;
            [target, node] = this.target.split(':');
            if (target === 'popup') {
                this.target = target;
                this.node = node || null;
            }
        },

        computed: {
            isPopup() {
                return this.target === 'popup';
            },
            uniqueId() {
                return this.getHtmlId('popover', hash(this.title));
            }
        },

        methods: {
            toggleEditForm() {
                if (this.isShowedEditForm)
                    this.closeEditForm();
                else
                    this.openEditForm();
            },

            openEditForm() {
                let _this = this;
                //alert('Выводим форму из схемы');

                this.requestHttp('get', this.link, {}, () => {
                    this.isShowedEditForm = true;
                    this.$refs.popoverEditForm.$emit('open');
                });
            },

            closeEditForm() {
                this.isShowedEditForm = false;
                this.$refs.popoverEditForm.$emit('close');
            },

            /**
             * Сохраняем запись в справочнике
             *
             * @method saveItem
             * @param {object} params - Входящие параметры: isNew (новый), fields (список полей)
             */
            saveItem(params) {
                let schema = Object.assign({}, this.schema);

                _.map(params.fields, (field, fieldCode) => {
                    schema.forms.self.grid.fields[fieldCode].value = field.value;
                });

                let url = params.isNew ? this.schema._link : schema.forms.self._link;
                let typeRequest = params.isNew ? 'post' : 'put';
                let successMessage = params.isNew ? 'Запис створено' : 'Запис оновлено';

                if (!_.isEmpty(url)) {
                    this.requestHttp(typeRequest, url, schema, () => {
                        this.notifySuccess(successMessage);
                        this.closeEditForm();
                    });
                } else {
                    this.notifyError(_v.sprintf(
                        'Помилка в форматі даних. Не вказано url для %s запиту!', typeRequest
                    ));
                }
            },

            /**
             * Обработчик HTTP запросов
             *
             * @method requestHttp
             * @param {string} type - Тип HTTP запроса (get, put, post, delete)
             * @param {string} url - URL HTTP запроса
             * @param {object} params - Параметры запроса
             * @param {function} callback - Callback-функция для обработки успешного выполнения операции
             */
            requestHttp(type, url, params, callback) {
                let _this = this;
                _this.showLoading = true;
                _u.requestHttp(type, url, params, (response) => {
                    _this.showLoading = false;
                    if (response.status == 200) {
                        let data = response.data.data;
                        if (!_.isEmpty(data)) {
                            _this.schema = Object.assign({}, data);
                            if (_.isFunction(callback)) callback(data);
                        }
                    } else {
                        _this.notifyError('Сталася помилка: ' + response.message);
                    }
                }, (error) => {
                    _this.showLoading = false;

                    if (error.response) {
                        if (error.response.status === 400 && error.response.data.data.forms !== undefined) {
                            _this.schema = Object.assign({}, error.response.data.data);
                        } else {
                            _this.notifyError('Сталася помилка: ' + _u.getResponseError(error));
                        }
                    }
                });
            },

        }
    }
</script>
