/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
require('./bootstrap');

import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import {Pagination} from 'element-ui';
import CKEditor from '@ckeditor/ckeditor5-vue';

import elementLocale from 'element-ui/lib/locale/lang/en';

Vue.use(Pagination);
Vue.use(ElementUI, {locale: elementLocale});
Vue.use(CKEditor);
import * as VueGoogleMaps from 'vue2-google-maps'

Vue.use(VueGoogleMaps, {
    load: {
        key: 'AIzaSyDp5t3FS7cUFytD2TP6ghgMka8zhB4LxXg',
        libraries: 'places'
    }
})

window.Vue = require('vue');

// User
Vue.component('admin-user-save-component', require('./base/admin/user/page/SaveComponent').default);
Vue.component('admin-profile-save-component', require('./base/admin/user/page/ProfileSaveComponent').default);

// User role
Vue.component('admin-role-save-component', require('./base/admin/role/page/SaveComponent').default);

//Base Component
Vue.component('delete-component', require('./base/components/admin/Delete').default);
Vue.component('checkbox-list-component', require('./base/components/admin/checkboxList').default);
Vue.component('checked-all-component', require('./base/components/admin/checkedAll').default);
Vue.component('per-page-component', require('./base/components/admin/perPage').default);
Vue.component('status-component', require('./base/components/admin/Status').default);
Vue.component('save-config', require('./base/components/admin/SaveConfig').default);



/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#admin',
});
