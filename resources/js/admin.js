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
        key: 'AIzaSyC3D2YUPeQdWLn4dBydz25IuYu2E2DTUGQ',
        libraries: 'places'
    }
})

window.Vue = require('vue');

// User
Vue.component('admin-user-save-component', require('./base/admin/user/page/SaveComponent').default);
Vue.component('admin-profile-save-component', require('./base/admin/user/page/ProfileSaveComponent').default);
Vue.component('admin-user-component', require('./base/admin/user/partials/User').default);
Vue.component('admin-user-filter-component', require('./base/admin/user/partials/UserFilters').default);


// User role
Vue.component('admin-role-save-component', require('./base/admin/role/page/SaveComponent').default);

//Base Component
Vue.component('delete-component', require('./base/components/admin/Delete').default);
Vue.component('checkbox-list-component', require('./base/components/admin/checkboxList').default);
Vue.component('checked-all-component', require('./base/components/admin/checkedAll').default);
Vue.component('per-page-component', require('./base/components/admin/perPage').default);
Vue.component('status-component', require('./base/components/admin/Status').default);
Vue.component('save-config', require('./base/components/admin/SaveConfig').default);
Vue.component('image-modal', require('./base/components/admin/ImageModal').default);

//Asset
Vue.component('asset-page-form', require('./base/admin/asset/page/AssetForm').default);
Vue.component('manager-page-form', require('./base/admin/asset/page/ManagerForm').default);
Vue.component('asset-view-page-form', require('./base/admin/asset/page/AssetViewForm').default);
Vue.component('asset-filter-component', require('./base/admin/asset/partials/AssetFilters').default);

//Revenue
Vue.component('revenue-filter-component', require('./base/admin/revenue/partials/RevenueFilters').default);
Vue.component('revenue-export-component', require('./base/admin/revenue/partials/RevenueExport').default);
Vue.component('revenue-view-page-form', require('./base/admin/revenue/page/RevenueViewForm').default);

//Payment
Vue.component('payment-page-form', require('./base/admin/payment/page/PaymentForm').default);
Vue.component('payment-view-page-form', require('./base/admin/payment/page/PaymentViewForm').default);

//Rental
Vue.component('rental-page-form', require('./base/admin/rental/page/RentalForm').default);
Vue.component('rental-view-page-form', require('./base/admin/rental/page/RentalViewForm').default);

//Invetment
Vue.component('investment-page-form', require('./base/admin/investment/page/InvestmentForm').default);
Vue.component('investment-view-page-form', require('./base/admin/investment/page/InvestmentViewForm').default);

//Comment
Vue.component('unread-comments', require('./base/admin/asset/partials/AssetUnreadComments').default);
Vue.component('pending-payments', require('./base/admin/notification/PaymentNotification').default);
Vue.component('pending-rentals', require('./base/admin/notification/RentalNotification').default);
Vue.component('upcoming-reminders', require('./base/admin/notification/ReminderNotification').default);

// Investor
Vue.component('admin-investor-save-component', require('./base/admin/investor/page/SaveComponent').default);
Vue.component('admin-investor-view-component', require('./base/admin/investor/page/ViewComponent').default);
Vue.component('admin-investor-component', require('./base/admin/investor/partials/Investor').default);
Vue.component('admin-investor-filter-component', require('./base/admin/investor/partials/InvestorFilters').default);
Vue.component('investor-profile-save-component', require('./base/admin/investor/page/ProfileSaveComponent').default);
Vue.component('update-investor-manager', require('./base/admin/investor/partials/UpdateInvestorManager').default);

//Lead
Vue.component('lead-page-form', require('./base/admin/lead/page/LeadForm').default);
Vue.component('lead-view-page-form', require('./base/admin/lead/page/LeadViewForm').default);
Vue.component('lead-filter-component', require('./base/admin/lead/partials/LeadFilters').default);
Vue.component('update-lead-manager', require('./base/admin/lead/partials/UpdateLeadManager').default);
Vue.component('leads-export-component', require('./base/admin/lead/partials/ExportLeads').default);

//Sale
Vue.component('sale-page-form', require('./base/admin/sale/page/SaleForm').default);
Vue.component('sale-view-page-form', require('./base/admin/sale/page/SaleViewForm').default);
Vue.component('sales-export-component', require('./base/admin/sale/partials/ExportSales').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#admin',
});
