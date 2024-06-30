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
                                        Name: {{ this.salesManager.name }} {{ this.salesManager.surname }}
                                    </p>
                                    <p v-if="salesManager.phone">
                                        Phone: {{ this.salesManager.prefix }}{{ this.salesManager.phone }}
                                    </p>
                                    <p v-if="salesManager.email">
                                        <a :href="'mailto: ' + salesManager.email">
                                            Email: {{ this.salesManager.email }}
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

</style>
