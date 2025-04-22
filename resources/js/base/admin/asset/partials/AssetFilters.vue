<template>
    <div class="block">
        <!-- Button to toggle the visibility of the filters -->
        <el-button type="primary" @click="showFilters = !showFilters" style="margin-bottom: 20px">
            {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
        </el-button>

        <!-- Filters form, shown only if showFilters is true -->
        <el-form
            v-if="showFilters"
            ref="form"
            :model="form"
            class="form-inline form-bordered"
            @submit.prevent="applyFilters"
        >
            <el-row>
                <!-- Date Range Filter -->
                <div class="form-group date-filter">
                    <el-date-picker
                        v-model="form.agreement_date"
                        type="daterange"
                        format="yyyy/MM/dd"
                        value-format="yyyy/MM/dd"
                        start-placeholder="Asset Created At Start date"
                        end-placeholder="Asset Created At End date">
                    </el-date-picker>
                </div>

                <div class="form-group date-filter">
                    <el-date-picker
                        v-model="form.payment_date"
                        type="daterange"
                        format="yyyy/MM/dd"
                        value-format="yyyy/MM/dd"
                        start-placeholder="Payments Start date"
                        end-placeholder="Payments End date">
                    </el-date-picker>
                </div>

                <!-- Investor Filter -->
                <div class="form-group">
                    <el-select v-model="form.investor" filterable placeholder="Investor" v-remove-readonly>
                        <el-option
                            label="All"
                            value="all"
                        ></el-option>
                        <el-option
                            v-for="investor in investors"
                            :key="investor.name + ' ' + investor.surname"
                            :label="investor.name + ' ' + investor.surname"
                            :value="investor.name + ' ' + investor.surname"
                        ></el-option>
                    </el-select>
                </div>

                <!-- Asset Filter -->
                <div class="form-group">
                    <el-select v-model="form.asset" filterable placeholder="Asset Name" v-remove-readonly>
                        <el-option
                            label="All"
                            value="all"
                        ></el-option>
                        <el-option
                            v-for="asset in assets"
                            :key="asset.project_name"
                            :label="asset.project_name"
                            :value="asset.project_name"
                        ></el-option>
                    </el-select>
                </div>

                <div class="form-group">
                    <el-select v-model="form.status" filterable placeholder="Status" v-remove-readonly>
                        <el-option label="All" value="all"></el-option>
                        <el-option label="Active" value="active"></el-option>
                        <el-option label="Sold" value="sold"></el-option>
                    </el-select>
                </div>

                <div class="form-group">
                    <el-select v-model="form.manager" filterable placeholder="Manager" v-remove-readonly>
                        <el-option
                            label="All"
                            value="all"
                        ></el-option>
                        <el-option
                            v-for="manager in managers"
                            :key="manager.name + ' ' + manager.surname"
                            :label="manager.name + ' ' + manager.surname"
                            :value="manager.name + ' ' + manager.surname"
                        ></el-option>
                    </el-select>
                </div>

                <div class="form-group">
                    <el-select v-model="form.asset_status" filterable placeholder="Asset Status" v-remove-readonly>
                        <el-option label="All" value="all"></el-option>
                        <el-option
                            v-for="status in statuses"
                            :key="status.asset_status"
                            :label="status.asset_status"
                            :value="status.asset_status"
                        ></el-option>
                    </el-select>
                </div>
                <div class="form-group">
                    <el-select v-model="form.asset_type" filterable placeholder="Asset Type" v-remove-readonly>
                        <el-option
                            label="All"
                            value="all"
                        ></el-option>
                        <el-option
                            v-for="type in types"
                            :key="type.type"
                            :label="type.type"
                            :value="type.type"
                        ></el-option>
                    </el-select>
                </div>

                <div class="form-group">
                    <el-select v-model="form.agreement_status" filterable placeholder="Agreement Status"
                               v-remove-readonly>
                        <el-option label="All" value="all"></el-option>
                        <el-option label="Complete" value="Complete"></el-option>
                        <el-option label="Installments" value="Installments"></el-option>
                    </el-select>
                </div>
                <div class="button-wrapper">
                    <el-button type="primary" icon="el-icon-search" @click="applyFilters">Apply Filters</el-button>
                    <el-button type="danger" icon="el-icon-delete" @click="clearFilters">Clear Filters</el-button>
                </div>
            </el-row>
        </el-form>
    </div>
</template>

<script>
import {responseParse} from "../../../mixins/responseParse";

export default {
    data() {
        return {
            form: {
                agreement_date: '',
                payment_date: '',
                investor: '',
                status: 'active',
                asset: '',
                manager: '',
                asset_status: '',
                asset_type: '',
                agreement_status: ''
            },
            showFilters: false,
            investors: [],
            assets: [],
            managers: [],
            types: [],
            statuses: [],
        };
    },
    mounted() {
        this.loadFiltersFromQueryParams();
        this.fetchRevenueFilters();

        if (this.form.agreement_date || this.form.payment_date || this.form.investor || this.form.asset
            || this.form.status !== 'active'
            || this.form.asset_type
            || this.form.asset_status
            || this.form.manager
            || this.form.agreement_status) {
            this.showFilters = true;
        }
    },
    methods: {
        loadFiltersFromQueryParams() {
            const urlParams = new URLSearchParams(window.location.search);
            this.form.agreement_date = urlParams.get('agreement_date') ? urlParams.get('agreement_date').split(',') : '';
            this.form.payment_date = urlParams.get('payment_date') ? urlParams.get('payment_date').split(',') : '';
            this.form.investor = urlParams.get('investor') || '';
            this.form.asset = urlParams.get('asset') || '';
            this.form.asset_status = urlParams.get('asset_status') || '';
            this.form.asset_type = urlParams.get('asset_type') || '';
            this.form.agreement_status = urlParams.get('agreement_status') || '';
            this.form.manager = urlParams.get('manager') || '';
            this.form.status = urlParams.get('status') || 'active';
        },
        applyFilters() {
            const queryParams = new URLSearchParams(this.form).toString();
            window.location.search = queryParams;
        },

        // Method to clear filters
        clearFilters() {
            this.form.agreement_date = '';
            this.form.payment_date = '';
            this.form.investor = '';
            this.form.asset = '';
            this.form.asset_status = '';
            this.form.asset_type = '';
            this.form.agreement_status = '';
            this.form.manager = '';
            this.form.status = 'active';

            this.applyFilters(); // Optionally, automatically apply the cleared filters
        },

        fetchRevenueFilters() {
            axios.get('/assets/filter-options')
                .then(response => {
                    responseParse(response, false);
                    if (response.status === 200) {
                        let data = response.data.data;

                        if (data.investors) {
                            this.investors = data.investors;
                        }
                        if (data.assets) {
                            this.assets = data.assets;
                        }
                        if (data.managers) {
                            this.managers = data.managers;
                        }
                        if (data.types) {
                            this.types = data.types;
                        }
                        if (data.statuses) {
                            this.statuses = data.statuses;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching filters:', error);
                });
        }
    }
};
</script>

<style scoped>
.block {
    margin-bottom: 20px;
}
</style>
