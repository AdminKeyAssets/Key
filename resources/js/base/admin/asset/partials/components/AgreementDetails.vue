<template>
    <div>
        <!-- Agreement Date -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Agreement Date:</label>
            <div class="col-md-10 uppercase-medium">
                <el-date-picker
                    v-model="form.agreement_date"
                    format="yyyy/MM/dd"
                    type="date"
                    value-format="yyyy/MM/dd"
                    placeholder="Pick an agreement date"
                />
            </div>
        </div>

        <!-- Agreements Upload -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Upload Agreement(s):</label>
            <div class="col-md-10 uppercase-medium">
                <el-form-item
                    v-for="(agreement, index) in form.agreements"
                    :key="agreement.id"
                >
                    <div class="col-md-3 uppercase-medium">
                        <el-input
                            class="col-md-12"
                            v-model="agreement.name"
                            placeholder="Name for Agreement"
                        />
                    </div>
                    <div class="col-md-3 uppercase-medium">
                        <input
                            type="file"
                            @change="onAgreementFileChange($event, index)"
                            v-if="!agreement.attachment"
                        />
                        <div v-else>
                            <img
                                v-if="agreement.attachment.preview"
                                :src="agreement.attachment.preview"
                                alt="preview"
                                style="max-width: 100px;"
                            />
                            <a
                                v-else
                                :href="agreement.attachment"
                                target="_blank"
                            >
                                {{ agreement.name }}
                            </a>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <el-button
                            icon="el-icon-delete-solid"
                            size="small"
                            type="danger"
                            @click.prevent="removeAgreement(agreement)"
                        />
                    </div>
                </el-form-item>
                <el-button
                    type="primary"
                    size="medium"
                    icon="el-icon-plus"
                    @click="addAgreement"
                >
                    Add Agreement
                </el-button>
            </div>
        </div>

        <!-- M2 Price -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">M2 Price:</label>
            <div class="col-md-7 uppercase-medium">
                <el-input-number
                    :disabled="loading"
                    v-model="form.price"
                    :controls="false"
                    size="large"
                    :precision="2"
                    :min="0"
                />
            </div>
        </div>

        <!-- Total Price -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Total Price:</label>
            <div class="col-md-10 uppercase-medium">
                <el-input-number
                    type="number"
                    :disabled="loading"
                    v-model="form.total_price"
                    :controls="false"
                    size="large"
                    :precision="2"
                    :min="0"
                />
            </div>
        </div>

        <!-- Agreement Status -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Agreement Status:</label>
            <div class="col-md-3 uppercase-medium">
                <el-select
                    v-model="form.agreement_status"
                    filterable
                    placeholder="Agreement Status"
                    v-remove-readonly
                >
                    <el-option
                        v-for="status in agreementStatuses"
                        :key="status"
                        :label="status"
                        :value="status"
                    />
                </el-select>
            </div>
        </div>

        <!-- Ownership Status -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Ownership Status:</label>
            <div class="col-md-3 uppercase-medium">
                <el-select
                    v-model="form.ownership_status"
                    filterable
                    placeholder="Ownership Status"
                    v-remove-readonly
                >
                    <el-option
                        v-for="status in ownershipStatuses"
                        :key="status"
                        :label="status"
                        :value="status"
                    />
                </el-select>
            </div>
        </div>

        <!-- Ownership Certificate (Complete) -->
        <template v-if="form.agreement_status === 'Complete'">
            <div class="form-group dashed">
                <label class="col-md-1 control-label">Ownership Certificate:</label>
                <div class="col-md-10 uppercase-medium">
                    <input
                        type="file"
                        @change="onOwnershipCertificateChange"
                    />
                    <div v-if="form.ownership_certificate">
                        <p>
                            File:
                            <a
                                :href="form.ownership_certificate"
                                target="_blank"
                            >
                                View Attachment
                            </a>
                        </p>
                        <el-button
                            icon="el-icon-delete-solid"
                            size="small"
                            type="danger"
                            @click="removeOwnershipCertificate"
                        />
                    </div>
                </div>
            </div>
        </template>

        <!-- Installments -->
        <template v-if="form.agreement_status === 'Installments'">
            <div class="form-group dashed">
                <label class="col-md-1 control-label">First Payment Date:</label>
                <div class="col-md-10 uppercase-medium">
                    <el-date-picker
                        v-model="form.first_payment_date"
                        format="yyyy/MM/dd"
                        type="date"
                        value-format="yyyy/MM/dd"
                        placeholder="Pick a First payment date"
                    />
                </div>
            </div>

            <div class="form-group dashed">
                <label class="col-md-1 control-label">Period:</label>
                <div class="col-md-3 uppercase-medium">
                    <el-select
                        v-model="form.period"
                        filterable
                        placeholder="Period"
                        v-remove-readonly
                    >
                        <el-option
                            v-for="number in numbers"
                            :key="number"
                            :label="number"
                            :value="number"
                        />
                    </el-select>
                </div>
            </div>

            <el-button
                type="primary"
                size="medium"
                icon="el-icon-check"
                @click="generatePayments"
                :disabled="loading"
                style="margin: 21px 1rem"
            >
                Load Payments
            </el-button>

            <div v-if="form.payments && form.payments.length">
                <el-table :data="form.payments" style="width: 100%">
                    <el-table-column prop="number" label="Payment" width="150"/>
                    <el-table-column prop="payment_date" label="Payment Date" width="180">
                        <template #default="{ row, $index }">
                            <el-date-picker
                                v-model="row.payment_date"
                                type="date"
                                format="yyyy/MM/dd"
                                value-format="yyyy/MM/dd"
                                @change="updatePaymentDate($index, row.payment_date)"
                            />
                        </template>
                    </el-table-column>
                    <el-table-column prop="amount" label="Amount" width="180">
                        <template #default="{ row, $index }">
                            <el-input
                                type="number"
                                v-model="row.amount"
                                @input="updatePaymentAmount($index)"
                            />
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </template>
    </div>
</template>

<script>
import {Notification} from 'element-ui';

export default {
    name: 'AgreementFormComponent',
    props: ['form', 'loading', 'currencies', 'agreementStatuses', 'ownershipStatuses', 'numbers'],

    data() {
        return {
            previousAgreementStatus: '',
            errorMessage: '',
            updatingTotalPrice: false,
            updatingSqmPrice: false,
        };
    },

    computed: {
        priceDisplay: {
            get() {
                if (this.form.price === null || this.form.price === undefined) return '';
                return parseFloat(this.form.price).toFixed(2);
            },
            set(val) {
                const num = parseFloat(val);
                if (!isNaN(num)) {
                    this.$emit('update-form', {...this.form, price: num});
                }
            }
        }
    },

    mounted() {
        // default currency
        if (this.form && !this.form.currency) {
            this.$emit('update-form', {...this.form, currency: 'USD'});
        }
        this.previousAgreementStatus = this.form.agreement_status;
    },

    watch: {
        'form.price': {handler: 'updateTotalPrice'},
        'form.area': {handler: 'updateTotalPrice'},
        'form.total_price': {handler: 'updateSqmPrice'},

        'form.agreement_status'(newStatus) {
            if (
                newStatus === 'Complete' &&
                this.form.payments_histories &&
                this.form.payments_histories.length > 0
            ) {
                const totalPayments = this.form.payments_histories.reduce((sum, h) => sum + h.amount, 0);
                if (totalPayments < this.form.agreement_price) {
                    this.form.agreement_status = 'Installments';
                    Notification.error({message: 'Total payments are less than the agreement price!'});
                }
            }
        }
    },

    methods: {
        onOwnershipCertificateChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = ev => {
                    this.$emit('update-form', {
                        ...this.form,
                        ownership_certificate: file,
                        ownershipCertificatePreview: ev.target.result
                    });
                };
                reader.readAsDataURL(file);
            }
        },
        removeOwnershipCertificate() {
            this.$emit('update-form', {...this.form, ownership_certificate: null, ownershipCertificatePreview: null});
        },

        generatePayments() {
            if (!this.form.payments) this.$set(this.form, 'payments', []);
            const totalAmount = parseFloat(this.form.total_price);
            const period = parseInt(this.form.period, 10);
            const firstDate = new Date(this.form.first_payment_date);
            if (isNaN(totalAmount) || isNaN(period) || isNaN(firstDate)) return;

            const amountPer = (totalAmount / period).toFixed(2);
            const payments = [];
            for (let i = 0; i < period; i++) {
                const dt = new Date(firstDate);
                dt.setMonth(dt.getMonth() + i);
                payments.push({number: i + 1, payment_date: this.formatDate(dt), amount: amountPer});
            }
            this.updateFinalPaymentAmount(payments);
            this.$set(this.form, 'payments', payments);
        },

        updatePaymentDate(index, date) {
            this.$set(this.form.payments, index, {...this.form.payments[index], payment_date: date});
        },

        updatePaymentAmount(index) {
            this.$set(this.form.payments, index, {
                ...this.form.payments[index],
                amount: parseFloat(this.form.payments[index].amount)
            });
            this.updateFinalPaymentAmount(this.form.payments);
        },

        updateFinalPaymentAmount(payments) {
            const total = parseFloat(this.form.total_price);
            const sum = payments.slice(0, -1).reduce((s, p) => s + parseFloat(p.amount), 0);
            const finalAmt = (total - sum).toFixed(2);
            this.$set(payments, payments.length - 1, {...payments[payments.length - 1], amount: finalAmt});
        },

        formatDate(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}/${m}/${d}`;
        },

        updateTotalPrice() {
            if (this.updatingSqmPrice) return;
            const price = parseFloat(this.form.price);
            const area = parseFloat(this.form.area);
            if (isNaN(price) || isNaN(area)) return;
            this.updatingTotalPrice = true;
            const totalPrice = price * area;
            const payload = {...this.form, total_price: totalPrice};
            if (!this.form.id) payload.current_value = totalPrice;
            this.$emit('update-form', payload);
            this.updatingTotalPrice = false;
        },

        updateSqmPrice() {
            if (this.updatingTotalPrice) return;
            const totalPrice = parseFloat(this.form.total_price);
            const area = parseFloat(this.form.area);
            if (isNaN(totalPrice) || isNaN(area) || area === 0) return;
            this.updatingSqmPrice = true;
            const rawPrice = totalPrice / area;
            this.$emit('update-form', {...this.form, price: rawPrice});
            this.updatingSqmPrice = false;
        },

        onAgreementFileChange(e, i) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = ev => {
                    const ags = [...this.form.agreements];
                    ags[i].attachment = {
                        file,
                        preview: file.type.startsWith('image/') ? ev.target.result : null,
                        name: file.name
                    };
                    this.$emit('update-form', {...this.form, agreements: ags});
                };
                reader.readAsDataURL(file);
            }
        },

        addAgreement() {
            const ags = Array.isArray(this.form.agreements) ? [...this.form.agreements] : [];
            ags.push({id: Date.now(), name: '', attachment: null});
            this.$emit('update-form', {...this.form, agreements: ags});
        },

        removeAgreement(item) {
            const ags = Array.isArray(this.form.agreements)
                ? this.form.agreements.filter(a => a.id !== item.id)
                : [];
            this.$emit('update-form', {...this.form, agreements: ags});
        }
    }
};
</script>
