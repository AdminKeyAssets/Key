<template>
    <div>
        <div class="col-xs-12">
            <div class="registration-btn project-title-buttons">
                <div class="content-wrapper">
                    <div class="block col-md-9 asset-details-wrapper form-horizontal form-bordered">
                        <el-form v-loading="loading"
                                 element-loading-text="Loading..."
                                 element-loading-spinner="el-icon-loading"
                                 element-loading-background="rgba(0, 0, 0, 0.0)"
                                 class="">

                            <el-row style="margin-bottom: 30px">
                                <el-card class="box-card">
                                    <div slot="header" class="clearfix main-header">
                                        <span>Project Details</span>
                                    </div>

                                    <el-row class="project-details-wrapper">
                                        <el-col class="project-details" :span="24" :md="16">

                                            <div v-if="form.project_description" class="form-group">
                                                <label class="col-md-2 control-label">Project Description:</label>
                                                <div class="col-md-10 uppercase-medium"
                                                     style="white-space: pre-line; padding-left: 15px">
                                                    {{ form.project_description }}
                                                </div>
                                            </div>

                                            <div v-if="form.project_link" class="form-group">
                                                <label class="col-md-2 control-label">Project Link:</label>
                                                <div class="col-md-10 uppercase-medium">
                                                    <a :href="form.project_link" target="_blank">
                                                        {{ form.project_link }}
                                                    </a>
                                                </div>
                                            </div>

                                            <div v-if="form.city" class="form-group">
                                                <label class="col-md-2 control-label">City:</label>
                                                <div class="col-md-10 uppercase-medium">
                                                    {{ form.city }}
                                                </div>
                                            </div>

                                            <div v-if="form.address" class="form-group">
                                                <label class="col-md-2 control-label">Address:</label>
                                                <div class="col-md-10 uppercase-medium">
                                                    {{ form.address }}
                                                </div>
                                            </div>

                                            <div v-if="form.delivery_date" class="form-group">
                                                <label class="col-md-2 control-label">Delivery Date:</label>
                                                <div class="col-md-10 uppercase-medium">
                                                    {{ form.delivery_date }}
                                                </div>
                                            </div>
                                        </el-col>
                                        <el-col class="project-gallery" :span="24" :md="8">
                                            <div v-if="form.gallery" class="form-group">
                                                <div class="col-md-12 uppercase-medium">
                                                    <ImageBox :initial-main-image="form.gallery[0].image"
                                                              :images="form.gallery"></ImageBox>
                                                </div>
                                            </div>
                                        </el-col>
                                    </el-row>
                                    <el-row>
                                        <div v-if="form.location" class="form-group">
                                            <div v-html="modifiedText">

                                            </div>
                                        </div>
                                    </el-row>
                                </el-card>
                            </el-row>

                            <el-row style="margin-bottom: 30px">
                                <el-card class="box-card asset-details-card">
                                    <div slot="header" class="clearfix main-header">
                                        <span>Asset Details</span>
                                    </div>

                                    <el-row>

                                        <el-row class="row-item" v-if="form.type || form.floor">
                                            <el-col :span="12">
                                                <div v-if="form.type" class="form-group">
                                                    <label class="col-md-4 control-label">Asset Type:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.type }}
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <div v-if="form.floor" class="form-group">
                                                    <label class="col-md-4 control-label">Floor:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.floor }} / {{ form.total_floors }}
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                        <el-row class="row-item" v-if="form.flat_number || form.area">
                                            <el-col :span="12">
                                                <div v-if="form.flat_number" class="form-group">
                                                    <label class="col-md-4 control-label">Flat number:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.flat_number }}
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <div v-if="form.area" class="form-group">
                                                    <label class="col-md-4 control-label">Area (m2):</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.area }}
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                        <el-row class="row-item" v-if="form.condition || form.cadastral_number">
                                            <el-col :span="12">
                                                <div v-if="form.condition" class="form-group">
                                                    <label class="col-md-4 control-label">Delivery Condition:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.condition }}
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <div v-if="form.cadastral_number" class="form-group">
                                                    <label class="col-md-4 control-label">Cadastral Number:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.cadastral_number }}
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="24">
                                                <div v-if="form.delivery_condition_description" class="form-group">
                                                    <label class="control-label" style="padding-left:15px;">Condition
                                                        Description:</label>
                                                    <div class="uppercase-medium"
                                                         style="white-space: pre-line; padding-left: 15px"
                                                         v-html="form.delivery_condition_description">
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>


                                        <el-row class="row-item" v-if="form.flat_plan || form.floor_plan">
                                            <el-col :span="12">
                                                <div v-if="form.flat_plan" class="form-group">
                                                    <label class="col-md-4 control-label">Flat Plan:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        <div v-if="form.flat_plan">
                                                            <ImageModal :image-path="form.flat_plan"
                                                                        :width="100"
                                                                        :height="100"
                                                                        :thumbnail="form.flat_plan"></ImageModal>
                                                        </div>
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <div v-if="form.floor_plan" class="form-group">
                                                    <label class="col-md-4 control-label">Floor Plan:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        <div v-if="form.floor_plan">
                                                            <ImageModal :image-path="form.floor_plan"
                                                                        :width="100"
                                                                        :height="100"
                                                                        :thumbnail="form.floor_plan"></ImageModal>
                                                        </div>
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>


                                        <el-row class="row-item" v-if="form.asset_status || form.investor_id">
                                            <el-col :span="12">
                                                <div v-if="form.asset_status" class="form-group">
                                                    <label class="col-md-4 control-label">Asset Status:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.asset_status }}
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <div v-if="!investorView && form.investor_id" class="form-group">
                                                    <label class="col-md-4 control-label">Investor: </label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ selectedInvestor.name }} {{ selectedInvestor.surname }}
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                    </el-row>

                                </el-card>
                            </el-row>

                            <el-row style="margin-bottom: 30px" v-if="form.tenant">
                                <el-card class="box-card" v-if="form.asset_status === 'Rented'">
                                    <div slot="header" class="clearfix main-header">
                                        <span>Tenant Details</span>
                                    </div>
                                    <el-row>
                                        <el-row class="row-item"
                                                v-if="(form.tenant.name || form.tenant.surname) || form.tenant.id_number">
                                            <el-col :span="12">
                                                <div v-if="form.tenant.name || form.tenant.surname"
                                                     class="form-group">
                                                    <label class="col-md-4 control-label">Tenant Name:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.tenant.name }} {{ form.tenant.surname }}
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <div v-if="form.tenant.id_number" class="form-group">
                                                    <label class="col-md-4 control-label">ID Number:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.tenant.id_number }}
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                        <el-row class="row-item"
                                                v-if="form.tenant.citizenship || form.tenant.email">
                                            <el-col :span="12">
                                                <div v-if="form.tenant.citizenship" class="form-group">
                                                    <label class="col-md-4 control-label">Citizenship:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.tenant.citizenship }}
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <div v-if="form.tenant.email" class="form-group">
                                                    <label class="col-md-4 control-label">Email:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.tenant.email }}
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                        <el-row class="row-item"
                                                v-if="form.tenant.phone || form.tenant.agreement_date">
                                            <el-col :span="12">
                                                <div v-if="form.tenant.phone" class="form-group">
                                                    <label class="col-md-4 control-label">Phone:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.tenant.prefix + form.tenant.phone }}
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <div v-if="form.tenant.agreement_date" class="form-group">
                                                    <label class="col-md-4 control-label">Agreement Date:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.tenant.agreement_date }}
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                        <el-row class="row-item"
                                                v-if="form.tenant.passport">
                                            <el-col :span="12">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Passport:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        <div v-if="form.tenant.passport">
                                                            <ImageModal :image-path="form.tenant.passport"
                                                                        :width="100"
                                                                        :height="100"
                                                                        :thumbnail="form.tenant.passport"></ImageModal>
                                                        </div>
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                        <el-row class="row-item"
                                                v-if="form.tenant.agreement_term || form.tenant.monthly_rent">
                                            <el-col :span="12">
                                                <div v-if="form.tenant.agreement_term" class="form-group">
                                                    <label class="col-md-4 control-label">Agreement Term:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.tenant.agreement_term }} Month(s)
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <div v-if="form.tenant.monthly_rent" class="form-group">
                                                    <label class="col-md-4 control-label">Monthly Rent:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ formatPrice(form.tenant.monthly_rent) }}
                                                        {{ form.tenant.currency }}
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                    </el-row>

                                    <el-row style="margin-top: 20px; " class="payments-wrapper row-item">
                                        <el-col :span="24" :md="11">
                                            <div v-if="form.rentals.length">
                                                <div class="payments-schedule-title-wrapper">
                                                    <div class="rentals-schedule-heading"
                                                         style="text-align: center; max-width: 500px">
                                                        <h4>Rentals Schedule</h4>
                                                    </div>
                                                    <el-button
                                                        icon="el-icon-document"
                                                        style="margin-bottom: 2rem; margin-right: 3rem;"
                                                        type="secondary"
                                                        class="pull-right"
                                                        @click="exportRentSchedule">Export Rents
                                                    </el-button>
                                                </div>
                                                <el-table border :data="form.rentals" style="width: 100%">
                                                    <el-table-column prop="payment_date" label="Payment Date"/>
                                                    <el-table-column prop="amount" label="Amount">
                                                        <template slot-scope="scope">
                                                            {{
                                                                scope.row.status ? formatPrice(scope.row.amount) : formatPrice(scope.row.left_amount)
                                                            }} {{ form.currency }}
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="status" label="Status">
                                                        <template slot-scope="scope">
                                                            <i v-if="scope.row.status" class="el-icon-check"
                                                               style="color: green"></i>
                                                            <i v-else class="el-icon-close" style="color: red"></i>
                                                        </template>
                                                    </el-table-column>
                                                </el-table>
                                            </div>
                                        </el-col>
                                        <el-col :span="24" :md="11" class="payments-history-wrapper-col">
                                            <div
                                                v-if="form.rental_payments_histories && form.rental_payments_histories.length">
                                                <div class="payments-schedule-title-wrapper">
                                                    <div class="payments-history-heading"
                                                         style="text-align: center; max-width: 500px">
                                                        <h4>Payments History</h4>
                                                    </div>
                                                    <el-button
                                                        icon="el-icon-document"
                                                        style="margin-bottom: 2rem; margin-right: 3rem;"
                                                        type="secondary"
                                                        class="pull-right"
                                                        @click="exportRentalPayments">Export Payments
                                                    </el-button>
                                                </div>
                                                <el-table border :data="form.rental_payments_histories"
                                                          style="width: 100%">
                                                    <el-table-column prop="date" label="Payment Date"/>
                                                    <el-table-column prop="amount" label="Amount">
                                                        <template slot-scope="scope">
                                                            {{ formatPrice(scope.row.amount) }} {{ form.currency }}
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="attachment" label="Attachment">
                                                        <template slot-scope="scope">
                                                            <a :href="scope.row.attachment" v-if="scope.row.attachment"
                                                               target="_blank">View
                                                                {{ getFilename(scope.row.attachment) }}</a>
                                                        </template>
                                                    </el-table-column>
                                                </el-table>
                                            </div>
                                        </el-col>
                                    </el-row>
                                </el-card>
                            </el-row>

                            <el-row style="margin-bottom: 30px" v-if="form.extraDetails && form.extraDetails.length">
                                <el-card class="box-card extra-details-card" v-if="form.asset_status === 'Rented'">
                                    <div slot="header" class="clearfix main-header">
                                        <span>Extra Details</span>
                                    </div>
                                    <el-row class="row-item">
                                        <div>
                                            <el-table border :data="form.extraDetails"
                                                      style="width: 100%">
                                                <el-table-column prop="key"/>
                                                <el-table-column prop="value"/>
                                                <el-table-column prop="attachment">
                                                    <template slot-scope="scope">
                                                        <a :href="scope.row.attachment" v-if="scope.row.attachment"
                                                           target="_blank">View
                                                            {{ getFilename(scope.row.attachment) }}</a>
                                                    </template>
                                                </el-table-column>
                                            </el-table>
                                        </div>
                                    </el-row>
                                </el-card>
                            </el-row>

                            <el-row style="margin-bottom: 30px">
                                <el-card class="box-card agreement-details-card"
                                         :class="{ 'hidden-body': !showAgreementDetails }">

                                    <div slot="header" v-if="form.agreement_status === 'Complete'"
                                         class="clearfix main-header"
                                         @click="showAgreementDetails = !showAgreementDetails" style="cursor: pointer;">
                                        <div style="width: 98%">
                                            <span>Agreement Details</span>
                                        </div>
                                        <div style="width: 2%">
                                            <i v-if="!showAgreementDetails" class="el-icon-caret-right"></i>
                                            <i v-else class="el-icon-caret-bottom"></i>
                                        </div>
                                    </div>
                                    <div slot="header" v-else class="clearfix main-header">
                                        <span>Agreement Details</span>
                                    </div>
                                    <el-row>
                                        <el-row class="row-item">
                                            <el-col :span="12">
                                                <div v-if="form.agreement_date" class="form-group">
                                                    <label class="col-md-4 control-label">Agreement Date:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.agreement_date }}
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <div v-if="form.agreements && form.agreements.length"
                                                     class="form-group">
                                                    <label class="col-md-4 control-label">Agreement(s):</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        <div v-for="agreement in form.agreements">
                                                            <p v-if="agreement.attachment && agreement.name">
                                                                <a :href="agreement.attachment"
                                                                   target="_blank">{{ agreement.name }}</a></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                        <el-row class="row-item" v-if="form.price || form.total_price">
                                            <el-col :span="12">
                                                <div v-if="form.price" class="form-group">
                                                    <label class="col-md-4 control-label">M2 Price:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ formatPrice(form.price) }} {{ form.currency }}
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <div v-if="form.total_price" class="form-group">
                                                    <label class="col-md-4 control-label">Total Price:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ formatPrice(form.total_price) }} {{ form.currency }}
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                        <el-row class="row-item" v-if="form.agreement_status">
                                            <el-col :span="12">
                                                <div v-if="form.agreement_status" class="form-group">
                                                    <label class="col-md-4 control-label">Agreement Status:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.agreement_status }}
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <template v-if="form.agreement_status === 'Complete'">
                                                    <div v-if="form.ownership_certificate" class="form-group">
                                                        <label class="col-md-4 control-label">Ownership
                                                            Certificate:</label>
                                                        <div class="col-md-6 uppercase-medium">
                                                            <div v-if="form.ownership_certificate">
                                                                <p v-if="form.ownership_certificate"><a
                                                                    :href="form.ownership_certificate"
                                                                    v-if="form.ownership_certificate" target="_blank">View
                                                                    {{ getFilename(form.ownership_certificate) }}</a>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </el-col>
                                        </el-row>

                                    </el-row>

                                    <template>
                                        <el-row class="row-item">
                                            <el-col :span="12">
                                                <div v-if="form.first_payment_date" class="form-group">
                                                    <label class="col-md-4 control-label">First Payment Date:</label>
                                                    <div class="col-md-8 uppercase-medium">
                                                        {{ form.first_payment_date }}
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <div v-if="form.period" class="form-group">
                                                    <label class="col-md-4 control-label">Period:</label>
                                                    <div class="col-md-8 uppercase-medium">
                                                        {{ form.period }} Month(s)
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                        <el-row style="margin-top: 20px;" class="payments-wrapper row-item">
                                            <el-col :span="24" :md="11">
                                                <div v-if="form.payments && form.payments.length">
                                                    <div class="payments-schedule-title-wrapper">
                                                        <div class="payments-schedule-heading"
                                                             style="text-align: center; max-width: 500px">
                                                            <h4>Payments Schedule</h4>
                                                        </div>
                                                        <el-button
                                                            icon="el-icon-document"
                                                            style="margin-bottom: 2rem; margin-right: 3rem;"
                                                            type="secondary"
                                                            class="pull-right"
                                                            @click="exportPaymentSchedule">Export Schedule
                                                        </el-button>
                                                    </div>
                                                    <el-table border :data="form.payments" style="width: 100%">
                                                        <el-table-column prop="payment_date" label="Payment Date"/>
                                                        <el-table-column prop="amount" label="Amount">
                                                            <template slot-scope="scope">
                                                                {{
                                                                    scope.row.status ? formatPrice(scope.row.amount) : formatPrice(scope.row.left_amount)
                                                                }} {{ form.currency }}
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column prop="status" label="Status">
                                                            <template slot-scope="scope">
                                                                <i v-if="scope.row.status" class="el-icon-check"
                                                                   style="color: green"></i>
                                                                <i v-else class="el-icon-close" style="color: red"></i>
                                                            </template>
                                                        </el-table-column>
                                                    </el-table>
                                                </div>
                                            </el-col>

                                            <el-col :span="24" :md="11" class="payments-history-wrapper-col">
                                                <div v-if="form.payments_histories && form.payments_histories.length">
                                                    <div class="payments-schedule-title-wrapper">
                                                        <div class="payments-history-heading"
                                                             style="text-align: center; max-width: 500px">
                                                            <h4>Payments History</h4>
                                                        </div>
                                                        <el-button
                                                            icon="el-icon-document"
                                                            style="margin-bottom: 2rem; margin-right: 3rem;"
                                                            type="secondary"
                                                            class="pull-right"
                                                            @click="exportPayments">Export Payments
                                                        </el-button>
                                                    </div>
                                                    <el-table border :data="form.payments_histories"
                                                              style="width: 100%">
                                                        <el-table-column prop="date" label="Payment Date"/>
                                                        <el-table-column prop="amount" label="Amount">
                                                            <template slot-scope="scope">
                                                                {{ formatPrice(scope.row.amount) }} {{ form.currency }}
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column prop="attachment" label="Attachment">
                                                            <template slot-scope="scope">
                                                                <a :href="scope.row.attachment"
                                                                   v-if="scope.row.attachment" target="_blank">View
                                                                    {{ getFilename(scope.row.attachment) }}</a>
                                                            </template>
                                                        </el-table-column>
                                                    </el-table>
                                                </div>
                                            </el-col>
                                        </el-row>
                                    </template>

                                </el-card>
                            </el-row>

                            <el-row style="margin-bottom: 30px" v-if="form.currentValues && form.currentValues.length">
                                <el-card class="box-card current-value-box">
                                    <div slot="header" class="clearfix main-header">
                                        <span>Current Value</span>
                                    </div>

                                    <el-row v-if="form.current_value">
                                        <el-col :span="24">
                                            <div>
                                                <el-table :data="form.currentValues" style="width: 100%">
                                                    <el-table-column prop="value" label="Value">
                                                        <template slot-scope="scope">
                                                            {{ formatPrice(scope.row.value) }} {{ scope.row.currency }}
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="date" label="Date"/>
                                                    <el-table-column prop="attachment" label="Attachment"
                                                                     width="fit-content">
                                                        <template slot-scope="scope">
                                                            <a v-if="scope.row.attachment" :href="scope.row.attachment"
                                                               target="_blank">{{
                                                                    getFilename(scope.row.attachment)
                                                                }}</a>
                                                        </template>
                                                    </el-table-column>
                                                </el-table>
                                            </div>
                                        </el-col>
                                    </el-row>
                                </el-card>
                            </el-row>

                            <el-row style="margin-bottom: 30px">
                                <el-card class="box-card">
                                    <div slot="header" class="clearfix main-header">
                                        <span>Comments</span>
                                    </div>
                                    <AssetComments
                                        :id="this.form.id"
                                        :user-id="this.userId"
                                        :is-admin="this.isAdmin"
                                        :investor-view="this.investorView"
                                    ></AssetComments>
                                </el-card>
                            </el-row>
                        </el-form>
                    </div>
                    <div class="block col-md-3 asset-manager-details">
                        <div v-loading="loading"
                             class="form-horizontal form-bordered">

                            <el-card class="box-card asset-manager" v-if="salesManager">
                                <div slot="header" class="clearfix box-card-header">
                                    <el-row style="display: flex; align-items: center;">
                                        <el-col :span="6">
                                            <ImageModal v-if="salesManager.profile_picture"
                                                        :thumbnail="salesManager.profile_picture"
                                                        :image-path="salesManager.profile_picture"
                                                        :width="50"
                                                        :height="50"
                                                        :rounded="true"></ImageModal>
                                        </el-col>
                                        <el-col :span="18">
                                            <h4>Asset Manager</h4>
                                        </el-col>
                                    </el-row>
                                </div>
                                <div class="text item">
                                    <p v-if="salesManager.name || salesManager.surname">
                                        {{ salesManager.name }} {{ salesManager.surname }}
                                    </p>
                                    <p v-if="salesManager.phone">
                                        <a :href="whatsappLink" target="_blank" class="phone-container">
                                            <span class="whatsapp-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="24px"
                                                     height="24px"
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
                                        <a :href="'mailto:' + salesManager.email" class="email-container">
                                            <span class="email-icon">
                                                <svg width="25" height="16" viewBox="0 0 25 16" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                        <g clip-path="url(#clip0_94_23630)">
                                                        <path
                                                            d="M24.9986 15.454V0.542549C24.9986 0.241901 24.7524 0 24.4463 0H24.2107L13.1934 7.78575L23.5775 15.9965H24.4463C24.7524 15.9965 24.9986 15.7546 24.9986 15.454Z"
                                                            fill="#FF9100"/>
                                                        <path
                                                            d="M0 15.454V0.542549C0 0.241901 0.246236 0 0.552272 0H0.787955L11.8053 7.78575L1.42465 16H0.552272C0.246236 16 0 15.7546 0 15.454Z"
                                                            fill="#F7BA0F"/>
                                                        <g style="mix-blend-mode:multiply">
                                                        <path
                                                            d="M24.7389 15.25L12.7296 5.81934L0.0484371 15.2224C0.0484371 15.2224 -0.102822 15.4919 0.11879 15.561L12.7261 6.61761L24.2112 15.3088L24.7917 15.6474C24.9957 15.6474 24.9288 15.4885 24.7389 15.2465V15.25Z"
                                                            fill="#FF9100"/>
                                                        </g>
                                                        <path
                                                            d="M24.5251 16.0002H0.552272C0.246236 16.0002 0 15.7548 0 15.4542V15.4404L12.8113 6.50732L25 15.7341C24.905 15.8931 24.7291 16.0002 24.5251 16.0002Z"
                                                            fill="#FF9100"/>
                                                        <g style="mix-blend-mode:multiply">
                                                        <path
                                                            d="M24.7951 0.300781L24.2147 0.639442L12.7295 9.33404L0.122231 0.39063C-0.102899 0.459745 0.0518782 0.729291 0.0518782 0.729291L12.7295 10.1323L24.7388 0.701645C24.9288 0.4632 24.9956 0.300781 24.7916 0.300781H24.7951Z"
                                                            fill="#FF9100"/>
                                                        </g>
                                                        <path
                                                            d="M24.5251 0H0.552272C0.246236 0 0 0.241901 0 0.542549V0.556372L12.8113 9.49287L25 0.266091C24.905 0.107127 24.7291 0 24.5251 0Z"
                                                            fill="#F7BA0F"/>
                                                        </g>
                                                        <defs>
                                                        <clipPath id="clip0_94_23630">
                                                        <rect width="25" height="16" fill="white"/>
                                                        </clipPath>
                                                        </defs>
                                                    </svg>
                                            </span>
                                            <span class="managers-email">
                                                {{ salesManager.email }}
                                            </span>
                                        </a>
                                    </p>
                                </div>
                            </el-card>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>

import {responseParse} from '../../../mixins/responseParse'
import {getData} from '../../../mixins/getData'
import MapMarker from "../../../components/admin/MapMarker.vue";
import ImageModal from "../../../components/admin/ImageModal.vue";
import ImageBox from "../../../components/admin/ImageBox.vue";
import AssetComments from "../partials/AssetComments.vue";

export default {
    components: {
        ImageBox, ImageModal, MapMarker, AssetComments
    },


    props: {
        id: {
            type: [String, Number],
            required: true
        },
        userId: {
            type: [String, Number],
            required: true
        },
        getSaveDataRoute: {
            type: [String, Number],
            required: true
        },
        isAdmin: {
            type: [Boolean, Number],
            default: false
        },
        investorView: {
            type: [Boolean, Number],
            default: false
        }
    },

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
            showAgreementDetails: true
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
        },
        modifiedText() {
            return this.replaceWidth(this.form.location);
        },
        selectedInvestor() {
            return this.investors.find(investor => investor.id === this.form.investor_id);
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
                        if (this.form.agreement_status === 'Complete'){
                            this.showAgreementDetails = false;
                        }
                    }

                    this.form.id = this.id;
                }
                this.loading = false
            })
        },
        formatPrice(amount) {
            //Do not Format
            if (amount !== undefined && amount !== '') {
                // const value = parseFloat(amount.replace(/,/g, ''));
                if (!isNaN(amount)) {
                    return new Intl.NumberFormat('en-US', {
                        style: 'decimal',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0,
                    }).format(amount);
                }
            }
            return '0.00';
        },

        async exportRentSchedule() {
            try {
                const response = await axios.get(`/assets/${this.id}/rental/export`, {
                    params: this.filters,
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'rents_schedule.xlsx');
                document.body.appendChild(link);
                link.click();
            } catch (error) {
                console.error('Error exporting rents:', error);
            }
        },

        async exportPaymentSchedule() {
            try {
                const response = await axios.get(`/assets/${this.id}/payments/export`, {
                    params: this.filters,
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'payments_schedule.xlsx');
                document.body.appendChild(link);
                link.click();
            } catch (error) {
                console.error('Error exporting payments:', error);
            }
        },

        async exportRentalPayments() {
            try {
                const response = await axios.get(`/assets/${this.id}/rental-payments-history/export`, {
                    params: this.filters,
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'rental_payments.xlsx');
                document.body.appendChild(link);
                link.click();
            } catch (error) {
                console.error('Error exporting payments:', error);
            }
        },

        async exportPayments() {
            try {
                const response = await axios.get(`/assets/${this.id}/payments-history/export`, {
                    params: this.filters,
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'payments.xlsx');
                document.body.appendChild(link);
                link.click();
            } catch (error) {
                console.error('Error exporting payments:', error);
            }
        },

        getFilename(path) {
            return path.split('/').pop();
        },

        replaceWidth(text) {
            return text.replace(/width="600"/g, 'width="100%"');
        }
    }
}

</script>

<style>

.agreement-details-card .clearfix.main-header {
    padding-left: 15px;
    font-size: 16px;
    font-weight: bold;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
}

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

.phone-container, .email-container {
    display: flex;
    align-items: center;
}

.whatsapp-icon, .email-icon {
    margin-right: 10px;
    cursor: pointer;
}

.clearfix.main-header {
    padding-left: 15px;
    font-size: 16px;
    font-weight: bold;
}

@media (min-width: 769px) {
    .payments-wrapper {
        display: flex;
        justify-content: space-between;
    }

    .content-wrapper {
        position: relative;
    }

    .block.col-md-3.asset-manager-details {
        position: fixed;
        max-width: 21%;
        right: 25px;
    }
}

@media (min-width: 769px) {
    .el-table th > .cell, .el-table td > .cell {
        font-size: 13px !important;
        text-align: center;
    }
}

@media (max-width: 768px) {
    .project-details-wrapper {
        display: flex;
        flex-direction: column;
    }

    .el-table th > .cell, .el-table td > .cell {
        font-size: 11px !important;
        text-align: center;
    }

    .project-details-wrapper .project-gallery {
        order: 1;
    }

    .project-details-wrapper .project-details {
        order: 2;
    }
}

.dashed.el-row {
    border-bottom: 1px dashed #eaedf1;
}

.form-group {
    border-bottom-color: transparent !important;
}

.el-table .cell {
    padding-right: 0 !important;
}
</style>
