<template>
    <div class="pagination-panel">
        <div class="pagination-navi-panel">
            <paginate v-if="pagination && pagination.total_pages > 1"
                    ref="paginate"
                    :page-count="pagination.total_pages"
                    :margin-pages="1"
                    :page-range="3"
                    :initial-page="pagination.page - 1"
                    :container-class="'pagination'"
                    :page-class="'page-item'"
                    :page-link-class="'page-link btn-primary'"
                    :prev-class="'prev-item page-item nav-item'"
                    :prev-link-class="'page-link prev-link-item'"
                    :next-class="'next-item page-item nav-item'"
                    :next-link-class="'page-link next-link-item'"
                    :break-view-class="'break-view'"
                    :break-view-link-class="'break-view-link'"
                    :prev-text="' '"
                    :next-text="' '"
                    :click-handler="changePageNum"
            ></paginate>
        </div>
        <div class="pagination-per-page-panel">
            <v-select v-if="pagination && pagination.total_items > 10"
                :options="rowPerPageOptions"
                :searchable="false"
                :filterable="false"
                :value="getSelectedRowPerPage()"
                @input="changeRowPerPage"
            >
            </v-select>
        </div>
    </div>
</template>

<script>
    import paginate from 'vuejs-paginate';
    import vSelect from 'vue-select';

    /**
     * Универсальный справочник, компонент постраничной навигации.
     *
     * p.s. Прикручивать к нему поддержку v-model не стал, т.к. кол-ва кода не изменится, как и понимание логики
     * (шило на мыло). Всё построено на вызове событии changePage родительского компонента.
     *
     * @vuedoc
     * @module vue:components/handbook/grids/Pagination
     * @exports vue:components/handbook/grids/Pagination
     */
    export default {
        name: "handbook-pagination",

        components: {
            'paginate': paginate,
            'v-select': vSelect
        },

        /**
         * Входящие параметры
         *
         * @property {object} pagination - Параметры для постраничной навигации
         */
        props: [
            'pagination'
        ],

        /**
         * Данные компонента
         *
         * @method data
         * @property {array} rowPerPageOptions - Список размеров страницы
         * @property {boolean} initMode - Признак того, что выполняется инициализация данных компонента. Взводится в
         * момент выбора одного из параметров. Часть "финта ушами" для корректно работы с компонентом paginate
         */
        data() {
            return {
                rowPerPageOptions: [
                    { id: 10, label: '10'},
                    { id: 20, label: '20'},
                    { id: 50, label: '50'},
                    { id: 100, label: '100'},
                ],
                initMode: false
            }
        },

        /**
         * Отслеживаемые данные
         *
         * @property {object} pagination - Входящие параметры для постраничной навигации
         */
        watch: {
            pagination: function(newValue, oldValue) {
                if (this.$refs.paginate !== undefined) {
                    // Финт ушами подсмотренный в документации vuejs-paginate
                    this.$refs.paginate.selected = newValue.page - 1;
                }

            }
        },

        methods: {
            /**
             * Меняем номер страницы
             *
             * @method changePageNum
             * @param {numeric} pageNum - Номер страницы
             */
            changePageNum(pageNum) {
                if (!this.initMode) {
                    let pagination = { page: pageNum, per_page: this.pagination.per_page };
                    /**
                     * Вызов метода `changePage` в {@link vue:containers/HandbookIndex родительском компоненте HandbookIndex}, который
                     * обрабатывает запрос на изменение данных о постраничной навигации (номер старницы, размер, ...)
                     *
                     * @event handbook/Pagination=>changePage
                     */
                    this.$emit('changePage', pagination);
                }
            },

            /**
             * Меняем размер страницы
             *
             * @method changeRowPerPage
             * @param {object} item - Размер страницы
             */
            changeRowPerPage(item) {
                if (!this.initMode && !_.isEmpty(item)) {
                    let pagination = { page: 1, per_page: item.id };
                    this.$emit('changePage', pagination);
                }
            },

            /**
             * Возвращаем параметры постраничной навигациия. Используется компонентом
             * {@link https://npmjs.com/package/vuejs-paginate `vuejs-paginate`}
             *
             * @method getSelectedRowPerPage
             * @returns {object}
             */
            getSelectedRowPerPage() {
                this.initMode = true;
                this.$nextTick(() => {
                    this.initMode = false;
                });

                return this.pagination.per_page ?
                    { id: this.pagination.per_page, label: this.pagination.per_page} :
                    { id: 10, label: '10' };
            },

        }
    }
</script>

