<template>
    <div>
        <div class="block">
            <el-form v-loading="loading"
                     element-loading-text="Loading..."
                     element-loading-spinner="el-icon-loading"
                     element-loading-background="rgba(0, 0, 0, 0.0)"
                     ref="form" :model="form" class="form-horizontal form-bordered">

                <el-row style="margin-left: 15px">

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Name:</label>
                        <div class="col-md-10 uppercase-medium">
                            <input class="form-control" :disabled="loading" v-model="form.name"></input>
                        </div>
                    </div>


                    <el-tabs v-model="activeNames">
                        <el-tab-pane label="Project Details" name="1">
                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Upload Icon:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input type="file" @change="onIconChange" accept="image/*">
                                    <div v-if="form.icon">
                                        <ImageModal v-if="form.iconPreview"
                                                    :image-path="form.iconPreview"
                                                    :thumbnail="form.iconPreview"></ImageModal>
                                        <ImageModal v-else
                                                    :image-path="form.icon"
                                                    :thumbnail="form.icon"></ImageModal>
                                        <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                                   @click="removeIcon"></el-button>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Project Name:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input class="form-control" :disabled="loading" v-model="form.project_name"></input>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Project Description:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <el-input
                                        type="textarea"
                                        autosize
                                        placeholder="Project Description"
                                        :disabled="loading"
                                        v-model="form.project_description">
                                    </el-input>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Project Link:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input class="form-control" :disabled="loading" v-model="form.project_link"></input>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">City:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input class="form-control" :disabled="loading" v-model="form.city"></input>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Address:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input class="form-control" :disabled="loading" v-model="form.address"></input>
                                </div>
                            </div>
                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Location:</label>
                                <MapMarker :update-data="this.updateData"
                                           :item="this.item"
                                ></MapMarker>
                            </div>
                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Delivery Date:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <el-date-picker
                                        v-model="form.delivery_date"
                                        format="yyyy/MM/dd"
                                        type="date"
                                        value-format="yyyy/MM/dd"
                                        placeholder="Pick a delivery date">
                                    </el-date-picker>
                                </div>
                            </div>
                        </el-tab-pane>
                        <el-tab-pane label="Asset Details" name="2">
                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Type:</label>
                                <div class="col-md-3 uppercase-medium">
                                    <el-select v-model="form.type"
                                               :value="form.type"
                                               filterable
                                               placeholder="Select Type">
                                        <el-option
                                            v-for="type in types"
                                            :key="type"
                                            :label="type"
                                            :value="type">
                                        </el-option>
                                    </el-select>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Floor:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input class="form-control" :disabled="loading" v-model="form.floor"></input>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Flat number:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input class="form-control" :disabled="loading" v-model="form.flat_number"></input>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Area (m2):</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input class="form-control" :disabled="loading" v-model="form.area"></input>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Price:</label>
                                <div class="col-md-7 uppercase-medium">
                                    <input class="form-control" :disabled="loading" v-model="form.price"></input>
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
                                    <input class="form-control" :disabled="loading" v-model="form.total_price"></input>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Delivery Condition:</label>
                                <div class="col-md-3 uppercase-medium">
                                    <el-select v-model="form.condition"
                                               :value="form.condition"
                                               filterable
                                               placeholder="Delivery Condition">
                                        <el-option
                                            v-for="condition in conditions"
                                            :key="condition"
                                            :label="condition"
                                            :value="condition">
                                        </el-option>
                                    </el-select>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Cadastral Number:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input class="form-control" :disabled="loading"
                                           v-model="form.cadastral_number"></input>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Select Investor:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <el-select v-model="form.investor_id" :value="form.investor_id" filterable
                                               placeholder="Select">
                                        <el-option
                                            v-for="item in investors"
                                            :key="item.id"
                                            :label="item.name"
                                            :value="item.id">
                                        </el-option>
                                    </el-select>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Upload Floor Plan:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input type="file" @change="onFloorPlanChange" accept="image/*">
                                    <div v-if="form.floor_plan">
                                        <ImageModal v-if="form.floorPlanPreview"
                                                    :image-path="form.floorPlanPreview"
                                                    :thumbnail="form.floorPlanPreview"></ImageModal>
                                        <ImageModal v-else
                                                    :image-path="form.floor_plan"
                                                    :thumbnail="form.floor_plan"></ImageModal>
                                        <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                                   @click="removeFloorPlan"></el-button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Upload Flat Plan:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input type="file" @change="onFlatPlanChange" accept="image/*">
                                    <div v-if="form.flat_plan">
                                        <ImageModal v-if="form.flatPlanPreview"
                                                    :image-path="form.flatPlanPreview"
                                                    :thumbnail="form.flatPlanPreview"></ImageModal>
                                        <ImageModal v-else
                                                    :image-path="form.flat_plan"
                                                    :thumbnail="form.flat_plan"></ImageModal>
                                        <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                                   @click="removeFlatPlan"></el-button>
                                    </div>
                                </div>
                            </div>
                        </el-tab-pane>
                        <el-tab-pane label="Extra" name="3">
                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Attachments:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input type="file" @change="onFileChange" multiple>
                                    <div v-if="form.attachments">
                                        <ul>
                                            <li v-for="(file, index) in form.attachments" :key="index"
                                                style="display: inline-block; margin-right: 10px">
                                                <img v-if="file.preview" :src="file.preview" alt="preview"
                                                     style="max-width: 100px;"/>
                                                <img v-else-if="file.type === 'image'" :src="file.path" alt="preview"
                                                     style="max-width: 100px;"/>
                                                <a v-else :href="file.path" target="_blank">{{ file.name }}</a>
                                                <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                                           @click="removeAttachment(index)"></el-button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Extra Details:</label>
                                <div class="col-md-10 uppercase-medium">

                                    <el-form-item
                                        v-for="(extraDetail) in form.extraDetails"
                                        :key="extraDetail.id">
                                        <div class="col-md-5 uppercase-medium">
                                            <el-input class="col-md-5" v-model="extraDetail.key"
                                                      placeholder="Name for extra detail"></el-input>
                                        </div>
                                        <div class="col-md-5 uppercase-medium">
                                            <el-input class="col-md-5" v-model="extraDetail.value"
                                                      placeholder="Value for extra detail"></el-input>
                                        </div>
                                        <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                                   @click.prevent="removeDetail(extraDetail)"></el-button>
                                    </el-form-item>
                                    <el-button type="primary" size="medium" icon="el-icon-plus" @click="addDetail">Add
                                        Extra
                                        Details
                                    </el-button>
                                </div>
                            </div>
                        </el-tab-pane>
                        <el-tab-pane label="Agreement Details" name="4">
                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Agreement Date:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <el-date-picker
                                        v-model="form.agreement_date"
                                        format="yyyy/MM/dd"
                                        type="date"
                                        value-format="yyyy/MM/dd"
                                        placeholder="Pick a agreement date">
                                    </el-date-picker>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Agreement Upload:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input type="file" @change="onAgreementChange" accept="image/*">
                                    <div v-if="form.agreement">
                                        <p v-if="form.agreement">File: <a :href="form.agreement" target="_blank">View
                                            Attachment</a>
                                        </p>
                                        <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                                   @click="removeAgreement"></el-button>
                                    </div>
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
                            <template v-if="this.form.agreement_status === 'Complete'">
                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Ownership Certificate:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <input type="file" @change="onOwnershipCertificateChange">
                                        <div v-if="form.ownership_certificate">
                                            <p v-if="form.ownership_certificate">File: <a :href="form.ownership_certificate" target="_blank">View
                                            Attachment</a>
                                            </p>
                                            <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                                       @click="removeOwnershipCertificate"></el-button>
                                        </div>
                                    </div>

                                </div>
                            </template>
                            <template v-if="this.form.agreement_status === 'Installments'">
                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Total Amount:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <input class="form-control" :disabled="loading"
                                               v-model="form.total_agreement_price"></input>
                                    </div>
                                </div>
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
                                <div v-if="form.payments.length">
                                    <el-table :data="form.payments" style="width: 100%">
                                        <el-table-column prop="number" label="Payment Number" width="150"/>
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
                        </el-tab-pane>
                        <el-tab-pane label="Current Value" name="5">
                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Current Value:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input class="form-control" :disabled="loading"
                                           v-model="form.current_value"></input>
                                </div>
                                <div v-if="form.currentValues">
                                    <el-table :data="form.currentValues" style="width: 100%">
                                        <el-table-column prop="value" label="Value" width="150"/>

                                        <el-table-column prop="date" label="Date" width="180">
                                            <template slot-scope="scope">
                                                <el-date-picker
                                                    v-model="scope.row.date"
                                                    type="date"
                                                    format="yyyy/MM/dd"
                                                    disabled
                                                    value-format="yyyy/MM/dd"
                                                ></el-date-picker>
                                            </template>
                                        </el-table-column>
                                    </el-table>
                                </div>
                            </div>
                        </el-tab-pane>
                    </el-tabs>
                </el-row>
            </el-form>
        </div>
    </div>
</template>


<script>
import {responseParse} from '../../../mixins/responseParse'
import {getData} from '../../../mixins/getData'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import MapMarker from "../../../components/admin/MapMarker.vue";
import ImageModal from "../../../components/admin/ImageModal.vue";

export default {
    components: {ImageModal, MapMarker},
    props: [
        'routes',
        'updateData',
        'item',
        'investors'
    ],
    data() {
        return {
            form: {
                extraDetails: [],
                attachments: [],
                existingAttachments: [],
                attachmentsToRemove: [],
                icon: null,
                iconPreview: null,
                payments: [],
                currency: 'USD'
            },
            loading: false,
            editor: ClassicEditor,
            addDetailIsBtnDisabled: true,
            fileList: [],
            currencies: {
                "USD": "USD",
                "GEL": "GEL",
            },
            types: {
                "Flat": "Flat",
                "Land": "Land",
                "Office": "Office",
                "Commercial Space": "Commercial Space",
                "Villa": "Villa"
            },
            conditions: {
                "Black Frame": "Black Frame",
                "White Frame": "White Frame",
                "Green Frame": "Green Frame",
                "Renovated": "Renovated"
            },
            agreementStatuses: {
                "Complete": "Complete",
                "Installments": "Installments"
            },
            activeNames: '1',
            numbers: this.getNumbersInRange(2, 50),
        }
    },
    updated() {
        this.updateData(this.form);
    },
    watch: {
        'item'() {
            if (this.item) {
                this.form = this.item;
                this.form.existingAttachments = this.item.existingAttachments || [];
            }
        }
    },
    methods: {
        getNumbersInRange(start, end) {
            let numbers = [];
            for (let i = start; i <= end; i++) {
                numbers.push(i);
            }
            return numbers;
        },

        removeDetail(item) {
            var index = this.form.extraDetails.indexOf(item);
            if (index !== 0 && index !== -1) {
                this.form.extraDetails.splice(index, 1);
            }
        },
        addDetail() {
            this.form.extraDetails.push({
                id: Date.now(),
                key: '',
                value: ''
            });
        },

        onFileChange(e) {
            const files = e.target.files;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.attachments.push({
                        file: file,
                        preview: file.type.startsWith('image/') ? e.target.result : null,
                        name: file.name,
                    });
                };
                reader.readAsDataURL(file);
            }
        },
        removeAttachment(index) {
            this.form.attachments.splice(index, 1);
            if (this.form.existingAttachments.length) {
                this.fileList.push(this.form.existingAttachments[index].id);
                this.form.attachmentsToRemove = this.fileList;
                this.form.existingAttachments.splice(index, 1);
            }
        },

        onIconChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.icon = file;
                    this.form.iconPreview = e.target.result;
                };
                reader.onerror = (error) => {
                    console.error('Error loading file:', error);
                };
                reader.readAsDataURL(file);
            }
        },
        removeIcon() {
            this.form.icon = null;
            this.form.iconPreview = null;
        },

        onFloorPlanChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.floor_plan = file;
                    this.form.floorPlanPreview = e.target.result;
                };
                reader.onerror = (error) => {
                    console.error('Error loading file:', error);
                };
                reader.readAsDataURL(file);
            }
        },
        removeFloorPlan() {
            this.form.floor_plan = null;
            this.form.floorPlanPreview = null;
        },

        onFlatPlanChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.flat_plan = file;
                    this.form.flatPlanPreview = e.target.result;
                };
                reader.onerror = (error) => {
                    console.error('Error loading file:', error);
                };
                reader.readAsDataURL(file);
            }
        },
        removeFlatPlan() {
            this.form.flat_plan = null;
            this.form.flatPlanPreview = null;
        },

        onAgreementChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.agreement = file;
                    this.form.agreementPreview = e.target.result;
                };
                reader.onerror = (error) => {
                    console.error('Error loading file:', error);
                };
                reader.readAsDataURL(file);
            }
        },
        removeAgreement() {
            this.form.agreement = null;
            this.form.agreementPreview = null;
        },

        onOwnershipCertificateChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.ownership_certificate = file;
                    this.form.ownershipCertificatePreview = e.target.result;
                };
                reader.onerror = (error) => {
                    console.error('Error loading file:', error);
                };
                reader.readAsDataURL(file);
            }
        },
        removeOwnershipCertificate() {
            this.form.ownership_certificate = null;
            this.form.ownershipCertificatePreview = null;
        },

        generatePayments() {
            this.form.payments = [];
            const totalAmount = parseFloat(this.form.total_agreement_price);
            const period = parseInt(this.form.period);
            const firstPaymentDate = new Date(this.form.first_payment_date);

            if (!totalAmount || !period || isNaN(firstPaymentDate)) {
                return;
            }

            const amountPerPeriod = (totalAmount / period).toFixed(2);
            for (let i = 0; i < period; i++) {
                let paymentDate = new Date(firstPaymentDate);
                paymentDate.setMonth(paymentDate.getMonth() + i);

                this.form.payments.push({
                    number: i + 1,
                    payment_date: paymentDate.toISOString().substring(0, 10),
                    amount: amountPerPeriod
                });
            }

            this.updateFinalPaymentAmount();
        },

        updatePaymentDate(index, date) {
            this.$set(this.form.payments, index, {
                ...this.form.payments[index],
                date
            });
        },

        updatePaymentAmount(index) {
            const totalAmount = parseFloat(this.form.total_agreement_price);
            let amountSum = this.form.payments.reduce((sum, payment, idx) => {
                return idx !== index ? sum + parseFloat(payment.amount) : sum;
            }, 0);
            const finalAmount = totalAmount - amountSum;

            this.$set(this.form.payments, index, {
                ...this.form.payments[index],
                amount: parseFloat(this.form.payments[index].amount)
            });

            this.updateFinalPaymentAmount();
        },

        updateFinalPaymentAmount() {
            const totalAmount = parseFloat(this.form.total_agreement_price);
            let amountSum = this.form.payments.slice(0, -1).reduce((sum, payment) => {
                return sum + parseFloat(payment.amount);
            }, 0);

            const finalAmount = totalAmount - amountSum;

            this.$set(this.form.payments, this.form.payments.length - 1, {
                ...this.form.payments[this.form.payments.length - 1],
                amount: finalAmount.toFixed(2)
            });
        },
    }
}

</script>
