<template>
    <div>
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
            <label class="col-md-1 control-label">M2 Price:</label>
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

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Asset Status:</label>
            <div class="col-md-3 uppercase-medium">
                <el-select v-model="form.asset_status" placeholder="Select Status">
                    <el-option label="Vacant" value="Vacant"></el-option>
                    <el-option label="Under Renovation" value="Under Renovation"></el-option>
                    <el-option label="Rented" value="Rented"></el-option>
                </el-select>
            </div>
        </div>

        <div v-if="form.asset_status === 'Rented'">
            <TenantDetails
                :tenant="tenant"
                :loading="loading"
                :countries="item.countries"
                :prefixes="item.prefixes"
                @update-tenant="updateTenant"
                @generate-rental-list="generateRentalList"/>
            <div v-if="rentals.length">
                <el-table :data="rentals" style="width: 100%">
                    <el-table-column prop="number" label="Payment" width="150"/>
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
    </div>
</template>

<script>
import ImageModal from "../../../../components/admin/ImageModal.vue";
import TenantDetails from './TenantDetails.vue';

export default {
    components: { ImageModal, TenantDetails },
    props: ['form', 'loading', 'types', 'currencies', 'conditions', 'investors', 'updateData', 'item'],
    data() {
        return {
            tenant: {
                name: '',
                surname: '',
                id_number: '',
                citizenship: '',
                email: '',
                phone: '',
                agreement_date: '',
                agreement_term: '',
                monthly_rent: 0,
                currency: 'USD',
            },
            rentals: [],
        }
    },
    created(){
        console.log(this.form.tenant)
    },
    watch: {
        'form.price': 'updateTotalPrice',
        'form.area': 'updateTotalPrice',
        'form'() {
            if (this.form) {
                this.tenant = this.form.tenant || [];
                this.rentals = this.form.rentals || [];
            }
        }
    },
    methods: {
        onFloorPlanChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.$emit('update-form', { ...this.form, floor_plan: file, floorPlanPreview: e.target.result });
                };
                reader.onerror = (error) => {
                    console.error('Error loading file:', error);
                };
                reader.readAsDataURL(file);
            }
        },
        removeFloorPlan() {
            this.$emit('update-form', { ...this.form, floor_plan: null, floorPlanPreview: null });
        },

        onFlatPlanChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.$emit('update-form', { ...this.form, flat_plan: file, flatPlanPreview: e.target.result });
                };
                reader.onerror = (error) => {
                    console.error('Error loading file:', error);
                };
                reader.readAsDataURL(file);
            }
        },
        removeFlatPlan() {
            this.$emit('update-form', { ...this.form, flat_plan: null, flatPlanPreview: null });
        },

        updateTenant(newTenant) {
            this.$emit('update-form', { ...this.form, tenant: newTenant });
        },

        generateRentalList() {
            if (!this.tenant.agreement_date || !this.tenant.agreement_term || !this.tenant.monthly_rent) {
                this.$message.error('Please fill in all required fields for the rental agreement.');
                return;
            }

            const term = parseInt(this.tenant.agreement_term, 10);
            const agreementDate = new Date(this.tenant.agreement_date);
            const rentals = [];

            for (let i = 0; i < term; i++) {
                const paymentDate = new Date(agreementDate);
                paymentDate.setMonth(paymentDate.getMonth() + i);

                rentals.push({
                    number: i + 1,
                    date: this.formatDate(paymentDate),
                    amount: this.tenant.monthly_rent,
                    status: 'Pending'
                });
            }

            this.rentals = rentals;
            this.$emit('update-form', { ...this.form, rentals: this.rentals });
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
            this.$emit('update-form', { ...this.form, total_price: totalPrice });
        },

        updateRentalDate(index, date) {
            this.$set(this.rentals, index, {
                ...this.rentals[index],
                payment_date: date
            });
        },
        updateRentalAmount(index) {
            const totalAmount = parseFloat(this.tenant.monthly_rent) * parseInt(this.tenant.agreement_term, 10);
            let amountSum = this.rentals.reduce((sum, rental, idx) => {
                return idx !== index ? sum + parseFloat(rental.amount) : sum;
            }, 0);
            const finalAmount = totalAmount - amountSum;

            this.$set(this.rentals, index, {
                ...this.rentals[index],
                amount: parseFloat(this.rentals[index].amount)
            });

            this.updateFinalRentalAmount(this.rentals);
        },
        updateFinalRentalAmount(rentals) {
            const totalAmount = parseFloat(this.tenant.monthly_rent) * parseInt(this.tenant.agreement_term, 10);
            let amountSum = rentals.slice(0, -1).reduce((sum, rental) => {
                return sum + parseFloat(rental.amount);
            }, 0);

            const finalAmount = totalAmount - amountSum;

            this.$set(rentals, rentals.length - 1, {
                ...rentals[rentals.length - 1],
                amount: finalAmount.toFixed(2)
            });
        },
    }
}
</script>
