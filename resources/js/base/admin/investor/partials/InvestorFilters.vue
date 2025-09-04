<template>
    <div class="block">
        <!-- Button to toggle filters visibility -->
        <el-button type="primary" @click="showFilters = !showFilters" style="margin-bottom: 20px">
            {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
        </el-button>

        <!-- Filters form, shown only if showFilters is true -->
        <el-form
            v-if="showFilters"
            ref="form" :model="form" class="form-inline form-bordered form-filters"
            @submit.prevent="applyFilters">
            <el-row>
                <div class="form-group date-filter">
                    <el-date-picker
                        v-model="form.create_date"
                        type="daterange"
                        format="yyyy/MM/dd"
                        value-format="yyyy/MM/dd"
                        start-placeholder="Start date"
                        end-placeholder="End date">
                    </el-date-picker>
                </div>

                <!-- Search Input -->
                <div class="form-group">
                    <el-select v-model="form.search" filterable placeholder="Investor" v-remove-readonly>
                        <el-option
                            label="All"
                            value="all"
                        ></el-option>
                        <el-option
                            v-for="investor in investors"
                            :key="investor.id"
                            :label="investor.full_name || (investor.name + ' ' + investor.surname)"
                            :value="investor.full_name || (investor.name + ' ' + investor.surname)"
                        ></el-option>
                    </el-select>
                </div>

                <div class="form-group">
                    <el-select v-model="form.citizenship" filterable placeholder="Citizenship" v-remove-readonly>
                        <el-option
                            v-for="country in countries"
                            :key="country"
                            :label="country"
                            :value="country"
                        ></el-option>
                    </el-select>
                </div>

                <div class="form-group" v-if="isAdmin">
                    <el-select v-model="form.manager" filterable placeholder="Manager" v-remove-readonly>
                        <el-option
                            v-for="manager in managers"
                            :key="manager.id"
                            :label="manager.full_name || (manager.name + ' ' + manager.surname)"
                            :value="manager.full_name || (manager.name + ' ' + manager.surname)"
                        ></el-option>
                    </el-select>
                </div>

                <div class="form-group">
                    <el-input class="el-input--is-round" placeholder="Number of Assets" maxlength="150"
                              show-word-limit
                              v-model="form.assets"></el-input>
                </div>
                <div class="form-group">
                    <el-select v-model="form.status" filterable placeholder="Status" v-remove-readonly>
                        <el-option label="Active" value="active"></el-option>
                        <el-option label="All" value="all"></el-option>
                        <el-option label="Archived" value="archived"></el-option>
                    </el-select>
                </div>
                <div class="button-wrapper">
                    <el-button type="secondary" icon="el-icon-search" @click="applyFilters">Apply Filters</el-button>
                    <el-button type="danger" icon="el-icon-delete" @click="clearFilters">Clear Filters</el-button>
                </div>
            </el-row>
        </el-form>
    </div>
</template>

<script>
import { responseParse } from "../../../mixins/responseParse";

export default {
    props: [
        'isAdmin'
    ],
    data() {
        return {
            form: {
                search: '',
                assets: '',
                create_date: '',
                citizenship: '',
                manager: '',
                status: 'active', // Default to active
            },
            countries: [],
            managers: [],
            investors: [],
            showFilters: false, // Controls the visibility of the filters, initially hidden
        };
    },
    mounted() {
        this.fetchInvestorFilters();
        this.loadFiltersFromQueryParams();

        if (this.form.assets ||
            (this.form.create_date && this.form.create_date.length > 0) ||
            this.form.citizenship ||
            this.form.manager ||
            this.form.search) {
            this.showFilters = true;
        }
    },
    methods: {
        loadFiltersFromQueryParams() {
            const urlParams = new URLSearchParams(window.location.search);
            this.form.search = urlParams.get('search') || '';
            this.form.assets = urlParams.get('assets') || '';
            this.form.create_date = urlParams.get('create_date') ? urlParams.get('create_date').split(',') : '';
            this.form.citizenship = urlParams.get('citizenship') || '';
            this.form.manager = urlParams.get('manager') || '';
            this.form.status = urlParams.get('status') || 'active';
        },
        applyFilters() {
            const queryParams = new URLSearchParams(this.form).toString();
            window.location.search = queryParams;
        },

        clearFilters() {
            this.form.search = '';
            this.form.assets = '';
            this.form.create_date = '';
            this.form.citizenship = '';
            this.form.manager = '';
            this.form.status = 'active';
            this.applyFilters(); // Optionally apply cleared filters
        },

        fetchInvestorFilters() {
            axios.get('/admin/investors/filter-options')
                .then(response => {
                    responseParse(response, false);
                    if (response.status === 200) {
                        // Response data.
                        let data = response.data.data;

                        if (data.countries) {
                            this.countries = data.countries;
                        }
                        if (data.managers) {
                            this.managers = data.managers;
                        }

                        if (data.investors) {
                            this.investors = data.investors;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching investors:', error);
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
