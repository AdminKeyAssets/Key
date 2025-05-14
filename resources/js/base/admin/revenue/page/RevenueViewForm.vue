<template>
    <div>
        <div class="col-xs-12">
            <div class="registration-btn project-title-buttons">
                <div class="content-wrapper">
                    <div class="block col-md-9 revenue-wrapper form-horizontal form-bordered">
                        <el-form v-loading="loading"
                                 element-loading-text="Loading..."
                                 element-loading-spinner="el-icon-loading"
                                 element-loading-background="rgba(0, 0, 0, 0.0)"
                                 class="">

                            <!-- Sold Asset Section -->
                            <el-row style="margin-bottom: 30px" v-if="form.sale_status === 'sold'">
                                <el-card class="box-card agreement-details-card"
                                         :class="{ 'hidden-body': !showAgreementDetails }">
                                    <div slot="header"
                                         class="clearfix main-header"
                                         @click="showAgreementDetails = !showAgreementDetails" style="cursor: pointer;">
                                        <div style="width: 98%">
                                            <span>Sold - </span> <span>{{ form.sale_date }}</span>
                                        </div>
                                        <div style="width: 2%">
                                            <i v-if="!showAgreementDetails" class="el-icon-caret-right"></i>
                                            <i v-else class="el-icon-caret-bottom"></i>
                                        </div>
                                    </div>
                                    <el-row>
                                        <el-row class="row-item" v-if="form.agreement_date || form.sale_date">
                                            <el-col :span="12">
                                                <div v-if="form.agreement_date" class="form-group">
                                                    <label class="col-md-4 control-label">Agreement Date:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.agreement_date }}
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <div v-if="form.sale_date" class="form-group">
                                                    <label class="col-md-4 control-label">Sale Date:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.sale_date }}
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                        <el-row class="row-item">
                                            <el-col :span="12">
                                                <div v-if="form.agreements && form.agreements.length"
                                                     class="form-group">
                                                    <label class="col-md-4 control-label">Agreement(s):</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        <div v-for="agreement in form.agreements">
                                                            <p v-if="agreement.attachment && agreement.name">
                                                                <a :href="agreement.attachment"
                                                                   target="_blank">{{ agreement.name }}</a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <div v-if="form.purchaser" class="form-group">
                                                    <label class="col-md-4 control-label">Purchaser:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.purchaser }}
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                        <el-row class="row-item">
                                            <el-col :span="12">
                                                <div v-if="form.total_price" class="form-group">
                                                    <label class="col-md-4 control-label">Total Price:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.total_price }}
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <div v-if="form.sale_agreement" class="form-group">
                                                    <label class="col-md-4 control-label">Sale Agreement:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        <div>
                                                            <p v-if="form.sale_agreement">
                                                                <a :href="form.sale_agreement"
                                                                   target="_blank">View Agreement</a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                        <el-row class="row-item">
                                            <el-col :span="12">
                                                <div v-if="form.paid" class="form-group">
                                                    <label class="col-md-4 control-label">Paid:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ form.paid }}
                                                    </div>
                                                </div>
                                            </el-col>
                                            <el-col :span="12">
                                                <div v-if="form.sale_price" class="form-group">
                                                    <label class="col-md-4 control-label">Sale Price:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ formatPrice(form.sale_price) }}$
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                        <template>
                                            <div class="table-responsive renovation-table">
                                                <table class="table table-vcenter table-striped">
                                                    <thead>
                                                    <tr>
                                                        <th>Renovation</th>
                                                        <th>Other Investment</th>
                                                        <th>Total Investment</th>
                                                        <th>Capital Gain</th>
                                                        <th>Rent</th>
                                                        <th>Net Cash Balance</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr>
                                                        <td>{{ formatPrice(form.renovation, 0, ".", ",") }}$</td>
                                                        <td>{{ formatPrice(form.other_investment, 0, ".", ",") }}$</td>
                                                        <td>{{ formatPrice(form.total_investment, 0, ".", ",") }}$</td>
                                                        <td>{{ formatPrice(form.capital_gain, 0, ".", ",") }}$</td>
                                                        <td>{{ formatPrice(form.rent, 0, ".", ",") }}$</td>
                                                        <td>{{ formatPrice(form.net_cash_balance, 0, ".", ",") }}$</td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </template>
                                    </el-row>

                                    <el-row v-if="isAdmin && form.sale_status === 'sold'">
                                        <div class="sale-actions">
                                            <span class="sale-delete" @click="deleteSale(form.id)">
                                                Restore
                                            </span>
                                            <div style="display: inline-block">
                                                <span class="sale-delete" @click="openModal">
                                                    Edit
                                                </span>
                                                <el-dialog
                                                    :visible.sync="visible"
                                                    title="Sell Asset"
                                                    width="500px"
                                                    @close="resetForm"
                                                >
                                                    <el-form ref="saleForm" :model="form"
                                                             label-width="120px">
                                                        <el-form-item label="Sale Date" prop="sale_date">
                                                            <el-date-picker
                                                                v-model="form.sale_date"
                                                                format="yyyy/MM/dd"
                                                                value-format="yyyy/MM/dd"
                                                                type="date"
                                                                placeholder="Pick a sale date">
                                                            </el-date-picker>
                                                        </el-form-item>
                                                        <el-form-item label="Sale Price" prop="sale_price">
                                                            <el-input v-model="form.sale_price" type="number"
                                                                      placeholder="Enter sale price">
                                                            </el-input>
                                                        </el-form-item>
                                                        <el-form-item label="Purchaser" prop="purchaser">
                                                            <el-input v-model="form.purchaser"
                                                                      placeholder="Enter purchaser name">
                                                            </el-input>
                                                        </el-form-item>
                                                        <div class="form-group dashed">
                                                            <label class="col-md-4 control-label">Attachment:</label>
                                                            <div class="col-md-6 uppercase-medium">
                                                                <p v-if="form.sale_agreement">
                                                                    File: <a :href="form.sale_agreement" target="_blank">
                                                                    View Attachment</a>
                                                                    <el-button type="danger" icon="el-icon-delete"
                                                                               size="small"
                                                                               @click="removeFile">
                                                                    </el-button>
                                                                </p>
                                                                <input v-else type="file" class="form-control"
                                                                       @change="onFileChange">
                                                            </div>
                                                        </div>
                                                    </el-form>
                                                    <span slot="footer" class="dialog-footer">
                                                        <el-button @click="visible = false">Cancel</el-button>
                                                        <el-button type="primary" :disabled="loading" @click="save">
                                                            {{ form.id ? 'Update' : 'Save' }}
                                                        </el-button>
                                                    </span>
                                                </el-dialog>
                                            </div>
                                        </div>
                                    </el-row>
                                </el-card>
                            </el-row>

                            <!-- Rentals Section -->
                            <el-row style="margin-bottom: 30px" v-if="tenants && tenants.length">
                                <el-card class="box-card" :class="{ 'hidden-body': !showRentals }">
                                    <div slot="header" class="clearfix main-header"
                                         @click="showRentals = !showRentals" style="cursor: pointer;">
                                        <div style="width: 98%">
                                            <span>Rentals</span>
                                        </div>
                                        <div style="width: 2%">
                                            <i v-if="!showRentals" class="el-icon-caret-right"></i>
                                            <i v-else class="el-icon-caret-bottom"></i>
                                        </div>
                                    </div>
                                    <!-- Export Rentals Button -->
                                    <div style="padding: 10px;">
                                        <el-button icon="el-icon-document" type="secondary" @click="exportRentals">
                                            Export Rentals
                                        </el-button>
                                    </div>
                                    <el-row v-if="showRentals" style="margin-top: 20px;" class="payments-wrapper row-item">
                                        <el-col :span="24" class="payments-history-wrapper-col">
                                            <el-row style="margin-bottom: 30px" v-for="tenant in tenants" :key="tenant.id">
                                                <el-card class="box-card"
                                                         :class="{ 'hidden-body': !tenant.showDetails && !tenant.status }">
                                                    <!-- Card Header -->
                                                    <div slot="header" class="clearfix main-header"
                                                         @click="tenant.showDetails = !tenant.showDetails"
                                                         style="cursor: pointer;">
                                                        <div style="width: 98%">
                                                            <span>{{ tenant.name }} {{ tenant.surname }}</span>
                                                            <span>
                                                                <span>{{ tenant.agreement_date }}</span>
                                                                <span v-if="tenant.rent_end_date"> - {{ tenant.rent_end_date }}</span>
                                                                <span v-if="tenant.rentals && tenant.rentals.length && !tenant.status">
                                                                    - {{ tenant.rentals.slice(-1)[0].payment_date }}
                                                                </span>
                                                            </span>
                                                        </div>
                                                        <div style="width: 2%">
                                                            <i v-if="!tenant.showDetails" class="el-icon-caret-right"></i>
                                                            <i v-else class="el-icon-caret-bottom"></i>
                                                        </div>
                                                    </div>
                                                    <!-- Card Content -->
                                                    <el-row v-if="tenant.showDetails">
                                                        <el-row class="row-item" v-if="tenant.id_number || tenant.citizenship">
                                                            <el-col :span="12">
                                                                <div class="form-group">
                                                                    <label class="col-md-4 control-label">ID Number:</label>
                                                                    <div class="col-md-6 uppercase-medium">
                                                                        {{ tenant.id_number }}
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                            <el-col :span="12">
                                                                <div v-if="tenant.citizenship" class="form-group">
                                                                    <label class="col-md-4 control-label">Citizenship:</label>
                                                                    <div class="col-md-6 uppercase-medium">
                                                                        {{ tenant.citizenship }}
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                        </el-row>
                                                        <el-row class="row-item" v-if="tenant.phone || tenant.email">
                                                            <el-col :span="12">
                                                                <div v-if="tenant.phone" class="form-group">
                                                                    <label class="col-md-4 control-label">Phone:</label>
                                                                    <div class="col-md-6 uppercase-medium">
                                                                        {{ tenant.prefix + tenant.phone }}
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                            <el-col :span="12">
                                                                <div v-if="tenant.email" class="form-group">
                                                                    <label class="col-md-4 control-label">Email:</label>
                                                                    <div class="col-md-6 uppercase-medium">
                                                                        {{ tenant.email }}
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                        </el-row>
                                                        <el-row class="row-item" v-if="tenant.agreement_term || tenant.monthly_rent">
                                                            <el-col :span="12">
                                                                <div v-if="tenant.agreement_term" class="form-group">
                                                                    <label class="col-md-4 control-label">Agreement Term:</label>
                                                                    <div class="col-md-6 uppercase-medium">
                                                                        {{ tenant.agreement_term }} Month(s)
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                            <el-col :span="12">
                                                                <div v-if="tenant.monthly_rent" class="form-group">
                                                                    <label class="col-md-4 control-label">Monthly Rent:</label>
                                                                    <div class="col-md-6 uppercase-medium">
                                                                        {{ formatPrice(tenant.monthly_rent) }}$
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                        </el-row>
                                                        <el-row class="row-item" v-if="tenant.agreement_date || tenant.rent_end_date">
                                                            <el-col :span="12">
                                                                <div v-if="tenant.agreement_date" class="form-group">
                                                                    <label class="col-md-4 control-label">Agreement Date:</label>
                                                                    <div class="col-md-6 uppercase-medium">
                                                                        {{ tenant.agreement_date }}
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                            <el-col :span="12">
                                                                <div v-if="tenant.rent_end_date" class="form-group">
                                                                    <label class="col-md-4 control-label">Rent End Date:</label>
                                                                    <div class="col-md-6 uppercase-medium">
                                                                        {{ tenant.rent_end_date }}
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                        </el-row>
                                                        <el-row class="row-item" v-if="tenant.passport || tenant.rent_agreement">
                                                            <el-col :span="12" v-if="tenant.rent_agreement" >
                                                                <div class="form-group">
                                                                    <label class="col-md-4 control-label">Rent Agreement:</label>
                                                                    <div class="col-md-6 uppercase-medium">
                                                                        <div>
                                                                            <p>
                                                                                <a :href="tenant.rent_agreement"
                                                                                   target="_blank">
                                                                                    View Agreement
                                                                                </a>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                            <el-col :span="12" v-if="tenant.passport">
                                                                <div class="form-group">
                                                                    <label class="col-md-4 control-label">Passport:</label>
                                                                    <div class="col-md-6 uppercase-medium">
                                                                        <div>
                                                                            <ImageModal :image-path="tenant.passport"
                                                                                        :width="100"
                                                                                        :height="100"
                                                                                        :thumbnail="tenant.passport">
                                                                            </ImageModal>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                        </el-row>
                                                        <el-row class="row-item" v-if="tenant.rental_payments_amount_sum || tenant.investments_during_rent">
                                                            <el-col v-if="tenant.rental_payments_amount_sum" :span="12">
                                                                <div class="form-group">
                                                                    <label class="col-md-4 control-label">Total rent:</label>
                                                                    <div class="col-md-6 uppercase-medium">
                                                                        {{ formatPrice(tenant.rental_payments_amount_sum) }}
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                            <el-col :span="12" v-if="tenant.investments_during_rent">
                                                                <div  class="form-group">
                                                                    <label class="col-md-4 control-label">Total Investment:</label>
                                                                    <div class="col-md-6 uppercase-medium">
                                                                        {{ formatPrice(tenant.investments_during_rent) }}
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                        </el-row>
                                                        <el-row class="row-item" v-if="tenant.representative || tenant.net_cash_balance">
                                                            <el-col :span="12" v-if="tenant.net_cash_balance">
                                                                <div class="form-group">
                                                                    <label class="col-md-4 control-label">Net Cash Balance:</label>
                                                                    <div class="col-md-6 uppercase-medium">
                                                                        {{ formatPrice(tenant.net_cash_balance) }}
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                            <el-col :span="12" v-if="tenant.representative">
                                                                <div class="form-group">
                                                                    <label class="col-md-4 control-label">Representative:</label>
                                                                    <div class="col-md-6 uppercase-medium">
                                                                        {{ tenant.representative }}
                                                                    </div>
                                                                </div>
                                                            </el-col>
                                                        </el-row>
                                                    </el-row>
                                                    <el-row v-if="tenant.showDetails" style="margin-top: 20px;" class="payments-wrapper row-item">
                                                        <el-col :span="24" :md="11">
                                                            <div>
                                                                <div class="payments-schedule-title-wrapper">
                                                                    <div class="rentals-schedule-heading"
                                                                         style="text-align: center; max-width: 500px">
                                                                        <h4>Rentals Schedule</h4>
                                                                    </div>
                                                                </div>
                                                                <el-table border :data="tenant.rentals" style="width: 100%">
                                                                    <el-table-column prop="payment_date" label="Payment Date"/>
                                                                    <el-table-column prop="amount" label="Amount">
                                                                        <template slot-scope="scope">
                                                                            {{ formatPrice(scope.row.amount) }}$
                                                                        </template>
                                                                    </el-table-column>
                                                                </el-table>
                                                            </div>
                                                        </el-col>
                                                        <el-col :span="24" :md="11" class="payments-history-wrapper-col">
                                                            <div v-if="tenant.rental_payments">
                                                                <div class="payments-schedule-title-wrapper">
                                                                    <div class="payments-history-heading"
                                                                         style="text-align: center; max-width: 500px">
                                                                        <h4>Payments History</h4>
                                                                    </div>
                                                                </div>
                                                                <el-table border :data="tenant.rental_payments" style="width: 100%">
                                                                    <el-table-column prop="date" label="Payment Date"/>
                                                                    <el-table-column prop="amount" label="Amount">
                                                                        <template slot-scope="scope">
                                                                            {{ formatPrice(scope.row.amount) }}$
                                                                        </template>
                                                                    </el-table-column>
                                                                    <el-table-column prop="attachment" label="Attachment">
                                                                        <template slot-scope="scope">
                                                                            <a :href="scope.row.attachment"
                                                                               v-if="scope.row.attachment"
                                                                               target="_blank">
                                                                                View {{ getFilename(scope.row.attachment) }}
                                                                            </a>
                                                                        </template>
                                                                    </el-table-column>
                                                                </el-table>
                                                            </div>
                                                        </el-col>
                                                    </el-row>
                                                    <el-row v-if="isAdmin && !tenant.status">
                                                        <div class="rental-actions">
                                                            <span class="rental-delete" @click="deleteRental(tenant.id)">
                                                                Delete
                                                            </span>
                                                        </div>
                                                    </el-row>
                                                </el-card>
                                            </el-row>
                                        </el-col>
                                    </el-row>
                                </el-card>
                            </el-row>

                            <!-- Investments Section -->
                            <el-row style="margin-bottom: 30px" v-if="investments && investments.length">
                                <el-card class="box-card" :class="{ 'hidden-body': !showInvestments }">
                                    <div slot="header" class="clearfix main-header"
                                         @click="showInvestments = !showInvestments" style="cursor: pointer;">
                                        <div style="width: 98%">
                                            <span>Investments</span>
                                        </div>
                                        <div style="width: 2%">
                                            <i v-if="!showInvestments" class="el-icon-caret-right"></i>
                                            <i v-else class="el-icon-caret-bottom"></i>
                                        </div>
                                    </div>
                                    <!-- Export Investments Button -->
                                    <div style="padding: 10px;">
                                        <el-button icon="el-icon-document" type="secondary" @click="exportInvestments">
                                            Export Investments
                                        </el-button>
                                    </div>
                                    <el-row v-if="showInvestments" style="margin-top: 20px;" class="payments-wrapper row-item">
                                        <el-col :span="24" class="payments-history-wrapper-col">
                                            <div>
                                                <el-table border :data="investments" style="width: 100%">
                                                    <el-table-column prop="date" label="Payment Date"/>
                                                    <el-table-column prop="status" label="Status"/>
                                                    <el-table-column prop="amount" label="Amount">
                                                        <template slot-scope="scope">
                                                            {{ formatPrice(scope.row.amount) }}$
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="description" label="Description">
                                                        <template slot-scope="scope">
                                                            <el-popover placement="bottom" width="300" trigger="hover">
                                                                <div style="word-break: break-word; text-align: left">
                                                                    {{ scope.row.description }}
                                                                </div>
                                                                <div slot="reference"
                                                                     style="width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                                    {{ scope.row.description }}
                                                                </div>
                                                            </el-popover>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="attachment" label="Attachment">
                                                        <template slot-scope="scope">
                                                            <a :href="scope.row.attachment" v-if="scope.row.attachment"
                                                               target="_blank">
                                                                View {{ getFilename(scope.row.attachment) }}
                                                            </a>
                                                        </template>
                                                    </el-table-column>
                                                </el-table>
                                            </div>
                                        </el-col>
                                    </el-row>
                                </el-card>
                            </el-row>

                            <!-- Asset Value History Section -->
                            <el-row style="margin-bottom: 30px" v-if="currentValues">
                                <el-card class="box-card" :class="{ 'hidden-body': !showCurrentValues }">
                                    <div slot="header" class="clearfix main-header"
                                         @click="showCurrentValues = !showCurrentValues" style="cursor: pointer;">
                                        <div style="width: 98%">
                                            <span>Asset Value History</span>
                                        </div>
                                        <div style="width: 2%">
                                            <i v-if="!showCurrentValues" class="el-icon-caret-right"></i>
                                            <i v-else class="el-icon-caret-bottom"></i>
                                        </div>
                                    </div>
                                    <!-- Export Asset Value History Button -->
                                    <div style="padding: 10px;">
                                        <el-button icon="el-icon-document" type="secondary" @click="exportAssetValues">
                                            Export Asset Values
                                        </el-button>
                                    </div>
                                    <el-row v-if="showCurrentValues" style="margin-top: 20px;" class="payments-wrapper row-item">
                                        <el-col :span="24">
                                            <div>
                                                <el-table :data="currentValues" style="width: 100%">
                                                    <el-table-column prop="value" label="Value">
                                                        <template slot-scope="scope">
                                                            {{ formatPrice(scope.row.value) }}$
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="date" label="Date"/>
                                                    <el-table-column prop="attachment" label="Attachment" width="fit-content">
                                                        <template slot-scope="scope">
                                                            <a v-if="scope.row.attachment" :href="scope.row.attachment"
                                                               target="_blank">
                                                                {{ getFilename(scope.row.attachment) }}
                                                            </a>
                                                        </template>
                                                    </el-table-column>
                                                </el-table>
                                            </div>
                                        </el-col>
                                    </el-row>
                                </el-card>
                            </el-row>

                        </el-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { responseParse } from '../../../mixins/responseParse'
import { getData } from '../../../mixins/getData'
import MapMarker from "../../../components/admin/MapMarker.vue";
import ImageModal from "../../../components/admin/ImageModal.vue";
import ImageBox from "../../../components/admin/ImageBox.vue";
import axios from "axios";

export default {
    components: {
        ImageBox, ImageModal, MapMarker
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
            routes: {},
            options: {},
            form: {
                id: this.id
            },
            visible: false,
            loading: false,
            tenants: {},
            investments: [],
            currentValues: [],
            showInvestments: false,
            showRentals: false,
            showCurrentValues: false,
            showAgreementDetails: true
        }
    },
    created() {
        this.getSaveData();
    },
    methods: {
        onFileChange(e) {
            this.form.sale_agreement = e.target.files[0];
        },
        removeFile() {
            this.form.sale_agreement = null;
        },
        openModal() {
            this.visible = true;
        },
        resetForm() {
            this.form = {
                sale_date: '',
                sale_price: '',
                purchaser: '',
                sale_agreement: null
            };
        },
        /**
         * Get save data.
         * @returns {Promise<void>}
         */
        async getSaveData() {
            this.loading = true;
            await getData({
                method: 'POST',
                url: this.getSaveDataRoute,
                data: this.form
            }).then(response => {
                responseParse(response, false);
                if (response.status == 200) {
                    let data = response.data.data;
                    this.routes = data.routes;
                    this.options = data.options;
                    this.tenants = data.tenants;
                    this.investments = data.investments;
                    this.currentValues = data.current_values;
                    if (data.item) {
                        this.form = data.item;
                        if (data.item.files) {
                            this.form.attachments = data.item.files;
                        }
                    }
                    this.tenants.forEach(tenant => {
                        if (tenant.rentals && tenant.rentals.length) {
                            let rentals = [];
                            tenant.rentals.forEach(rental => {
                                rentals.push({
                                    payment_date: rental.payment_date,
                                    amount: rental.amount,
                                });
                            });
                            tenant.rentals = rentals;
                        } else {
                            tenant.rentals = this.generateRentals(
                                tenant.agreement_date,
                                tenant.agreement_term,
                                tenant.monthly_rent,
                                !tenant.status && tenant.rental_payments_amount_sum
                                    ? tenant.rental_payments_amount_sum
                                    : (tenant.agreement_term * tenant.monthly_rent)
                            );
                        }
                        this.$set(tenant, 'showDetails', tenant.status);
                    });
                    this.form.id = this.id;
                }
                this.loading = false;
            });
        },
        formatPrice(amount) {
            if (amount !== undefined && amount !== '') {
                if (!isNaN(amount)) {
                    if (amount % 1 === 0) {
                        return new Intl.NumberFormat('en-US', {
                            style: 'decimal',
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0,
                        }).format(amount);
                    } else {
                        return new Intl.NumberFormat('en-US', {
                            style: 'decimal',
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        }).format(amount);
                    }
                }
            }
            return '0.00';
        },
        getFilename(path) {
            return path.split('/').pop();
        },
        generateRentals(agreement_date, agreement_term, monthly_rent, rental_payments_amount_sum) {
            let leftRentalPaymentsAmount = rental_payments_amount_sum;
            let enoughtForMonth = Math.ceil(rental_payments_amount_sum / monthly_rent);
            const rentals = [];
            let [year, month, day] = agreement_date.split('/').map(Number);
            let currentDate = new Date(year, month - 1, day);
            for (let i = 0; i < enoughtForMonth; i++) {
                const paymentMonth = new Date(currentDate);
                paymentMonth.setMonth(paymentMonth.getMonth() + i);
                const formattedDate = `${paymentMonth.getFullYear()}/${String(paymentMonth.getMonth() + 1).padStart(2, '0')}/${String(paymentMonth.getDate()).padStart(2, '0')}`;
                const paymentAmount = Math.min(monthly_rent, leftRentalPaymentsAmount);
                rentals.push({
                    payment_date: formattedDate,
                    amount: paymentAmount,
                });
                leftRentalPaymentsAmount -= paymentAmount;
            }
            return rentals;
        },
        async deleteRental(tenantId) {
            this.$confirm('Are you sure?', 'You are deleting a rental', {
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                type: 'warning'
            }).then(async () => {
                await axios.post(`/assets/revenues/rental/delete/${tenantId}`).then(response => {
                    responseParse(response);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                });
            });
        },
        async deleteSale(assetId) {
            this.$confirm('Are you sure?', 'You are deleting a sale', {
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                type: 'warning'
            }).then(async () => {
                await axios.post(`/assets/delete/sell/${assetId}`).then(response => {
                    responseParse(response);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                });
            });
        },
        async save() {
            this.$refs.saleForm.validate(async (valid) => {
                if (!valid) return;
                this.loading = true;
                let formData = new FormData();
                for (let key in this.form) {
                    formData.append(key, this.form[key]);
                }
                formData.append('asset_id', this.assetId);
                try {
                    const response = await axios.post(`/assets/sell/${this.id}`, formData, {
                        headers: {'Content-Type': 'multipart/form-data'}
                    });
                    responseParse(response);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } catch (error) {
                    responseParse(error.response);
                    console.error(error);
                } finally {
                    this.loading = false;
                }
            });
        },
        async exportRentals() {
            try {
                const response = await axios.get(`/assets/revenues/${this.id}/rentals/export`, {
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'revenue_rentals.xlsx');
                document.body.appendChild(link);
                link.click();
            } catch (error) {
                console.error('Error exporting rentals:', error);
            }
        },
        async exportInvestments() {
            try {
                const response = await axios.get(`/assets/revenues/${this.id}/investments/export`, {
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'revenue_investments.xlsx');
                document.body.appendChild(link);
                link.click();
            } catch (error) {
                console.error('Error exporting investments:', error);
            }
        },
        async exportAssetValues() {
            try {
                const response = await axios.get(`/assets/revenues/${this.id}/asset-value-history/export`, {
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'asset_values.xlsx');
                document.body.appendChild(link);
                link.click();
            } catch (error) {
                console.error('Error exporting asset value history:', error);
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
                link.setAttribute('download', 'revenue_payments_schedule.xlsx');
                document.body.appendChild(link);
                link.click();
            } catch (error) {
                console.error('Error exporting payments schedule:', error);
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
        }
    }
}
</script>

<style>
.revenue-wrapper .clearfix.main-header {
    padding-left: 15px;
    font-size: 16px;
    font-weight: bold;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
}
.box-card.hidden-body .el-card__body {
    display: none;
}
.rental-actions, .sale-actions {
    padding: 10px 0 0;
}
.rental-delete, .sale-delete, .sale-edit {
    text-decoration: underline;
    cursor: pointer;
    padding: 0 10px;
    margin: 0 10px;
}
.renovation-table th, .renovation-table td {
    text-align: center;
}
</style>
