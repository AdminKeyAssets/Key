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

                            <el-row style="margin-bottom: 30px" v-if="tenants" v-for="tenant in tenants"
                                    v-bind:key="tenant.id">
                                <el-card class="box-card" :class="{ 'hidden-body': !tenant.showDetails }">
                                    <!-- Card Header - Toggles visibility on click -->
                                    <div slot="header" class="clearfix main-header" @click="tenant.showDetails = !tenant.showDetails" style="cursor: pointer;">
                                        <div style="width: 98%">
                                            <span>{{ tenant.name }} {{ tenant.surname }}</span>
                                            <span>
                                            <span>{{ tenant.agreement_date }}</span>
                                            <span v-if="tenant.rentals && tenant.rentals.length && !tenant.status"> - {{
                                                    tenant.rentals.slice(-1)[0].payment_date
                                                }}</span>
                                        </span>
                                        </div>
                                        <div style="width: 2%">
                                            <i v-if="!tenant.showDetails" class="el-icon-caret-right"></i>
                                            <i v-else class="el-icon-caret-bottom"></i>
                                        </div>
                                    </div>

                                    <!-- Card Content - Shown when tenant.showDetails is true -->
                                    <el-row v-if="tenant.showDetails">
                                        <el-row class="row-item"
                                                v-if="tenant.id_number || tenant.citizenship">
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

                                        <el-row class="row-item"
                                                v-if="tenant.phone || tenant.email">
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

                                        <el-row class="row-item"
                                                v-if="tenant.agreement_term || tenant.monthly_rent">
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
                                                        {{ formatPrice(tenant.monthly_rent) }}
                                                        {{ tenant.currency }}
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                        <el-row class="row-item"
                                                v-if="tenant.agreement_date">
                                            <el-col :span="12">
                                                <div v-if="tenant.agreement_date" class="form-group">
                                                    <label class="col-md-4 control-label">Agreement Date:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        {{ tenant.agreement_date }}
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>

                                        <el-row class="row-item"
                                                v-if="tenant.passport">
                                            <el-col :span="12">
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Passport:</label>
                                                    <div class="col-md-6 uppercase-medium">
                                                        <div v-if="tenant.passport">
                                                            <ImageModal :image-path="tenant.passport"
                                                                        :width="100"
                                                                        :height="100"
                                                                        :thumbnail="tenant.passport"></ImageModal>
                                                        </div>
                                                    </div>
                                                </div>
                                            </el-col>
                                        </el-row>
                                    </el-row>

                                    <el-row v-if="tenant.showDetails" style="margin-top: 20px;" class="payments-wrapper row-item">
                                        <el-col :span="24" :md="11">
                                            <div>
                                                <div class="payments-schedule-title-wrapper">
                                                    <div class="rentals-schedule-heading" style="text-align: center; max-width: 500px">
                                                        <h4>Rentals Schedule</h4>
                                                    </div>
                                                </div>
                                                <el-table border :data="tenant.rentals" style="width: 100%">
                                                    <el-table-column prop="payment_date" label="Payment Date"/>
                                                    <el-table-column prop="amount" label="Amount">
                                                        <template slot-scope="scope">
                                                            {{ formatPrice(scope.row.amount) }} {{ tenant.currency }}
                                                        </template>
                                                    </el-table-column>
                                                </el-table>
                                            </div>
                                        </el-col>

                                        <el-col :span="24" :md="11" class="payments-history-wrapper-col">
                                            <div v-if="tenant.rental_payments">
                                                <div class="payments-schedule-title-wrapper">
                                                    <div class="payments-history-heading" style="text-align: center; max-width: 500px">
                                                        <h4>Payments History</h4>
                                                    </div>
                                                </div>
                                                <el-table border :data="tenant.rental_payments" style="width: 100%">
                                                    <el-table-column prop="date" label="Payment Date"/>
                                                    <el-table-column prop="amount" label="Amount">
                                                        <template slot-scope="scope">
                                                            {{ formatPrice(scope.row.amount) }} {{ scope.row.currency }}
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="attachment" label="Attachment">
                                                        <template slot-scope="scope">
                                                            <a :href="scope.row.attachment" v-if="scope.row.attachment" target="_blank">View {{ getFilename(scope.row.attachment) }}</a>
                                                        </template>
                                                    </el-table-column>
                                                </el-table>
                                            </div>
                                        </el-col>
                                    </el-row>
                                </el-card>
                            </el-row>

                            <el-row style="margin-bottom: 30px" v-if="investments">
                                <el-card class="box-card" :class="{ 'hidden-body': !showInvestments }">
                                    <div slot="header" class="clearfix main-header"  @click="showInvestments = !showInvestments" style="cursor: pointer;">
                                       <div style="width: 98%">
                                           <span>Investments</span>
                                       </div>
                                        <div style="width: 2%">
                                            <i v-if="!showInvestments" class="el-icon-caret-right"></i>
                                            <i v-else class="el-icon-caret-bottom"></i>
                                        </div>
                                    </div>

                                    <el-row v-if="showInvestments" style="margin-top: 20px;" class="payments-wrapper row-item">
                                        <el-col :span="24" class="payments-history-wrapper-col">
                                            <div>
                                                <el-table border :data="investments" style="width: 100%">
                                                    <el-table-column prop="date" label="Payment Date"/>
                                                    <el-table-column prop="status" label="Status"/>
                                                    <el-table-column prop="amount" label="Amount">
                                                        <template slot-scope="scope">
                                                            {{ formatPrice(scope.row.amount) }} {{ scope.row.currency }}
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="description" label="Description">
                                                        <template slot-scope="scope">
                                                            <el-popover placement="bottom" width="300" trigger="hover">
                                                                <div style="word-break: break-word; text-align: left">{{ scope.row.description }}</div>
                                                                <div slot="reference" style="width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ scope.row.description }}</div>
                                                            </el-popover>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="attachment" label="Attachment">
                                                        <template slot-scope="scope">
                                                            <a :href="scope.row.attachment" v-if="scope.row.attachment" target="_blank">View {{ getFilename(scope.row.attachment) }}</a>
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
import {responseParse} from '../../../mixins/responseParse'
import {getData} from '../../../mixins/getData'
import MapMarker from "../../../components/admin/MapMarker.vue";
import ImageModal from "../../../components/admin/ImageModal.vue";
import ImageBox from "../../../components/admin/ImageBox.vue";

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
            loading: false,
            routes: {},
            options: {},
            /** Form data*/
            form: {
                id: this.id
            },
            tenants: {},
            rentals: {},
            investments: [],
            showInvestments: false,
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
                    this.tenants = data.tenants;
                    this.investments = data.investments;

                    this.tenants.forEach(tenant => {
                        tenant.rentals = this.generateRentals(tenant.agreement_date, tenant.agreement_term, tenant.monthly_rent, tenant.rental_payments_amount_sum);
                        this.$set(tenant, 'showDetails', false); // Add this line to initialize showDetails to false
                    });

                    this.form.id = this.id;
                }
                this.loading = false
            })
        },

        formatPrice(amount) {
            // Do not format if undefined or empty
            if (amount !== undefined && amount !== '') {
                // Ensure amount is a valid number
                if (!isNaN(amount)) {
                    // Check if the amount has decimal places
                    if (amount % 1 === 0) {
                        // No decimal places for whole numbers
                        return new Intl.NumberFormat('en-US', {
                            style: 'decimal',
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0,
                        }).format(amount);
                    } else {
                        // Keep two decimal places for non-whole numbers
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

                rentals.push({
                    payment_date: formattedDate,
                    amount: monthly_rent < leftRentalPaymentsAmount ? monthly_rent : leftRentalPaymentsAmount,
                });

                leftRentalPaymentsAmount = leftRentalPaymentsAmount - monthly_rent;
            }

            return rentals;
        },

        logAndReturn(value) {
            console.log(value);
            return value;
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

.phone-container, .email-container {
    display: flex;
    align-items: center;
}

.whatsapp-icon, .email-icon {
    margin-right: 10px;
    cursor: pointer;
}

.revenue-wrapper .clearfix.main-header {
    padding-left: 15px;
    font-size: 16px;
    font-weight: bold;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
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

.box-card.hidden-body .el-card__body{
    display: none;
}
</style>
