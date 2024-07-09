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
            <label class="col-md-1 control-label">Agreement Upload:</label>
            <div class="col-md-10 uppercase-medium">
                <input type="file" @change="onAgreementChange" accept="image/*">
                <div v-if="form.agreement">
                    <p v-if="form.agreement">File: <a :href="form.agreement" target="_blank">View Attachment</a></p>
                    <el-button icon="el-icon-delete-solid" size="small" type="danger"
                               @click="removeAgreement"></el-button>
                </div>
            </div>
        </div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">M2 Price:</label>
            <div class="col-md-7 uppercase-medium">
                <input class="form-control" type="number" :disabled="loading" v-model="form.price"></input>
            </div>
            <div class="col-md-3 uppercase-medium">
                <el-select v-model="form.currency"
                           :value="form.currency"
                           filterable
                           placeholder="Select">
                    <el-option
                        v-for="(currency, index) in currencies"
                        :key="index"
                        :label="currency"
                        :value="index">
                    </el-option>
                </el-select>
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
export default {
    props: ['form', 'loading', 'agreementStatuses', 'numbers'],
    watch: {
        'form.price': 'updateTotalPrice',
        'form.area': 'updateTotalPrice',
        'form'() {
            if (this.form) {
                if (!this.form.currency) {
                    this.$emit('update-form', { ...this.form, currency: 'USD' });
                }
            }
        }
    },
    methods: {
        onAgreementChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.$emit('update-form', { ...this.form, agreement: file, agreementPreview: e.target.result });
                };
                reader.onerror = (error) => {
                    console.error('Error loading file:', error);
                };
                reader.readAsDataURL(file);
            }
        },
        removeAgreement() {
            this.$emit('update-form', { ...this.form, agreement: null, agreementPreview: null });
        },

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
            const price = parseFloat(this.form.price) || 0;
            const area = parseFloat(this.form.area) || 0;
            const totalPrice = price * area;
            this.$emit('update-form', {...this.form, total_price: totalPrice});
        },
    }
}
</script>
