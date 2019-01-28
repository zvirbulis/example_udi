/**
 * Инициализация vue (библиотека, компоненты, etc)
 *
 * @namespace vue:init
 */

window.Vue = require('vue');

import hash from 'object-hash';
import _u from 'jsAssets/utils/';
/** Библиотека bootstrap компонентов для Vue **/
import BootstrapVue from 'bootstrap-vue';
/** Отслеживаем клик вне рабочей области компонента **/
import ClickOutside from 'vue-click-outside';
/** Компонент для отображения textarea с автоизменяемой высотой **/
import VueTextareaAutosize from 'vue-textarea-autosize';
/** Вывод служебных сообщений. См. в vueComponents/app/Header, компонент <notifications/>**/
import Notifications from 'vue-notification';
/** Библиотека для работы с адаптивом **/
import VueMq from 'vue-mq';
/** Хранилище состояни приложения **/
import store from 'jsAssets/store/index';

import { mapGetters } from 'vuex';

/** Настраиваем разрешения экрана **/
import {MQ_BREAKPOINTS} from 'jsAssets/constants/media';
Vue.use(VueMq, {
    breakpoints: MQ_BREAKPOINTS
});


Vue.use(Notifications);
Vue.use(BootstrapVue);
Vue.use(VueTextareaAutosize);

Vue.directive('click-outside', ClickOutside);

Vue.mixin({
    computed: {
        ...mapGetters([
            'user',
        ]),

        enableSkd() {
            return window.app_env.enable_skd;
        },
        enableMessenger() {
            return window.app_env.enable_messenger;
        },

        isAdmin() {
            return this.user ? this.user.group == 'admin' : false;
        },
        isDirector() {
            return this.user ? this.user.group == 'director' : false;
        },
        isParent() {
            return this.user ? this.user.group == 'parent' : false;
        },
        isStudent() {
            return this.user ? this.user.group == 'student' : false;
        },

        overlayLoading() {
            return true;
        },

    },
    methods: {
        /**
         * Формируем уведомление об успешном выполнении операции
         *
         * @memberOf vue:init
         * @method notifySuccess
         * @param { string } message - Сообщение об успешном выполнении
         */
        notifySuccess(message) {
            this.$notify({
                type: 'success',
                text: message,
                duration: 3000,
            });
        },

        /**
         * Формируем уведомление об ошибке при выполнении операции
         *
         * @memberOf vue:init
         * @method notifySuccess
         * @param { string } message - Сообщение об ошибке
         */
        notifyError(message) {
            this.$notify({
                type: 'error',
                text: message,
                duration: -1,
            });
        },

        /**
         * Формируем уведомление о предупреждении при выполнении операции
         *
         * @memberOf vue:init
         * @method notifyWarning
         * @param { string } message - Сообщение
         */
        notifyWarning(message) {
            this.$notify({
                type: 'warn',
                text: message,
                duration: 5000,
            });
        },

        /**
         * Генерируем идентификатор для html тега. Метод подключается через примеси (глобально)
         *
         * @memberOf vue:init
         * @method getHtmlId
         * @param { string } prefix - Префикс идентифкатора
         * @param { number } id - Идентифкатор элемента (или суффикс)
         * @returns { string } - Идентификатор html тега
         */
        getHtmlId(prefix, id) {
            return prefix + '_' + id
        },

        /**
         * Дополнение к функционалу пакета {@link https://npmjs.com/package/vue-mq|vue-mq}.
         * Возвращаем `true`, если текущее разрешение экрана (`this.$mq`) меньше входящего параметра `mq`
         *
         * @memberOf vue:init
         * @method mqAbove
         * @param {string} mq - Значение разрешения экрана (см. в сторону `jsAssets/constants/media`)
         * @returns {boolean}
         */
        $mqAbove(mq) {
            return this.$mqSearch(mq);
        },

        /**
         * Дополнение к функционалу пакета {@link https://npmjs.com/package/vue-mq|vue-mq}.
         * Возвращаем `true`, если текущее разрешение экрана (`this.$mq`) больше входящего параметра `mq`
         *
         * @memberOf vue:init
         * @method mqBelow
         * @param {string} mq - Значение разрешения экрана (см. в сторону `jsAssets/constants/media`)
         * @returns {boolean}
         */
        $mqBelow(mq) {
            return !this.$mqSearch(mq);
        },

        /**
         * Дополнение к функционалу пакета {@link https://npmjs.com/package/vue-mq|vue-mq}.
         * Просматриваем массив разрешений экрана (MQ_BREAKPOINTS) указанных при инициализации пакета `vue-mq` и ищем
         * совпадение `this.$mq` с входящим параметром mq. Если значение `this.$mq` встречается в массиве ранее, чем
         * значение mq, то возвращаем `false`, иначе `true`
         *
         * @memberOf vue:init
         * @method mqSearch
         * @param {string} mq - Значение разрешения экрана (см. в сторону `jsAssets/constants/media`)
         * @returns {boolean}
         */
        $mqSearch(mq) {
            let result = false;
            let found = false;
            _.map(MQ_BREAKPOINTS, (item, index)=> {
                if (this.$mq == index) {
                    result = found;
                }
                if (mq == index) {
                    found = true;
                }
            });
            return result;
        },

        /**
         * Возвращает роут для инициации чата с пользователем userId
         *
         * @param userId
         * @returns {string}
         */
        getStartMessengerLink(userId) {
            return (this.enableMessenger && userId) ? route('messages.chat.user.create', {user: userId}) : 'javascript:;';
        },


        /**
         * Возвращает роут для просмотра профиля userId
         *
         * @param userId
         * @returns {string}
         */
        getUserProfileLink(userId) {
            return userId ? route('users.profile.index', {user: userId}) : 'javascript:;';
        },

        /**
         * Возвращает роут для инициации чата с классом studentClassId
         *
         * @param studentClassId
         * @returns {string}
         */
        getStartStudentClassMessengerLink(studentClassId) {
            return (this.enableMessenger && studentClassId) ? route('messages.chat.class.create', {student_class: studentClassId}) : 'javascript:;';
        },

        /**
         * Возвращает роут для перехода на расписание учителя
         *
         * @param teacherId
         * @returns {string}
         */
        getTeacherScheduleLink(teacherId) {
            return teacherId ? route('schedule.teacher.index', {teacher: teacherId}) : 'javascript:;';
        },
        /**
         * Возвращает роут для перехода на распределение по-недельной нагрузки учителя
         *
         * @param teacherId
         * @returns {string}
         */
        getTeacherReportLink(teacherId){
            return teacherId ? route('hourly_load.teacher.index',{teacher: teacherId}) : 'javascript:;';
        },

        makeNotifyForAjaxError(error) {
            if (error.response) {
                app.$notify({
                    title: 'Error',
                    text: 'Сталася помилка: ' + _u.getResponseError(error),
                    type: 'error'
                });
            }
        },

        getClassRoomScheduleLink(classRoomId) {
            return classRoomId ? route('schedule.classroom.index', {class_room: classRoomId}) : 'javascript:;';
        },

        getStudentClassListLink(studentClassId) {
            return studentClassId ? route('studentclass.students', {student_class: studentClassId}) : 'javascript:;';
        },

        truncate(str, maxlength) {
            if (str.length > maxlength) {
                return str.slice(0, maxlength - 3) + '...';
            }

            return str;
        },

        getScheduleStudentClassLink(studentClassId) {
            return studentClassId ? route('schedule.class.index', {student_class: studentClassId}) : 'javascript:;';
        },

        /**
         * Обработчик HTTP запросов
         *
         * @method requestHttp
         * @param {string} type - Тип HTTP запроса (get, put, post, delete)
         * @param {string} url - URL HTTP запроса
         * @param {object} params - Параметры запроса
         * @param {function} callback - Callback-функция для обработки успешного выполнения операции
         * @param {function} callbackError - Callback-функция для обработки ошибок HTTP запроса
         */
        requestHttp(type, url, params, callback, callbackError) {
            params = _u.checkValue(params, {});
            let _this = this;

            if (_this.showLoading) return;

            params.timestamp = Date.now();

            _this.showLoading = true;
            _u.requestHttp(type, url, params, (response) => {
                _this.showLoading = false;
                if (response.status == 200) {
                    if (_.isFunction(callback)) callback(response.data);
                } else {
                    _this.notifyError('Сталася помилка: ' + response.message);
                    if (_.isFunction(callbackError)) callbackError(response);
                }
            }, (error) => {
                _this.showLoading = false;

                if (error.response) {
                    if (error.response.status === 400 && error.response.data.data.forms !== undefined) {
                        _this.schema = Object.assign({}, error.response.data.data);
                    } else {
                        _this.notifyError('Сталася помилка: ' + _u.getResponseError(error));
                        if (_.isFunction(callbackError)) callbackError(error);
                    }
                }
            });
        },

        getHashData(data) {
            return hash(data);
        },
    }
});

/** Создаем vue компоненты/контейнеры  **/

Vue.component('canteen', require('vueComponents/canteen/Canteen'));
Vue.component('canteen-news', require('vueComponents/canteen/CanteenNews'));
Vue.component('app-header', require('vueComponents/app/Header'));
Vue.component('app-menu', require('vueComponents/AppMenu'));
Vue.component('app-menu-new-messages', require('vueComponents/app/menu/NewMessages')); // потом отключить, когда app-menu завернём в vue-компонент ak@
Vue.component('schedule-class-index', require('vueContainers/schedule/ClassIndex'));
Vue.component('schedule-classroom-index', require('vueContainers/schedule/ClassRoomIndex'));
Vue.component('schedule-constructor-index', require('vueContainers/schedule/ConstructorIndex'));
Vue.component('schedule-student-index', require('vueContainers/schedule/StudentIndex'));
Vue.component('replacements-index', require('vueContainers/schedule/ReplacementsIndex'));
Vue.component('schedule-teacher-index', require('vueContainers/schedule/TeacherIndex'));
Vue.component('journal-lessons-index', require('vueContainers/JournalLessonsIndex'));
Vue.component('curator-journal-grades', require('./components/curator/JournalGrades'));
Vue.component('studentparent-journal-grades', require('vueContainers/StudentParentJournalGrades'));
Vue.component('daybook-index', require('vueContainers/DaybookIndex'));
Vue.component('home-feed-index', require('vueContainers/HomeFeedIndex'));
Vue.component('data-viewer-index', require('vueContainers/DataViewerIndex'));
Vue.component('floatholder-input', require('./components/FloatHolderInput'));
Vue.component('attendances-log', require('vueComponents/app/AttendancesLog'));
Vue.component('downloadable-file-list', require('vueComponents/DownloadableFileList'));
Vue.component('select-student-class', require('vueComponents/schedule/class/SelectStudentClass'));
Vue.component('students-progress-reports-groups', require('vueContainers/reports/StudentsProgressReportsGroups'));
Vue.component('students-progress-reports-list', require('vueContainers/reports/StudentsProgressReportsList'));
Vue.component('student-progress-report', require('vueContainers/reports/StudentProgressReport'));
Vue.component('news-index', require('vueContainers/NewsIndex'));
Vue.component('hourly-load-distribution-index', require('vueContainers/hourly_load/DistributionIndex'));
Vue.component('single-teacher-hourly-load-index', require('vueContainers/hourly_load/SingleTeacherIndex'));
Vue.component('personal-photo', require('vueComponents/profile/PersonalPhoto'));
Vue.component('roadmap-index', require('vueContainers/RoadmapIndex'));

if (window.app_env.enable_messenger) {
    Vue.component('chat-index', require('vueContainers/ChatIndex'));
}

// справочники
Vue.component('handbook-index', require('vueContainers/HandbookIndex'));

Vue.config.devtools = Vue.config.performance = process.env.NODE_ENV !== 'production';

const app = new Vue({
    el: '#app',
    store
});

