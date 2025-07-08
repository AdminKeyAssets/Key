<template>
    <div>
        <!-- Asset Type -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Asset Type:</label>
            <div class="col-md-3 uppercase-medium">
                <el-select
                    v-model="form.type"
                    filterable
                    placeholder="Select Type"
                >
                    <el-option
                        v-for="type in types"
                        :key="type"
                        :label="type"
                        :value="type"
                    ></el-option>
                </el-select>
            </div>
        </div>

        <!-- Floor -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Floor:</label>
            <div class="col-md-10 uppercase-medium">
                <input
                    type="number"
                    class="form-control"
                    :disabled="loading"
                    v-model="form.floor"
                />
            </div>
        </div>

        <!-- Flat Number -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Unit number:</label>
            <div class="col-md-10 uppercase-medium">
                <input
                    class="form-control"
                    :disabled="loading"
                    v-model="form.flat_number"
                />
            </div>
        </div>

        <!-- Area -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Area (m2):</label>
            <div class="col-md-10 uppercase-medium">
                <input
                    class="form-control"
                    type="number"
                    :disabled="loading"
                    v-model="form.area"
                />
            </div>
        </div>

        <!-- Delivery Condition -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Delivery Condition:</label>
            <div class="col-md-3 uppercase-medium">
                <el-select
                    v-model="form.condition"
                    filterable
                    placeholder="Delivery Condition"
                >
                    <el-option
                        v-for="condition in conditions"
                        :key="condition"
                        :label="condition"
                        :value="condition"
                    ></el-option>
                </el-select>
            </div>
        </div>

        <!-- Condition Description -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Condition Description:</label>
            <div class="col-md-10 uppercase-medium">
                <el-input
                    type="textarea"
                    autosize
                    placeholder="Condition Description"
                    :disabled="loading"
                    v-model="form.delivery_condition_description"
                ></el-input>
            </div>
        </div>

        <!-- Cadastral Number -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Cadastral Number:</label>
            <div class="col-md-10 uppercase-medium">
                <input
                    class="form-control"
                    :disabled="loading"
                    v-model="form.cadastral_number"
                />
            </div>
        </div>

        <!-- Select Investor -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Select Investor:</label>
            <div class="col-md-10 uppercase-medium">
                <el-select
                    v-model="form.investor_ids"
                    multiple
                    filterable
                    placeholder="Select"
                >
                    <el-option
                        v-for="item in investors"
                        :key="item.id"
                        :label="item.name + ' ' + item.surname"
                        :value="item.id"
                    ></el-option>
                </el-select>
            </div>
        </div>
        
        <!-- Select Asset Manager -->
        <ManagerSelection 
            v-model="form.manager_id"
            :asset-id="form.id" 
            @input="updateManagerId"
        />

        <div class="form-group">

            <label class="col-md-1 control-label">Developer Access: </label>
            <div class="col-md-10">
                <el-switch v-model="form.developer_access">
                </el-switch>
            </div>

        </div>

        <!-- Upload Floor Plan -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Upload Floor Plan:</label>
            <div class="col-md-10 uppercase-medium">
                <input type="file" @change="onFloorPlanChange" accept="image/*" />
                <div v-if="form.floor_plan">
                    <ImageModal
                        v-if="form.floorPlanPreview"
                        :image-path="form.floorPlanPreview"
                        :thumbnail="form.floorPlanPreview"
                    ></ImageModal>
                    <ImageModal
                        v-else
                        :image-path="form.floor_plan"
                        :thumbnail="form.floor_plan"
                    ></ImageModal>
                    <el-button
                        icon="el-icon-delete-solid"
                        size="small"
                        type="danger"
                        @click="removeFloorPlan"
                    ></el-button>
                </div>
            </div>
        </div>

        <!-- Upload Flat Plan -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Upload Flat Plan:</label>
            <div class="col-md-10 uppercase-medium">
                <input type="file" @change="onFlatPlanChange" accept="image/*" />
                <div v-if="form.flat_plan">
                    <ImageModal
                        v-if="form.flatPlanPreview"
                        :image-path="form.flatPlanPreview"
                        :thumbnail="form.flatPlanPreview"
                    ></ImageModal>
                    <ImageModal
                        v-else
                        :image-path="form.flat_plan"
                        :thumbnail="form.flat_plan"
                    ></ImageModal>
                    <el-button
                        icon="el-icon-delete-solid"
                        size="small"
                        type="danger"
                        @click="removeFlatPlan"
                    ></el-button>
                </div>
            </div>
        </div>

        <!-- Extra Details -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Extra Details:</label>
            <div class="col-md-10 uppercase-medium">
                <el-form-item
                    v-for="(extraDetail, index) in form.extraDetails"
                    :key="extraDetail.id"
                >
                    <div class="col-md-3 uppercase-medium">
                        <el-input
                            class="col-md-12"
                            v-model="extraDetail.key"
                            placeholder="Name"
                        ></el-input>
                    </div>
                    <div class="col-md-3 uppercase-medium">
                        <el-input
                            class="col-md-12"
                            v-model="extraDetail.provider"
                            placeholder="Provider"
                        ></el-input>
                    </div>
                    <div class="col-md-3 uppercase-medium">
                        <el-input
                            class="col-md-12"
                            v-model="extraDetail.value"
                            placeholder="Value"
                        ></el-input>
                    </div>
                    <div class="col-md-3 uppercase-medium">
                        <input
                            type="file"
                            @change="onExtraDetailFileChange($event, index)"
                            v-if="!extraDetail.attachment"
                        />
                        <div v-if="extraDetail.attachment">
                            <img
                                v-if="extraDetail.attachment.preview"
                                :src="extraDetail.attachment.preview"
                                alt="preview"
                                style="max-width: 100px;"
                            />
                            <a v-else :href="extraDetail.attachment" target="_blank">
                                View Attachment
                            </a>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <el-button
                            icon="el-icon-delete-solid"
                            size="small"
                            type="danger"
                            @click.prevent="removeDetail(extraDetail)"
                        ></el-button>
                    </div>
                </el-form-item>
                <el-button
                    type="primary"
                    size="medium"
                    icon="el-icon-plus"
                    @click="addDetail"
                >
                    Add Extra Details
                </el-button>
            </div>
        </div>

        <!-- Asset Status -->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Asset Status:</label>
            <div class="col-md-3 uppercase-medium">
                <el-select v-model="form.asset_status" placeholder="Select Status" v-remove-readonly>
                    <el-option label="Vacant" value="Vacant"></el-option>
                    <el-option label="Under Renovation" value="Under Renovation"></el-option>
                    <el-option label="Under Construction" value="Under Construction"></el-option>
                    <el-option label="Rented" value="Rented"></el-option>
                </el-select>
            </div>
        </div>

        <!-- Complete Rent Button (only shows if asset is rented) -->
        <div class="form-group dashed" v-if="form.asset_status === 'Rented'">
            <div class="col-md-offset-1 col-md-3">
                <el-button type="primary" @click="openCompleteRentDialog">
                    Complete Rent
                </el-button>
            </div>
        </div>

        <!-- Tenant Details and Rentals -->
        <div v-if="form.asset_status === 'Rented'">
            <TenantDetails
                :tenant="tenant"
                :loading="loading"
                :countries="item.countries"
                :prefixes="item.prefixes"
                @update-tenant="updateTenant"
                @generate-rental-list="generateRentalList"
            />
            <div v-if="rentals.length">
                <el-table :data="rentals" style="width: 100%">
                    <el-table-column prop="number" label="Payment" width="150" />
                    <el-table-column prop="payment_date" label="Payment Date" width="180">
                        <template slot-scope="scope">
                            <el-date-picker
                                v-model="scope.row.payment_date"
                                type="date"
                                format="yyyy/MM/dd"
                                value-format="yyyy/MM/dd"
                                @change="updateRentalDate(scope.$index, scope.row.payment_date)"
                            ></el-date-picker>
                        </template>
                    </el-table-column>
                    <el-table-column prop="amount" label="Amount" width="180">
                        <template slot-scope="scope">
                            <el-input
                                type="number"
                                v-model="scope.row.amount"
                                @input="updateRentalAmount(scope.$index)"
                            ></el-input>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </div>

        <!-- Complete Rent Modal -->
        <el-dialog
            title="Complete Rent"
            :visible.sync="completeRentDialog"
            width="30%"
            @close="resetRentCompletionDate"
        >
            <el-form :model="{ rentCompletionDate }">
                <el-form-item label="Completion Date">
                    <el-date-picker
                        v-model="rentCompletionDate"
                        type="date"
                        placeholder="Select date"
                        format="yyyy/MM/dd"
                        value-format="yyyy/MM/dd"
                    ></el-date-picker>
                </el-form-item>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button @click="completeRentDialog = false">Cancel</el-button>
                <el-button
                    type="primary"
                    :disabled="!rentCompletionDate"
                    @click="completeRentWithDate"
                >
                    Complete
                </el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
import ImageModal from "../../../../components/admin/ImageModal.vue";
import TenantDetails from "./TenantDetails.vue";
import ManagerSelection from "./ManagerSelection.vue";
import axios from "axios";

export default {
    name: "AssetFormComponent",
    components: { ImageModal, TenantDetails, ManagerSelection },
    props: [
        "form",
        "loading",
        "types",
        "currencies",
        "conditions",
        "investors",
        "updateData",
        "item",
    ],
    data() {
        return {
            currency: "USD",
            tenant: {
                name: "",
                surname: "",
                id_number: "",
                citizenship: "",
                email: "",
                phone: "",
                agreement_date: "",
                agreement_term: "",
                monthly_rent: 0,
                currency: "USD",
                representative: "",
            },
            rentals: [],
            updatingTotalPrice: false,
            updatingSqmPrice: false,
            completeRentDialog: false,
            rentCompletionDate: this.formatDate(new Date()),
        };
    },
    mounted() {
        // set default currency once
        if (this.form && !this.form.currency) {
            this.$emit("update-form", { ...this.form, currency: "USD" });
        }
        // initialize tenant & rentals from form
        if (this.form.tenant && this.form.tenant.id) {
            this.tenant = { ...this.form.tenant };
        }
        this.rentals = this.form.rentals ? [...this.form.rentals] : [];
    },
    watch: {
        'form.price': { handler: 'updateTotalPrice', immediate: false },
        'form.area': { handler: 'updateTotalPrice', immediate: false },
        'form.total_price': { handler: 'updateSqmPrice', immediate: false },
        'form.tenant.id'(newVal) {
            if (newVal) this.tenant = { ...this.form.tenant };
        },
        'form.rentals'(newVal) {
            this.rentals = newVal ? [...newVal] : [];
        },
    },
    methods: {
        onFloorPlanChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (evt) => {
                    this.$emit("update-form", {
                        ...this.form,
                        floor_plan: file,
                        floorPlanPreview: evt.target.result,
                    });
                };
                reader.readAsDataURL(file);
            }
        },
        removeFloorPlan() {
            this.$emit("update-form", {
                ...this.form,
                floor_plan: null,
                floorPlanPreview: null,
            });
        },
        onFlatPlanChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (evt) => {
                    this.$emit("update-form", {
                        ...this.form,
                        flat_plan: file,
                        flatPlanPreview: evt.target.result,
                    });
                };
                reader.readAsDataURL(file);
            }
        },
        removeFlatPlan() {
            this.$emit("update-form", {
                ...this.form,
                flat_plan: null,
                flatPlanPreview: null,
            });
        },
        updateTenant(newTenant) {
            this.$emit("update-form", { ...this.form, tenant: newTenant });
        },
        generateRentalList() {
            if (!this.tenant.agreement_date || !this.tenant.agreement_term || !this.tenant.monthly_rent) {
                this.$message.error("Please fill in all required fields for the rental agreement.");
                return;
            }
            const term = parseInt(this.tenant.agreement_term, 10);
            const agreementDate = new Date(this.tenant.agreement_date);
            const rentals = [];
            for (let i = 0; i < term; i++) {
                const pd = new Date(agreementDate);
                pd.setMonth(pd.getMonth() + i);
                rentals.push({ number: i + 1, payment_date: this.formatDate(pd), amount: this.tenant.monthly_rent, status: "Pending" });
            }
            this.rentals = rentals;
            this.$emit("update-form", { ...this.form, rentals: this.rentals });
        },
        formatDate(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}/${m}/${d}`;
        },
        updateTotalPrice() {
            if (this.updatingSqmPrice) return;
            const price = parseFloat(this.form.price) || 0;
            const area = parseFloat(this.form.area) || 0;
            const totalPrice = price * area;
            this.updatingTotalPrice = true;
            this.$emit("update-form", { ...this.form, total_price: totalPrice });
            this.$emit("update-form", { ...this.form, current_value: totalPrice });
            this.updatingTotalPrice = false;
        },
        updateSqmPrice() {
            if (this.updatingTotalPrice) return;
            const totalPrice = parseFloat(this.form.total_price) || 0;
            const area = parseFloat(this.form.area) || 0;
            if (area === 0) return;
            const price = totalPrice / area;
            this.updatingSqmPrice = true;
            this.$emit("update-form", { ...this.form, price });
            this.updatingSqmPrice = false;
        },
        updateRentalDate(index, date) {
            this.$set(this.rentals, index, { ...this.rentals[index], payment_date: date });
        },
        updateRentalAmount(index) {
            const totalAmount = parseFloat(this.tenant.monthly_rent) * parseInt(this.tenant.agreement_term, 10);
            let sum = this.rentals.reduce((s, r, idx) => idx !== index ? s + parseFloat(r.amount) : s, 0);
            this.$set(this.rentals, index, { ...this.rentals[index], amount: parseFloat(this.rentals[index].amount) });
        },
        onExtraDetailFileChange(e, idx) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (evt) => {
                    const details = [...this.form.extraDetails];
                    details[idx].attachment = { file, preview: file.type.startsWith('image/') ? evt.target.result : null, name: file.name };
                    this.$emit("update-form", { ...this.form, extraDetails: details });
                };
                reader.readAsDataURL(file);
            }
        },
        addDetail() {
            const list = Array.isArray(this.form.extraDetails) ? [...this.form.extraDetails] : [];
            list.push({ id: Date.now(), key: '', provider: '', value: '', attachment: null });
            this.$emit("update-form", { ...this.form, extraDetails: list });
        },
        removeDetail(item) {
            const newList = Array.isArray(this.form.extraDetails) ? this.form.extraDetails.filter(d => d.id !== item.id) : [];
            this.$emit("update-form", { ...this.form, extraDetails: newList });
        },
        openCompleteRentDialog() {
            this.rentCompletionDate = this.formatDate(new Date());
            this.completeRentDialog = true;
        },
        resetRentCompletionDate() {
            this.rentCompletionDate = this.formatDate(new Date());
        },
        async completeRentWithDate() {
            try {
                const response = await axios.post(`assets/${this.form.id}/rental/complete`, {
                    assetId: this.form.id,
                    completionDate: this.rentCompletionDate,
                });
                if (response.status === 200) {
                    this.$message.success("Request successful. Page will be refreshed.");
                    location.reload();
                }
            } catch (error) {
                this.$message.error("There was an error completing the rent.");
            } finally {
                this.completeRentDialog = false;
            }
        },
        
        updateManagerId(managerId) {
            this.$emit("update-form", { ...this.form, manager_id: managerId });
        }
    }
};
</script>
