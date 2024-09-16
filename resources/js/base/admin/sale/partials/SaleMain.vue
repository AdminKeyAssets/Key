<template>
    <div>
        <div class="block">
            <el-form v-loading="loading"
                     element-loading-text="Loading..."
                     element-loading-spinner="el-icon-loading"
                     element-loading-background="rgba(0, 0, 0, 0.0)"
                     ref="form" :model="form" class="form-horizontal form-bordered">

                <el-row>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Project: </label>
                        <div class="col-md-6">
                            <el-autocomplete
                                class="inline-input"
                                v-model="form.project"
                                :fetch-suggestions="querySearchProjects"
                                placeholder="Type Project"
                                :trigger-on-focus="false"
                            ></el-autocomplete>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Investor: </label>
                        <div class="col-md-6">
                            <el-autocomplete
                                class="inline-input"
                                v-model="form.investor"
                                :fetch-suggestions="querySearchInvestors"
                                placeholder="Type Investor"
                                :trigger-on-focus="false"
                            ></el-autocomplete>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Asset Type:</label>
                        <div class="col-md-6 uppercase-medium">
                            <el-select
                                v-model="form.type"
                                :value="form.type"
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

                    <div class="form-group">
                        <label class="col-md-2 control-label">Area (m2):</label>
                        <div class="col-md-6 uppercase-medium">
                            <input
                                class="form-control"
                                type="number"
                                :disabled="loading"
                                v-model="form.size"
                            />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">M2 Price:</label>
                        <div class="col-md-6 uppercase-medium">
                            <input class="form-control" type="number" :disabled="loading" v-model="form.price"></input>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Total Price:</label>
                        <div class="col-md-6 uppercase-medium">
                            <input class="form-control" type="number" :disabled="loading"
                                   v-model="form.total_price"></input>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Agreement Date:</label>
                        <div class="col-md-6 uppercase-medium">
                            <el-date-picker
                                v-model="form.agreement_date"
                                format="yyyy/MM/dd"
                                type="date"
                                value-format="yyyy/MM/dd"
                                placeholder="Pick an agreement date">
                            </el-date-picker>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Agreement Status:</label>
                        <div class="col-md-6 uppercase-medium">
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

                    <template v-if="form.agreement_status === 'Installments'">

                        <div class="form-group">
                            <label class="col-md-2 control-label">Down Payment:</label>
                            <div class="col-md-6 uppercase-medium">
                                <input class="form-control" :disabled="loading" v-model="form.down_payment"></input>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-2 control-label">Period:</label>
                            <div class="col-md-6 uppercase-medium">
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
                    </template>
                </el-row>

            </el-form>
        </div>
    </div>
</template>

<script>
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {
    props: [
        'routes',
        'updateData',
        'item',
        'investors',
        'projects',
    ],
    data() {
        return {
            form: {
                currency: "USD",
                size: 0,
                price: 0,
                total_price: 0
            },
            loading: false,
            editor: ClassicEditor,
            types: {
                "Flat": "Flat",
                "Land": "Land",
                "Office": "Office",
                "Commercial Space": "Commercial Space",
                "Villa": "Villa"
            },
            agreementStatuses: {
                "Complete": "Complete",
                "Installments": "Installments"
            },
            currencies: {
                "USD": "USD",
                "GEL": "GEL",
            },
            numbers: this.getNumbersInRange(2, 50),
            updatingTotalPrice: false, // Flag to prevent recursion
            updatingSqmPrice: false,
        }
    },
    watch: {
        'item'() {
            if (this.item) {
                this.form = this.item;
            }
        },
        'form.size'(newSize) {
            if (!this.updatingTotalPrice && !this.updatingSqmPrice) {
                this.calculateTotalPrice();
            }
        },
        'form.price'(newPrice) {
            if (!this.updatingTotalPrice && !this.updatingSqmPrice) {
                this.calculateTotalPrice();
            }
        },
        'form.total_price'(newTotalPrice) {
            if (!this.updatingTotalPrice && !this.updatingSqmPrice) {
                this.calculatePriceFromTotal();
            }
        }
    },
    methods: {
        querySearchProjects(queryString, cb) {
            let projects = this.projects || [];
            let results = queryString ? projects.filter(this.createFilter(queryString)) : projects;
            console.log(results); // Debugging
            cb(results);
        },
        querySearchInvestors(queryString, cb) {
            let investors = this.investors || [];
            let results = queryString ? investors.filter(this.createFilter(queryString)) : investors;
            console.log(results); // Debugging
            cb(results);
        },
        createFilter(queryString) {
            return (item) => {
                return item.value.toLowerCase().includes(queryString.toLowerCase());
            };
        },
        getNumbersInRange(start, end) {
            let numbers = [];
            for (let i = start; i <= end; i++) {
                numbers.push(i);
            }
            return numbers;
        },
        calculateTotalPrice() {
            if (this.form.size && this.form.price) {
                this.updatingTotalPrice = true;
                this.form.total_price = this.form.size * this.form.price;
                this.updatingTotalPrice = false;
            }
        },
        calculatePriceFromTotal() {
            if (this.form.size && this.form.total_price) {
                this.updatingSqmPrice = true;
                this.form.price = this.form.total_price / this.form.size;
                this.updatingSqmPrice = false;
            }
        }
    }
}
</script>

