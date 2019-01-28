<template>
    <div class="handbook" >
        <main>
            <div class="container" v-if="schema.list.metadata">

                <div class="handbook-header clearfix">
                    <div class="handbook-header-info">
                        <div class="special-button categories-button" v-if="schema.list.categories.items.length > 0" @click="isShowedCategories = !isShowedCategories"></div>
                        <div class="header-title">{{ schema.list.metadata.title }} {{ totalItems }}</div>
                    </div>
                    <div class="handbook-header-tools-box">
                        <template v-if="canCreateNewItem">
                            <create-button
                                    :title="'Створити новий'"
                                    :count="this.selectedItems.length"
                                    @click.native="createItem"
                            ></create-button>
                            <delete-button
                                    :title="'Видалити'"
                                    :count="selectedItems.length"
                                    @delete="confirmDeleteItems"
                            ></delete-button>
                        </template>
                        <div v-if="schema.list.filter"
                             class="special-button filter-button"
                             :class="{filtered: isFiltered}"
                             @click="openGridFilter"
                        ></div>
                        <div class="special-button settings-button" @click="openGridSettings"></div>
                        <b-dropdown v-if="schema.list.actions && schema.list.actions.length > 0"
                                    variant="link" size="lg" no-caret right
                                    class="handbook-edit-form-actions"
                        >
                            <template slot="button-content">
                                <div class="special-button more-menu">
                                    <a><span></span><span></span><span></span></a>
                                </div>
                            </template>

                            <b-dropdown-item v-for="(action, actionIndex) in schema.list.actions"
                                             :href="action.link"
                                             :key="action.code"
                                             :disabled="!isSelectedItems && !(action.active == true)"
                                             @click="clickAction(actionIndex)"
                            >
                                {{ action.name }}
                            </b-dropdown-item>

                        </b-dropdown>
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="handbook-body" :class="{'handbook-categories-is-open': isShowedCategories && schema.list.categories.items.length > 0}">
                    <div>
                        <handbook-edit-form v-if="schema.forms.parent"
                            :metadata="schema.forms.parent.metadata"
                            :grid="schema.forms.parent.grid"
                            :is_parent="true"
                            @closeParent="closeParentForm"
                        ></handbook-edit-form>
                    </div>
                    <handbook-categories v-if="isShowedCategories && schema.list.categories.items.length > 0"
                                         :categories="schema.list.categories"
                                         :selected_category="selectedCategory"
                                         @selectCategory="selectCategory"
                    ></handbook-categories>
                    <div v-if="schema" class="handbook-grid">
                        <handbook-grid
                            :metadata="schema.list.metadata"
                            :fields="listFields"
                            :items="schema.list.items"
                            :sort="schema.list.sort"
                            :open_edit_form="isShowedEditForm"
                            @editItem="editItem"
                            @deleteItems="deleteItems"
                            @changeSort="changeSort"
                            @selectItems="selectItems"
                        ></handbook-grid>
                        <handbook-pagination
                            :pagination="schema.list.pagination"
                            @changePage="changePage"
                        ></handbook-pagination>
                    </div>
                </div>

                <handbook-grid-filter v-if="isShowedGridFilter && schema.list.filter"
                                      :filter="schema.list.filter"
                                      @closeGridFilter="closeGridFilter"
                                      @setGridFilter="setGridFilter"
                                      @resetGridFilter="resetGridFilter"
                ></handbook-grid-filter>

                <handbook-grid-settings v-if="isShowedGridSettings"
                                        :list_fields="listFields"
                                        :order_fields="schema.list.fields"
                                        v-on:closeGridSettings="closeGridSettings"
                                        v-on:saveGridSettings="saveGridSettings"
                ></handbook-grid-settings>

                <handbook-edit-form v-if="isShowedEditForm"
                                    :metadata="schema.forms.self.metadata"
                                    :action_link="schema.forms.self._link"
                                    :grid="schema.forms.self.grid"
                                    :is_new="isNewRecord"
                                    :readonly="schema.forms.self.readonly"
                                    v-on:close="closeEditForm"
                                    v-on:save="saveItem"
                                    v-on:delete="deleteItem"
                ></handbook-edit-form>
                <div class="clearfix"></div>
            </div>
        </main>

        <confirm-action v-if="isShowedConfirmDelete"
                        :title="'Підтвердження видалення'"
                        :message="'Видалити записи з довідника?'"
                        :question="'Для видалення введіть \'так\''"
                        :expected_answer="'так'"
                        v-on:action="deleteItems"
                        v-on:hide="hideConfirmDeleteItems"
        ></confirm-action>

        <loading :overlay="overlayLoading"
                 :show="showLoading"
                 :label="labelLoading">
        </loading>
    </div>
</template>

<script type="text/babel">
    import _v from 'voca';
    import urlParse from 'url-parse';
    import queryString from 'query-string';
    import localStorage from 'store';
    import loading from 'vue-full-loading';
    import _u from 'jsAssets/utils/index';
    import handbookEditForm from 'vueComponents/handbook/forms/BaseEditForm';
    import handbookGrid from 'vueComponents/handbook/grids/BaseGrid';
    import handbookGridSettings from 'vueComponents/handbook/grids/Settings';
    import handbookGridFilter from 'vueComponents/handbook/grids/Filter';
    import handbookCategories from 'vueComponents/handbook/grids/Categories';
    import handbookPagination from 'vueComponents/handbook/grids/Pagination';
    import deleteButton from 'vueComponents/handbook/buttons/Delete';
    import createButton from 'vueComponents/handbook/buttons/Create';
    import confirmAction from 'vueComponents/ConfirmAction';

    /**
     * Универсальный справочник, контейнер для работы со справочником. По возможности вся логика работы с бакэндом
     * вынесена в него.
     *
     * @vuedoc
     * @module vue:containers/HandbookIndex
     * @exports vue:containers/HandbookIndex
     */
    export default {
        name: "handbook-index",

        components:{
            loading,
            'handbook-edit-form': handbookEditForm,
            'handbook-grid': handbookGrid,
            'handbook-grid-settings': handbookGridSettings,
            'handbook-grid-filter': handbookGridFilter,
            'handbook-pagination': handbookPagination,
            'handbook-categories': handbookCategories,
            'delete-button': deleteButton,
            'create-button': createButton,
            'confirm-action': confirmAction,
        },

        /**
         * Входящие параметры
         *
         * @property {string} init_url - URL по которому запрашивается схема при загрузке страницы
         */
        props: {
            init_url: {type: String, default: ''},
        },


        /**
         * Данные компонента
         *
         * @method data
         * @property {string} showLoading - Статус отображения индикатора загрузки
         * @property {string} labelLoading - Текстовое сообщение индикатора загрузки
         * @property {object} schema - Схема справочника
         * @property {object} selectedItems - Отмеченные записи
         * @property {object} selectedCategory - Выбранная категория
         * @property {boolean} isShowedConfirmDelete - Отображать (или нет) диалог подтверждения удаления данных
         * @property {boolean} isShowedEditForm - Отображать (или нет) форму редактирования
         * @property {boolean} isShowedGridSettings - Отображать (или нет) форму настройки внешнего вида таблицы
         * @property {boolean} isNewRecord - Признак того, что создаётся новая запись
         * @property {boolean} isShowedCategories - Признак того, что отображаются/скрыты категории
         * @property {array} listCategories - Список содержащих информацию о том в какой последвательности
         * отображать или нет поля в таблице
         */
        data() {
            return {
                showLoading: false,
                labelLoading: '',
                schema: {
                    list: {},
                    forms: {},
                    url_actions: {}
                },
                selectedItems: [],
                selectedCategory: {},
                isShowedConfirmDelete: false,
                isShowedEditForm: false,
                isShowedGridSettings: false,
                isShowedGridFilter: false,
                isNewRecord: false,
                isShowedCategories: true,
                listFields: [],
            }
        },

        computed: {
            totalItems() {
                return this.schema.list.pagination ? ' (' + this.schema.list.pagination.total_items + ')' : '';
            },
            canCreateNewItem() {
                return this.schema.forms && this.schema.forms.self && !this.schema.forms.self.readonly
            },
            isSelectedItems() {
                return this.selectedItems ? this.selectedItems.length > 0 : false;
            },
            isFiltered () {
                let result = false;

                if (this.schema.list.filter !== undefined) {
                    _.forEach(this.schema.list.filter.fields, (field) => {
                        if (field.value !== undefined && field.value !== '' && field.value !== 'null' && field.value !== null) {
                            result = true;
                        }
                    });
                }

                return result;
            },
            schemaCode() {
                let url = new urlParse(window.location);
                return _.last(url.pathname.split('/'));
            }
        },

        /**
         * Инициализируем данные при монтировании компонента. Получаем схему. Если необходимо открываем форму на
         * редактирование, инициализируем хлебные крошки через общее хранилищие приложения
         *
         * @method mounted
         */
        mounted() {
            let params = {};
            
            this.getList(this.init_url, params, () => {
                if (this.init_url !== this.schema._link) {
                    this.$nextTick(() => {
                        _u.setLocation(this.schema._link);

                        if (!_u.isEmpty(this.schema.forms.self._link)) {
                            this.openEditForm(false);
                        }
                    });
                }

                let breadcrumbsItems = [];

                if (this.schema.forms.parent)
                    breadcrumbsItems.push({
                        name: this.schema.forms.parent.metadata.title,
                        url: this.schema.forms.parent._link
                    });

                breadcrumbsItems.push({
                        name: this.schema.list.metadata.title,
                        url: this.schema._link
                });

                this.$store.commit('addAppPageBreadcrumbsItems', breadcrumbsItems);

                this.prepareSchema();
            });
        },

        methods: {

            prepareResponseData(data) {
                if (data.list.filter) {
                    _.each(data.list.filter.fields, (field) => {
                        field.value = field.value === null ? '' : field.value;
                    });
                }

                this.schema = Object.assign({}, data);
            },

            /**
             * Обрабатываем полученные данные. Извлекаем список полей из хранилища веб-браузера (времеенное решение,
             * в дальнейшем эти данные будут храниться на бакэнде) и формируем список полей (this.listFields), который
             * содержит порядок и признак того отображать поле или нет
             *
             * @method prepareData
             */
            prepareSchema() {
                let listFields = this.schema.list.fields.map( (field) => {
                    return Object.assign({ showed: field.default_hidden !== true }, field);
                });

                let savedListFields = localStorage.get('udi_fields_' + this.schemaCode);

                if (savedListFields === undefined || savedListFields.length != listFields.length) {
                    this.listFields = listFields;
                } else {
                    this.listFields = savedListFields;
                }
            },

            /**
             * Генерируем название кнопки для удаления записей в справочнике
             *
             * @method getTitleDeleteButton
             * @returns {string}
             */
            getTitleDeleteButton() {
                return _v.sprintf(
                    'Видалити (%s)', this.selectedItems.length
                );

            },

            /**
             * Обработчик события `selectItems` -- сохраняет в данных компонента список выделенных записей
             *
             * @method selectItems
             * @param {object} selectedItems - Список идентификаторов выбранных записей
             */
            selectItems(selectedItems) {
                this.selectedItems = selectedItems;
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
                        if(data.in_progress){
                            if (_.isFunction(callback)) callback(data);
                        }
                        else{
                            if (!_.isEmpty(data)) {
                                _this.prepareResponseData(data);
                            }
                            if (_.isFunction(callback)) callback(data);

                            this.prepareSchema();
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

            /**
             * Получаем список записей справочника на первом хите или при создании новой записи
             *
             * @method getList
             * @param {string} url - Адрес HTTP запроса
             * @param {object} params - Параметры GET запроса
             * @param {function} callback - Callback-функция для обработки успешного выполнения операции
             */
            getList(url, params, callback) {
                params = Object.assign(
                    _u.checkValue(params, { }),
                    { timestamp: Date.now() }
                );

                let savedSort = localStorage.get('udi_sort_' + this.schemaCode);
                if (!params.field && !_.isEmpty(savedSort) && savedSort.field) {
                    params.field = savedSort.field;
                    params.order = savedSort.order;
                }

                let savedPagination = localStorage.get('udi_pagination_' + this.schemaCode);

                if (!params.per_page && !_.isEmpty(savedPagination) && savedPagination.per_page) {
                    params.per_page = savedPagination.per_page;
                }

                this.requestHttp('get', url, this.getDefaultParams(params), callback);
            },

            /**
             * Получаем запись из справочника
             *
             * @method getItem
             * @param {string} url - URL HTTP запроса
             * @param {function} callback - Callback-функция для обработки успешного выполнения операции
             */
            getItem(url, callback) {
                this.requestHttp('get', url, this.getDefaultParams(), callback);
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
             * Создаём новую запись в справчонике
             *
             * @method createItem
             */
            createItem() {
                this.getList(this.schema._link, {}, () => {
                    this.openEditForm(true);
                })
            },

            /**
             * Редактируем запись в справочнике
             *
             * @method editItem
             * @param {object} item - Объект содержащий информацию о записи
             */
            editItem(item) {
                if (!_.isEmpty(item._link)) {
                    this.getItem(item._link, ()=> {
                        this.openEditForm(false);
                    });
                } else {
                    this.notifyError('Помилка в форматі даних. Не вказано url для get запиту!');
                }
            },

            /**
             * Удаляем записи
             *
             * @method deleteItems
             * @param {array} itemIds - Список идентификаторов записей для удаления
             */
            deleteItems() {
                this.requestHttp('delete', this.schema._link, this.getDefaultParams({item_ids: this.selectedItems}), () => {
                    this.selectedItems = {};
                    if (this.isShowedEditForm) {
                        this.closeEditForm();
                    }
                });
            },

            /**
             * Удаляем запись
             *
             * @method deleteItems
             * @param {array} itemId - Идентификатор записи для удаления
             */
            deleteItem(itemId) {
                this.selectedItems = [itemId];
                this.confirmDeleteItems();
            },

            /**
             * Закрываем форму редактирования
             *
             * @method closeEditForm
             */
            closeEditForm() {
                this.isShowedEditForm = false;
                this.isNewRecord = false;
            },

            /**
             * Закрываем форму редактирования
             *
             * @method closeEditForm
             */
            closeParentForm() {
                let url = this.schema.forms.parent._link;

                if (!_.isEmpty(url)) {
                    _u.redirect(url);
                }
            },

            /**
             * Обработчик события `changeSort` запрашивает данные с учётом новой сортировки. Сохранять сортировку в
             * данных компонента нет смысла, т.к. она возвратится к нам со схемой в результатах запроса
             *
             * @method changeSort
             * @param {object} sort - Данные о сортировке
             */
            changeSort(sort) {
                localStorage.set('udi_sort_' + this.schemaCode, Object.assign(this.schema.list.sort, sort));
                this.getList(this.schema._link, sort);
            },

            /**
             * Обработчик события `changePage` запрашивает данные с учётом параметров постраничной навигации
             *
             * @method changePage
             * @param {object} pagination - Данные о постраничной навигации
             */
            changePage(pagination) {
                this.getList(this.schema._link, Object.assign(this.schema.list.sort, pagination), (schema) => {
                    localStorage.set('udi_pagination_' + this.schemaCode, schema.list.pagination);
                });
            },

            /**
             * Открываем диалог на подтвержедние удаления данных
             *
             * @method confirmDeleteItems
             */
            confirmDeleteItems() {
                this.isShowedConfirmDelete = true;
                this.messageConfirmDelete = 'Видалити записи з довідника?';
            },

            /**
             * Скрываем диалог на подтвержедние удаления данных
             *
             * @method hideConfirmDeleteItems
             */
            hideConfirmDeleteItems() {
                this.isShowedConfirmDelete = false;
            },

            /**
             * Метод генерирующий данные для сортировки, постарнички и фильтра с учётом входящих данных
             *
             * @method getDefaultParams
             */
            getDefaultParams(params) {
                let filter = {};
                let arFilter = [];

                if (this.schema.list.filter !== undefined) {
                    _.forEach(this.schema.list.filter.fields, (field) => {
                        if (field.value !== undefined && field.value !== '' && field.value !== 'null' && field.value !== null) {
                            arFilter.push(field.code + '::' + (_.isArray(field.value) ? field.value.join('|') : field.value));
                        }
                    });
                    if (!_.isEmpty(arFilter)) {
                        filter['filter'] = arFilter.join(';');
                    }
                }

                return Object.assign({}, this.schema.list.sort, this.schema.list.pagination, filter, params);
            },

            /**
             * Открываем форму редактирования
             *
             * @method openEditForm
             * @param {boolean} isNew - Признак того, что мы создаём новую запись
             */
            openEditForm(isNew) {
                this.isShowedEditForm = true;
                this.isNewRecord = isNew;
            },

            selectCategory(category) {
                let url = _.isEmpty(category) ? this.schema._link : category._link;
                // сбрасываем значения фильтров в схеме
                localStorage.remove('udi_pagination_' + this.schemaCcode);
                this._resetGridFilter();
                this.selectedCategory = category;
                this.getFirstPage(url);
            },

            /**
             * Открываем форму настроек таблицы
             *
             * @method openGridSettings
             */
            openGridSettings() {
                this.isShowedGridSettings = true;
            },

            /**
             * Закрываем форму настроек таблицы
             *
             * @method closeGridSettings
             */
            closeGridSettings() {
                this.isShowedGridSettings = false;
            },

            /**
             * Сохраняем данные настроек внешнего вида таблицы
             *
             * @method saveGridSettings
             */
            saveGridSettings(listFields) {
                this.listFields = listFields;
                this.closeGridSettings();
                localStorage.set('udi' + this.schemaCode, listFields);
            },

            /**
             * Открываем фильтр
             *
             * @method openGridFilter
             */
            openGridFilter() {
                this.isShowedGridFilter = true;
            },

            /**
             * Закрываем форму фильтрации записей таблицы
             *
             * @method closeGridFilter
             */
            closeGridFilter() {
                this.isShowedGridFilter = false;
            },

            /**
             * Сохраняем данные фильтрации
             *
             * @method saveGridFilter
             */
            setGridFilter(listFields) {
                this.schema.list.filter.fields = Object.assign({}, listFields);
                this.closeGridFilter();
                this.getFirstPage();
            },

            /**
             * Сбрасываем данные фильтрации
             *
             * @method resetGridFilter
             */
            resetGridFilter() {
                this.closeGridFilter();
                this._resetGridFilter();
                this.getFirstPage();
            },


            /**
             * Сбрасываем данные фильтрации в схеме
             *
             * @method _resetGridFilter
             */
            _resetGridFilter() {
                _.forEach(this.schema.list.filter.fields, (field) => {
                    field.value = null;
                });
                this.selectedCategory = false;
            },

            getFirstPage(url) {
                url = url ? url : this.schema._link;
                this.getList(url, { page: 1 });
            },

            clickAction(actionIndex) {
                let action = this.schema.list.actions[actionIndex];
                if (action.active) {
                    if (action.type == 'custom_request') {
                        this.requestHttp('get', action.url, {}, () => {
                            if (action.need_reload) {
                                this.getList(this.init_url);
                            }
                        });
                    } else  {
                        if (this.selectedItems && this.selectedItems.length > 0) {
                            this.getList(this.schema._link, {action: action.code, item_ids: this.selectedItems}, (schema) => {
                                let actions = this.schema.list.actions;
                                let result = _.find(actions, {code: action.code});
                                if(result.success){
                                    this.notifySuccess(result.success);
                                    this.selectedItems = [];
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
                            });
                        }
                    }
                }
            },

        }
    }
</script>
