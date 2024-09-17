<template>
    <div>
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Agreement Date:</label>
            <div class="col-md-10 uppercase-medium">
                <el-date-picker
                    v-model="form.agreement_date"
                    format="yyyy/MM/dd"
                    type="date"
                    value-format="yyyy/MM/dd"
                    placeholder="Pick an agreement date">
                </el-date-picker>
            </div>
        </div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Upload Agreement(s):</label>
            <div class="col-md-10 uppercase-medium">
                <el-form-item v-for="(agreement, index) in form.agreements" :key="agreement.id">
                    <div class="col-md-3 uppercase-medium">
                        <el-input class="col-md-12" v-model="agreement.name" placeholder="Name for Agreement"></el-input>
                    </div>
                    <div class="col-md-3 uppercase-medium">
                        <input type="file" @change="onAgreementFileChange($event, index)" v-if="!agreement.attachment">
                        <div v-if="agreement.attachment">
                            <img v-if="agreement.attachment.preview" :src="agreement.attachment.preview" alt="preview"
                                 style="max-width: 100px;"/>
                            <a v-else :href="agreement.attachment" target="_blank">{{agreement.name}}</a>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                   @click.prevent="removeAgreement(agreement)"></el-button>
                    </div>
                </el-form-item>
                <el-button type="primary" size="medium" icon="el-icon-plus" @click="addAgreement">Add Agreement</el-button>
            </div>
        </div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">M2 Price:</label>
            <div class="col-md-7 uppercase-medium">
                <input class="form-control" type="number" :disabled="loading" v-model="form.price"></input>
            </div>
        </div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Total Price:</label>
            <div class="col-md-10 uppercase-medium">
                <input class="form-control" type="number" :disabled="loading" v-model="form.total_price"></input>
            </div>
        </div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Agreement Status:</label>
            <div class="col-md-3 uppercase-medium">
                <el-select v-model="form.agreement_status"
                           :value="form.agreement_status"
                           filterable
                           placeholder="Agreement Status">
                    <el-option
                        v-for="status in agreementStatuses"
                        :key="status"
                        :label="status"
                        :value="status">
                    </el-option>
                </el-select>
            </div>
        </div>

        <template v-if="form.agreement_status === 'Complete'">
            <div class="form-group dashed">
                <label class="col-md-1 control-label">Ownership Certificate:</label>
                <div class="col-md-10 uppercase-medium">
                    <input type="file" @change="onOwnershipCertificateChange">
                    <div v-if="form.ownership_certificate">
                        <p v-if="form.ownership_certificate">File: <a :href="form.ownership_certificate" target="_blank">View Attachment</a></p>
                        <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                   @click="removeOwnershipCertificate"></el-button>
                    </div>
                </div>
            </div>
        </template>

        <template v-if="form.agreement_status === 'Installments'">

            <div class="form-group dashed">
                <label class="col-md-1 control-label">First Payment Date:</label>
                <div class="col-md-10 uppercase-medium">
                    <el-date-picker
                        v-model="form.first_payment_date"
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
                    <el-select v-model="form.period"
                               :value="form.period"
                               filterable
                               placeholder="Period">
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
                       @click="generatePayments"
                       :disabled="loading"
                       style="margin: 21px 1rem">Load Payments
            </el-button>

            <div v-if="form.payments && form.payments.length">
                <el-table :data="form.payments" style="width: 100%">
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
        'form.price': 'updateTotalPrice',
        'form.area': 'updateTotalPrice',
        'form.total_price': 'updateSqmPrice',
        'form'() {
            if (this.form) {
                if (!this.form.currency) {
                    this.$emit('update-form', { ...this.form, currency: 'USD' });
                }
            }
        },
        'form.agreement_status'(newStatus) {
            if (newStatus === 'Complete' && this.form.payments_histories && this.form.payments_histories.length > 0) {
                const totalPayments = this.form.payments_histories.reduce((sum, history) => sum + history.amount, 0);

                if (totalPayments < this.form.agreement_price) {
                    this.form.agreement_status = 'Installments';
                    Notification.error({
                        message: 'Total payments are less than the agreement price!',
                    });
                } else {
                    this.form.agreement_status = newStatus;
                }
            }
        },
    },
    methods: {
        onOwnershipCertificateChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.$emit('update-form', { ...this.form, ownership_certificate: file, ownershipCertificatePreview: e.target.result });
                };
                reader.onerror = (error) => {
                    console.error('Error loading file:', error);
                };
                reader.readAsDataURL(file);
            }
        },
        removeOwnershipCertificate() {
            this.$emit('update-form', { ...this.form, ownership_certificate: null, ownershipCertificatePreview: null });
        },

        generatePayments() {
            if (!this.form.payments) {
                this.$set(this.form, 'payments', []);
            }

            const totalAmount = parseFloat(this.form.total_price);
            const period = parseInt(this.form.period);
            const firstPaymentDate = new Date(this.form.first_payment_date);

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
            this.$set(this.form, 'payments', payments);
        },

        updatePaymentDate(index, date) {
            this.$set(this.form.payments, index, {
                ...this.form.payments[index],
                payment_date: date
            });
        },

        updatePaymentAmount(index) {
            const totalAmount = parseFloat(this.form.total_price);
            let amountSum = this.form.payments.reduce((sum, payment, idx) => {
                return idx !== index ? sum + parseFloat(payment.amount) : sum;
            }, 0);
            const finalAmount = totalAmount - amountSum;

            this.$set(this.form.payments, index, {
                ...this.form.payments[index],
                amount: parseFloat(this.form.payments[index].amount)
            });

            this.updateFinalPaymentAmount(this.form.payments);
        },

        updateFinalPaymentAmount(payments) {
            const totalAmount = parseFloat(this.form.total_price);
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

        updateTotalPrice() {
            if (this.updatingSqmPrice) return; // Prevent recursion when sqm price is being updated
            this.updatingTotalPrice = true;

            const price = parseFloat(this.form.price) || 0;
            const area = parseFloat(this.form.area) || 0;
            const totalPrice = price * area;
            this.$emit('update-form', {...this.form, total_price: totalPrice});

            this.updatingTotalPrice = false;
        },

        updateSqmPrice() {
            if (this.updatingTotalPrice) return; // Prevent recursion when total price is being updated
            this.updatingSqmPrice = true;

            const totalPrice = parseFloat(this.form.total_price) || 0;
            const area = parseFloat(this.form.area) || 0;
            const price = totalPrice / area;
            this.$emit("update-form", {...this.form, price: price});

            this.updatingSqmPrice = false;
        },

        onAgreementFileChange(e, index) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const agreements = [...this.form.agreements];
                    agreements[index].attachment = {
                        file: file,
                        preview: file.type.startsWith('image/') ? e.target.result : null,
                        name: file.name,
                    };
                    this.$emit('update-form', { ...this.form, agreements });
                };
                reader.readAsDataURL(file);
            }
        },
        addAgreement() {
            this.$emit('update-form', {
                ...this.form,
                agreements: [...(Array.isArray(this.form.agreements) ? this.form.agreements : []), {
                    id: Date.now(),
                    name: '',
                    attachment: null
                }]
            });
        },
        removeAgreement(item) {
            const agreements = Array.isArray(this.form.agreements) ? this.form.agreements.filter(detail => detail.id !== item.id) : [];
            this.$emit('update-form', {...this.form, agreements});
        }
    }
}
</script>
