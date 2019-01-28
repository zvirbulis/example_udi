<template>
    <div v-if="show_categories && categories.items" class="handbook-categories">
        <ul>
            <li :class="{'selected': selected_category.id == undefined}" @click="selectCategory({})">
                Показати всі
            </li>

            <li v-for="category in categories.items"
                :key="category.id"
                @click="selectCategory(category)"
                :class="{'selected': selected_category.id == category.id}"
            >
                {{ category.label }}
            </li>
        </ul>
    </div>
</template>

<script>
    import paginate from 'vuejs-paginate';
    import vSelect from 'vue-select';

    /**
     * Универсальный справочник, вывод категорий
     *
     * @vuedoc
     * @module vue:components/handbook/grids/Categories
     * @exports vue:components/handbook/grids/Categories
     */
    export default {
        name: "handbook-categories",

        components: {
            'paginate': paginate,
            'v-select': vSelect
        },

        /**
         * Входящие параметры
         *
         * @property {object} categories - Список категорий
         * @property {object} selected_category - Выбранная категория
         * @property {boolean} show_categories - Признак того, отображать категории или нет
         */
        props: {
            categories: {type: Object, default: {}},
            selected_category: {type: Object, default: {}},
            show_categories: {type: Boolean, default: true},
        },

        methods: {
            /**
             * Выбираем категорию
             *
             * @method selectCategory
             * @param {object} category - Категория
             */
            selectCategory(category) {
                /**
                 * Вызов метода `selectCategory` в {@link vue:containers/HandbookIndex родительском компоненте HandbookIndex}, который
                 * обрабатывает запрос на выбор данных по указанной категории
                 *
                 * @event handbook/Categories=>selectCategory
                 */
                this.$emit('selectCategory', category);
            },
        }
    }
</script>

