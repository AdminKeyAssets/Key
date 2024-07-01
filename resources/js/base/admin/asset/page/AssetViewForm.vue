<template>
    <div>
        <div class="col-xs-12">
            <div class="registration-btn project-title-buttons">
                <div class="project-title">

                </div>
                <div>
                    <div class="block col-md-9">
                        <el-form v-loading="loading"
                                 element-loading-text="Loading..."
                                 element-loading-spinner="el-icon-loading"
                                 element-loading-background="rgba(0, 0, 0, 0.0)"
                                 class="form-horizontal form-bordered">

                            <el-row>
                                <el-col :span="16">
                                    <div v-if="form.project_name" class="form-group dashed">
                                        <label class="col-md-2 control-label">Project Name:</label>
                                        <div class="col-md-10 uppercase-medium">
                                            {{ form.project_name }}
                                        </div>
                                    </div>

                                    <div v-if="form.project_description" class="form-group dashed">
                                        <label class="col-md-2 control-label">Project Description:</label>
                                        <div class="col-md-10 uppercase-medium">
                                            {{ form.project_description }}
                                        </div>
                                    </div>

                                    <div v-if="form.project_link" class="form-group dashed">
                                        <label class="col-md-2 control-label">Project Link:</label>
                                        <div class="col-md-10 uppercase-medium">
                                            <a :href="form.project_link" target="_blank">
                                                {{ form.project_link }}
                                            </a>
                                        </div>
                                    </div>

                                    <div v-if="form.city" class="form-group dashed">
                                        <label class="col-md-2 control-label">City:</label>
                                        <div class="col-md-10 uppercase-medium">
                                            {{ form.city }}
                                        </div>
                                    </div>

                                    <div v-if="form.address" class="form-group dashed">
                                        <label class="col-md-2 control-label">Address:</label>
                                        <div class="col-md-10 uppercase-medium">
                                            {{ form.address }}
                                        </div>
                                    </div>
                                </el-col>
                                <el-col :span="8">
                                    <div v-if="form.icon" class="form-group dashed">
                                        <div class="col-md-10 uppercase-medium">
                                            <div v-if="form.icon">
                                                <ImageModal
                                                    :image-path="form.icon"
                                                    :thumbnail="form.icon"
                                                    :width="300"
                                                    :height="300"></ImageModal>
                                            </div>
                                        </div>
                                    </div>
                                </el-col>
                            </el-row>
                            <el-row>
                                <div v-if="form.location" class="form-group dashed">
                                    <label class="col-md-1 control-label">Location:</label>
                                    <MapMarker disabled v-if="form.location || !form.id" :item="form"></MapMarker>
                                </div>

                                <div v-if="form.delivery_date" class="form-group dashed">
                                    <label class="col-md-1 control-label">Delivery Date:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.delivery_date }}
                                    </div>
                                </div>

                                <div v-if="form.type" class="form-group dashed">
                                    <label class="col-md-1 control-label">Asset Type:</label>
                                    <div class="col-md-3 uppercase-medium">
                                        {{ form.type }}
                                    </div>
                                </div>

                                <div v-if="form.floor" class="form-group dashed">
                                    <label class="col-md-1 control-label">Floor:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.floor }}
                                    </div>
                                </div>

                                <div v-if="form.flat_number" class="form-group dashed">
                                    <label class="col-md-1 control-label">Flat number:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.flat_number }}
                                    </div>
                                </div>

                                <div v-if="form.area" class="form-group dashed">
                                    <label class="col-md-1 control-label">Area (m2):</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.area }}
                                    </div>
                                </div>

                                <div v-if="form.price" class="form-group dashed">
                                    <label class="col-md-1 control-label">M2 Price:</label>
                                    <div class="col-md-7 uppercase-medium">
                                        {{ form.price }} {{ form.currency }}
                                    </div>
                                </div>

                                <div v-if="form.total_price" class="form-group dashed">
                                    <label class="col-md-1 control-label">Total Price:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.total_price }} {{ form.currency }}
                                    </div>
                                </div>

                                <div v-if="form.condition" class="form-group dashed">
                                    <label class="col-md-1 control-label">Delivery Condition:</label>
                                    <div class="col-md-3 uppercase-medium">
                                        {{ form.condition }}
                                    </div>
                                </div>

                                <div v-if="form.cadastral_number" class="form-group dashed">
                                    <label class="col-md-1 control-label">Cadastral Number:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.cadastral_number }}
                                    </div>
                                </div>

                                <div v-if="form.investor_id" class="form-group dashed">
                                    <label class="col-md-1 control-label">Investor:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <el-select v-model="form.investor_id" disabled :value="form.investor_id"
                                                   filterable placeholder="Select">
                                            <el-option v-for="item in investors" :key="item.id"
                                                       :label="item.name + item.surname"
                                                       :value="item.id"></el-option>
                                        </el-select>
                                    </div>
                                </div>

                                <div v-if="form.floor_plan" class="form-group dashed">
                                    <label class="col-md-1 control-label">Floor Plan:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <div v-if="form.floor_plan">
                                            <ImageModal v-if="form.floorPlanPreview" :image-path="form.floorPlanPreview"
                                                        :thumbnail="form.floorPlanPreview"></ImageModal>
                                            <ImageModal v-else :image-path="form.floor_plan"
                                                        :thumbnail="form.floor_plan"></ImageModal>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="form.flat_plan" class="form-group dashed">
                                    <label class="col-md-1 control-label">Flat Plan:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <div v-if="form.flat_plan">
                                            <ImageModal v-if="form.flatPlanPreview" :image-path="form.flatPlanPreview"
                                                        :thumbnail="form.flatPlanPreview"></ImageModal>
                                            <ImageModal v-else :image-path="form.flat_plan"
                                                        :thumbnail="form.flat_plan"></ImageModal>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="form.asset_status" class="form-group dashed">
                                    <label class="col-md-1 control-label">Asset Status:</label>
                                    <div class="col-md-3 uppercase-medium">
                                        {{ form.asset_status }}
                                    </div>
                                </div>

                                <div v-if="form.asset_status === 'Rented'">
                                    <div v-if="form.tenant.name || form.tenant.surname" class="form-group dashed">
                                        <label class="col-md-1 control-label">Tenant Name:</label>
                                        <div class="col-md-10 uppercase-medium">
                                            {{ form.tenant.name }} {{ form.tenant.surname }}
                                        </div>
                                    </div>
                                    <div v-if="form.tenant.id_number" class="form-group dashed">
                                        <label class="col-md-1 control-label">ID Number:</label>
                                        <div class="col-md-10 uppercase-medium">
                                            {{ form.tenant.id_number }}
                                        </div>
                                    </div>
                                    <div v-if="form.tenant.citizenship" class="form-group">
                                        <label class="col-md-1 control-label">Citizenship:</label>
                                        <div class="col-md-10 uppercase-medium">
                                            {{ form.tenant.citizenship }}
                                        </div>
                                    </div>
                                    <div v-if="form.tenant.email" class="form-group dashed">
                                        <label class="col-md-1 control-label">Email:</label>
                                        <div class="col-md-10 uppercase-medium">
                                            {{ form.tenant.email }}
                                        </div>
                                    </div>
                                    <div v-if="form.tenant.phone" class="form-group phone">
                                        <label class="col-md-1 control-label">Phone:</label>
                                        <div class="col-md-10 uppercase-medium">
                                            {{ form.tenant.prefix + form.tenant.phone }}
                                        </div>
                                    </div>
                                    <div v-if="form.tenant.agreement_date" class="form-group dashed">
                                        <label class="col-md-1 control-label">Agreement Date:</label>
                                        <div class="col-md-10 uppercase-medium">
                                            {{ form.tenant.agreement_date }}
                                        </div>
                                    </div>
                                    <div v-if="form.tenant.agreement_term" class="form-group dashed">
                                        <label class="col-md-1 control-label">Agreement Term:</label>
                                        <div class="col-md-10 uppercase-medium">
                                            {{ form.tenant.agreement_term }}
                                        </div>
                                    </div>
                                    <div v-if="form.tenant.monthly_rent" class="form-group dashed">
                                        <label class="col-md-1 control-label">Monthly Rent:</label>
                                        <div class="col-md-7 uppercase-medium">
                                            {{ form.tenant.monthly_rent }} {{ form.tenant.currency }}
                                        </div>
                                    </div>
                                    <div v-if="form.rentals.length">
                                        <el-table :data="form.rentals" style="width: 100%">
                                            <el-table-column prop="number" label="Payment" width="150"/>
                                            <el-table-column prop="payment_date" label="Payment Date" width="180"/>
                                            <el-table-column prop="amount" label="Amount" width="180"/>
                                        </el-table>
                                    </div>
                                </div>

                                <div v-if="form.attachments" class="form-group dashed">
                                    <label class="col-md-1 control-label">Attachments:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <div v-if="form.attachments">
                                            <ul>
                                                <li v-for="(file, index) in form.attachments" :key="index"
                                                    style="display: inline-block; margin-right: 10px">
                                                    <img v-if="file.preview" :src="file.preview" alt="preview"
                                                         style="max-width: 100px;"/>
                                                    <img v-else-if="file.type === 'image'" :src="file.path"
                                                         alt="preview" style="max-width: 100px;"/>
                                                    <a v-else :href="file.path" target="_blank">{{ file.name }}</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="form.extraDetails" class="form-group dashed">
                                    <label class="col-md-1 control-label">Extra Details:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <el-form-item v-for="extraDetail in form.extraDetails" :key="extraDetail.id">
                                            <div class="col-md-5 uppercase-medium">{{ extraDetail.key }}:</div>
                                            <div class="col-md-5 uppercase-medium">{{ extraDetail.value }}</div>
                                        </el-form-item>
                                    </div>
                                </div>

                                <div v-if="form.agreement_date" class="form-group dashed">
                                    <label class="col-md-1 control-label">Agreement Date:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.agreement_date }}
                                    </div>
                                </div>

                                <div v-if="form.agreement" class="form-group dashed">
                                    <label class="col-md-1 control-label">Agreement:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <div v-if="form.agreement">
                                            <p v-if="form.agreement">File: <a :href="form.agreement" target="_blank">View
                                                Attachment</a></p>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="form.agreement_status" class="form-group dashed">
                                    <label class="col-md-1 control-label">Agreement Status:</label>
                                    <div class="col-md-3 uppercase-medium">
                                        {{ form.agreement_status }}
                                    </div>
                                </div>

                                <template v-if="form.agreement_status === 'Complete'">
                                    <div v-if="form.ownership_certificate" class="form-group dashed">
                                        <label class="col-md-1 control-label">Ownership Certificate:</label>
                                        <div class="col-md-10 uppercase-medium">
                                            <div v-if="form.ownership_certificate">
                                                <p v-if="form.ownership_certificate">File: <a
                                                    :href="form.ownership_certificate" target="_blank">View
                                                    Attachment</a></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <template v-if="form.agreement_status === 'Installments'">
                                    <div v-if="form.total_agreement_price" class="form-group dashed">
                                        <label class="col-md-1 control-label">Total Amount:</label>
                                        <div class="col-md-10 uppercase-medium">
                                            {{ form.total_agreement_price }}
                                        </div>
                                    </div>

                                    <div v-if="form.first_payment_date" class="form-group dashed">
                                        <label class="col-md-1 control-label">First Payment Date:</label>
                                        <div class="col-md-10 uppercase-medium">
                                            {{ form.first_payment_date }}
                                        </div>
                                    </div>

                                    <div v-if="form.period" class="form-group dashed">
                                        <label class="col-md-1 control-label">Period:</label>
                                        <div class="col-md-3 uppercase-medium">
                                            {{ form.period }}
                                        </div>
                                    </div>

                                    <div v-if="form.payments && form.payments.length">
                                        <el-table :data="form.payments" style="width: 100%">
                                            <el-table-column prop="number" label="Payment" width="150"/>
                                            <el-table-column prop="payment_date" label="Payment Date" width="180"/>
                                            <el-table-column prop="amount" label="Amount" width="180"/>
                                        </el-table>
                                    </div>
                                </template>

                                <div v-if="form.current_value" class="form-group dashed">
                                    <label class="col-md-1 control-label">Current Value:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.current_value }}
                                    </div>
                                    <div v-if="form.currentValues.length">
                                        <el-table :data="form.currentValues" style="width: 100%">
                                            <el-table-column prop="value" label="Value" width="150"/>
                                            <el-table-column prop="date" label="Date" width="180"/>
                                        </el-table>
                                    </div>
                                </div>
                            </el-row>
                        </el-form>
                    </div>
                    <div class="block col-md-3">
                        <div v-loading="loading"
                             class="form-horizontal form-bordered">
                            <el-card class="box-card" v-if="salesManager">
                                <div slot="header" class="clearfix box-card-header">
                                    <el-row>
                                        <el-col :span="6">
                                            <ImageModal v-if="salesManager.profile_picture"
                                                        :thumbnail="salesManager.profile_picture"
                                                        :image-path="salesManager.profile_picture"
                                                        :rounded="true"></ImageModal>
                                        </el-col>
                                        <el-col :span="18">
                                            <span>Asset Manager</span>
                                        </el-col>
                                    </el-row>
                                </div>
                                <div class="text item">
                                    <p v-if="salesManager.name || salesManager.surname">
                                        Name: {{ salesManager.name }} {{ salesManager.surname }}
                                    </p>
                                    <p v-if="salesManager.phone">
                                        <a :href="whatsappLink" target="_blank" class="phone-container">
                                            <span class="whatsapp-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="24px" height="24px"
                                                     clip-rule="evenodd">
                                                    <path fill="#fff"
                                                          d="M4.868,43.303l2.694-9.835C5.9,30.59,5.026,27.324,5.027,23.979C5.032,13.514,13.548,5,24.014,5c5.079,0.002,9.845,1.979,13.43,5.566c3.584,3.588,5.558,8.356,5.556,13.428c-0.004,10.465-8.522,18.98-18.986,18.98c-0.001,0,0,0,0,0h-0.008c-3.177-0.001-6.3-0.798-9.073-2.311L4.868,43.303z"/>
                                                    <path fill="#fff"
                                                          d="M4.868,43.803c-0.132,0-0.26-0.052-0.355-0.148c-0.125-0.127-0.174-0.312-0.127-0.483l2.639-9.636c-1.636-2.906-2.499-6.206-2.497-9.556C4.532,13.238,13.273,4.5,24.014,4.5c5.21,0.002,10.105,2.031,13.784,5.713c3.679,3.683,5.704,8.577,5.702,13.781c-0.004,10.741-8.746,19.48-19.486,19.48c-3.189-0.001-6.344-0.788-9.144-2.277l-9.875,2.589C4.953,43.798,4.911,43.803,4.868,43.803z"/>
                                                    <path fill="#cfd8dc"
                                                          d="M24.014,5c5.079,0.002,9.845,1.979,13.43,5.566c3.584,3.588,5.558,8.356,5.556,13.428c-0.004,10.465-8.522,18.98-18.986,18.98h-0.008c-3.177-0.001-6.3-0.798-9.073-2.311L4.868,43.303l2.694-9.835C5.9,30.59,5.026,27.324,5.027,23.979C5.032,13.514,13.548,5,24.014,5 M24.014,42.974C24.014,42.974,24.014,42.974,24.014,42.974C24.014,42.974,24.014,42.974,24.014,42.974C24.014,42.974,24.014,42.974,24.014,42.974 M24.014,42.974C24.014,42.974,24.014,42.974,24.014,42.974C24.014,42.974,24.014,42.974,24.014,42.974 M24.014,4C24.014,4,24.014,4,24.014,4C12.998,4,4.032,12.962,4.027,23.979c-0.001,3.367,0.849,6.685,2.461,9.622l-2.585,9.439c-0.094,0.345,0.002,0.713,0.254,0.967c0.19,0.192,0.447,0.297,0.711,0.297c0.085,0,0.17-0.011,0.254-0.033l9.687-2.54c2.828,1.468,5.998,2.243,9.197,2.244c11.024,0,19.99-8.963,19.995-19.98c0.002-5.339-2.075-10.359-5.848-14.135C34.378,6.083,29.357,4.002,24.014,4L24.014,4z"/>
                                                    <path fill="#40c351"
                                                          d="M35.176,12.832c-2.98-2.982-6.941-4.625-11.157-4.626c-8.704,0-15.783,7.076-15.787,15.774c-0.001,2.981,0.833,5.883,2.413,8.396l0.376,0.597l-1.595,5.821l5.973-1.566l0.577,0.342c2.422,1.438,5.2,2.198,8.032,2.199h0.006c8.698,0,15.777-7.077,15.78-15.776C39.795,19.778,38.156,15.814,35.176,12.832z"/>
                                                    <path fill="#fff" fill-rule="evenodd"
                                                          d="M19.268,16.045c-0.355-0.79-0.729-0.806-1.068-0.82c-0.277-0.012-0.593-0.011-0.909-0.011c-0.316,0-0.83,0.119-1.265,0.594c-0.435,0.475-1.661,1.622-1.661,3.956c0,2.334,1.7,4.59,1.937,4.906c0.237,0.316,3.282,5.259,8.104,7.161c4.007,1.58,4.823,1.266,5.693,1.187c0.87-0.079,2.807-1.147,3.202-2.255c0.395-1.108,0.395-2.057,0.277-2.255c-0.119-0.198-0.435-0.316-0.909-0.554s-2.807-1.385-3.242-1.543c-0.435-0.158-0.751-0.237-1.068,0.238c-0.316,0.474-1.225,1.543-1.502,1.859c-0.277,0.317-0.554,0.357-1.028,0.119c-0.474-0.238-2.002-0.738-3.815-2.354c-1.41-1.257-2.362-2.81-2.639-3.285c-0.277-0.474-0.03-0.731,0.208-0.968c0.213-0.213,0.474-0.554,0.712-0.831c0.237-0.277,0.316-0.475,0.474-0.791c0.158-0.317,0.079-0.594-0.04-0.831C20.612,19.329,19.69,16.983,19.268,16.045z"
                                                          clip-rule="evenodd"/>
                                                </svg>
                                            </span>
                                            <span class="managers-phone">
                                                {{ salesManager.prefix }}{{ salesManager.phone }}
                                            </span>
                                        </a>
                                    </p>
                                    <p v-if="salesManager.email">
                                        <a :href="'mailto:' + salesManager.email">
                                            Email: {{ salesManager.email }}
                                        </a>
                                        <a :href="'mailto:' + salesManager.email">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                    </p>
                                </div>
                            </el-card>

                            <el-card class="box-card" v-if="nextPayment" style="margin-top: 20px">
                                <div slot="header" class="clearfix box-card-header">
                                    <span>Next Payment</span> <i
                                    v-if="Date.now() < new Date(this.nextPayment.payment_date)"
                                    class="el-icon-warning"
                                    style="color: red"></i>
                                </div>
                                <div class="text item">
                                    <p v-if="nextPayment.month">
                                        Payment #: {{ this.nextPayment.month }}
                                    </p>
                                    <p v-if="nextPayment.payment_date">
                                        Date: {{ this.nextPayment.payment_date }}
                                    </p>
                                    <p v-if="nextPayment.amount">
                                        Amount: {{ this.nextPayment.amount }}
                                    </p>
                                </div>
                            </el-card>
                        </div>
                    </div>
                    <div class="block col-md-9">
                        <AssetComments
                            :id="this.form.id"
                        ></AssetComments>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import {responseParse} from '../../../mixins/responseParse'
import {getData} from '../../../mixins/getData'
import AssetComments from "../partials/AssetComments.vue";
import MapMarker from "../../../components/admin/MapMarker.vue";
import ImageModal from "../../../components/admin/ImageModal.vue";
import AssetDetails from "../partials/components/AssetDetails.vue";
import TenantDetails from "../partials/components/TenantDetails.vue";
import ExtraDetails from "../partials/components/ExtraDetails.vue";
import AgreementDetails from "../partials/components/AgreementDetails.vue";
import CurrentValue from "../partials/components/CurrentValue.vue";

export default {
    components: {
        CurrentValue,
        AgreementDetails, ExtraDetails, TenantDetails, AssetDetails, ImageModal, MapMarker, AssetComments
    },
    props: [
        'getSaveDataRoute',
        'id'
    ],
    data() {
        return {
            item: {},
            data: {},
            loading: false,
            routes: {},
            options: {},
            /** Form data*/
            form: {
                id: this.id
            },
            investors: {},
            salesManager: {},
            nextPayment: {},
        }
    },
    created() {
        this.getSaveData();
    },
    computed: {
        whatsappLink() {
            if (this.salesManager.phone) {
                // Construct the WhatsApp URL with the sanitized phone number
                const sanitizedPrefix = this.salesManager.prefix.replace(/\D/g, '');
                const sanitizedPhone = this.salesManager.phone.replace(/\D/g, '');
                return `https://wa.me/${sanitizedPrefix}${sanitizedPhone}`;
            }
            return '';
        }
    },
    methods: {
        /**
         *
         * Get save data.
         *
         * @returns {Promise<void>}
         */
        async getSaveData() {
            this.loading = true;

            await getData({
                method: 'POST',
                url: this.getSaveDataRoute,
                data: this.form
            }).then(response => {
                // Parse response notification.
                responseParse(response, false);

                if (response.status == 200) {
                    // Response data.
                    let data = response.data.data;

                    this.routes = data.routes;
                    this.options = data.options;
                    this.investors = data.investors;
                    this.salesManager = data.salesManager;
                    this.nextPayment = data.nextPayment;
                    if (data.item) {
                        this.form = data.item;
                        if (data.item.files) {
                            this.form.attachments = data.item.files;
                        }
                    }

                    this.form.id = this.id;
                }
                this.loading = false
            })
        },
        formatPrice(amount) {
            if (amount !== undefined && amount !== '') {
                // const value = parseFloat(amount.replace(/,/g, ''));
                if (!isNaN(amount)) {
                    return new Intl.NumberFormat('en-US', {
                        style: 'decimal',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }).format(amount);
                }
            }
            return '0.00';
        }
    }
}

</script>

<style>
.box-card-header {
    font-weight: bold;
}

.text {
    font-size: 14px;
}

.item {
    margin-bottom: 18px;
}

.clearfix:before,
.clearfix:after {
    display: table;
    content: "";
}

.clearfix:after {
    clear: both
}

.fab, .fas {
    margin-left: 10px;
    color: #25D366; /* WhatsApp green */
    cursor: pointer;
}
.phone-container {
    display: flex;
    align-items: center;
}

.whatsapp-icon {
    margin-right: 10px;
    cursor: pointer;
}

.managers-phone {
    font-size: 16px;
}
</style>
