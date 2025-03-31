<template>
    <div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Renovation Status:</label>
            <div class="col-md-3 uppercase-medium">
                <el-select v-model="form.renovation_status" placeholder="Select Status" v-remove-readonly>
                    <el-option label="In Progress" value="In Progress"></el-option>
                    <el-option label="Completed" value="Completed"></el-option>
                </el-select>
            </div>
        </div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Agreement Date:</label>
            <div class="col-md-10 uppercase-medium">
                <el-date-picker
                    v-model="form.renovation_agreement_date"
                    format="yyyy/MM/dd"
                    type="date"
                    value-format="yyyy/MM/dd"
                    placeholder="Pick an agreement date">
                </el-date-picker>
            </div>
        </div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Add Attachment:</label>
            <div class="col-md-10 uppercase-medium">

                <div class="col-md-3 uppercase-medium">
                    <el-input class="col-md-12" v-model="form.renovation_agreement_name" placeholder="Name for Agreement"></el-input>
                </div>
                <div class="col-md-3 uppercase-medium">
                    <input type="file" @change="onRenovationAttachmentChange">
                    <div v-if="form.renovation_agreement">
                        <p v-if="form.renovation_agreement">File: <a :href="form.renovation_agreement"
                                                                     target="_blank">View Attachment</a></p>
                        <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                   @click="removeRenovationAttachment"></el-button>
                    </div>
                </div>

            </div>
        </div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Total Price:</label>
            <div class="col-md-10 uppercase-medium">
                <input class="form-control" type="number" :disabled="loading" v-model="form.renovation_total_price"></input>
            </div>
        </div>

        <template>

            <div class="form-group dashed">
                <label class="col-md-1 control-label">First Payment Date:</label>
                <div class="col-md-10 uppercase-medium">
                    <el-date-picker
                        v-model="form.renovation_first_payment_date"
                        format="yyyy/MM/dd"
                        type="date"
                        value-format="yyyy/MM/dd"
                        placeholder="Pick a First payment date">
                    </el-date-picker>
                </div>
            </div>

            <div class="form-group dashed">
                <label class="col-md-1 control-label">Period:</label>
                <div class="col-md-3 uppercase-medium">
                    <el-select v-model="form.renovation_period"
                               :value="form.renovation_period"
                               filterable
                               placeholder="Period"
                               v-remove-readonly>
                        <el-option
                            v-for="number in numbers"
                            :key="number"
                            :label="number"
                            :value="number">
                        </el-option>
                    </el-select>
                </div>
            </div>

            <el-button type="primary" size="medium" icon="el-icon-check"
                       @click="generateRenovationPayments"
                       :disabled="loading"
                       style="margin: 21px 1rem">Load Renovation Payments
            </el-button>

            <div v-if="form.renovation_payments && form.renovation_payments.length">
                <el-table :data="form.renovation_payments" style="width: 100%">
                    <el-table-column prop="number" label="Payment" width="150"/>
                    <el-table-column prop="payment_date" label="Payment Date" width="180">
                        <template slot-scope="scope">
                            <el-date-picker
                                v-model="scope.row.payment_date"
                                type="date"
                                format="yyyy/MM/dd"
                                value-format="yyyy/MM/dd"
                                @change="updatePaymentDate(scope.$index, scope.row.payment_date)"
                            ></el-date-picker>
                        </template>
                    </el-table-column>
                    <el-table-column prop="amount" label="Amount" width="180">
                        <template slot-scope="scope">
                            <el-input
                                type="number"
                                v-model="scope.row.amount"
                                @input="updatePaymentAmount(scope.$index)"
                            ></el-input>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </template>
    </div>
</template>

<script>
import { Notification } from 'element-ui';
export default {
    props: ['form', 'loading', 'currencies', 'agreementStatuses', 'numbers'],
    data() {
        return {
            previousAgreementStatus: '',
            errorMessage: '',
            updatingTotalPrice: false, // Flag to prevent recursion
            updatingSqmPrice: false,
        };
    },
    watch: {
        'form'() {
            if (this.form) {
                if (!this.form.currency) {
                    this.$emit('update-form', { ...this.form, currency: 'USD' });
                }
            }
        },

    },
    methods: {
        generateRenovationPayments() {
            if (!this.form.renovation_payments) {
                this.$set(this.form, 'renovations_payments', []);
            }

            const totalAmount = parseFloat(this.form.renovation_total_price);
            const period = parseInt(this.form.renovation_period);
            const firstPaymentDate = new Date(this.form.renovation_first_payment_date);

            if (!totalAmount || !period || isNaN(firstPaymentDate)) {
                return;
            }

            const amountPerPeriod = (totalAmount / period).toFixed(2);
            const payments = [];
            for (let i = 0; i < period; i++) {
                let paymentDate = new Date(firstPaymentDate);
                paymentDate.setMonth(paymentDate.getMonth() + i);

                payments.push({
                    number: i + 1,
                    payment_date: this.formatDate(paymentDate),
                    amount: amountPerPeriod
                });
            }

            this.updateFinalPaymentAmount(payments);
            this.$set(this.form, 'renovation_payments', payments);
        },

        updatePaymentDate(index, date) {
            this.$set(this.form.renovation_payments, index, {
                ...this.form.renovation_payments[index],
                payment_date: date
            });
        },

        updatePaymentAmount(index) {
            const totalAmount = parseFloat(this.form.renovation_total_price);
            let amountSum = this.form.renovation_payments.reduce((sum, payment, idx) => {
                return idx !== index ? sum + parseFloat(payment.amount) : sum;
            }, 0);
            const finalAmount = totalAmount - amountSum;

            this.$set(this.form.renovation_payments, index, {
                ...this.form.renovation_payments[index],
                amount: parseFloat(this.form.renovation_payments[index].amount)
            });

            this.updateFinalPaymentAmount(this.form.renovation_payments);
        },

        updateFinalPaymentAmount(payments) {
            const totalAmount = parseFloat(this.form.renovation_total_price);
            let amountSum = payments.slice(0, -1).reduce((sum, payment) => {
                return sum + parseFloat(payment.amount);
            }, 0);

            const finalAmount = totalAmount - amountSum;

            this.$set(payments, payments.length - 1, {
                ...payments[payments.length - 1],
                amount: finalAmount.toFixed(2)
            });
        },
        formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}/${month}/${day}`;
        },

        onRenovationAttachmentChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.$emit('update-form', {
                        ...this.form,
                        renovation_agreement: file,
                        renovationAttachmentPreview: e.target.result
                    });
                };
                reader.onerror = (error) => {
                    console.error('Error loading file:', error);
                };
                reader.readAsDataURL(file);
            }
        },
        removeRenovationAttachment() {
            this.$emit('update-form', {
                ...this.form,
                renovation_attachment: null,
                renovationAttachmentPreview: null
            });
        },
    }
}
</script>
