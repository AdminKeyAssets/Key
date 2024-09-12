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
                        <label class="col-md-2 control-label">Project: <span class="text-danger">*</span></label>
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
                        <label class="col-md-2 control-label">Investor: <span class="text-danger">*</span></label>
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

                    <div class="form-group dashed">
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

                    <div class="form-group">
                        <label class="col-md-1 control-label">Total Price:</label>
                        <div class="col-md-10 uppercase-medium">
                            <input class="form-control" type="number" :disabled="loading"
                                   v-model="form.total_price"></input>
                        </div>
                    </div>

                    <div class="form-group">
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

                    <template v-if="form.agreement_status === 'Installments'">

                        <div class="form-group dashed">
                            <label class="col-md-1 control-label">Down Payment:</label>
                            <div class="col-md-10 uppercase-medium">
                                <input class="form-control" :disabled="loading" v-model="form.down_payment"></input>
                            </div>
                        </div>

                        <div class="form-group">
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
                    </template>
                </el-row>

            </el-form>
        </div>
    </div>
</template>


<script>
import {responseParse} from '../../../mixins/responseParse'
import {getData} from '../../../mixins/getData'
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
        }
    },
    updated() {
        this.updateData(this.form);
    },
    watch: {
        'item'() {
            if (this.item) {
                this.form = this.item;
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
    },
}

</script>
