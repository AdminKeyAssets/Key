<template>
    <div class="block">
        <!-- Button to toggle filters visibility -->
        <el-button type="primary" @click="showFilters = !showFilters" style="margin-bottom: 20px">
            {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
        </el-button>

        <!-- Filters form, shown only if showFilters is true -->
        <el-form
            v-if="showFilters"
            :key="'filters-' + showFilters"
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
                    <el-input 
                        v-model="form.search" 
                        placeholder="Search title or content..."
                        clearable>
                    </el-input>
                </div>

                <!-- Status Filter -->
                <div class="form-group">
                    <el-select v-model="form.status" filterable placeholder="Status" v-remove-readonly>
                        <el-option label="All" value="all"></el-option>
                        <el-option label="Draft" value="draft"></el-option>
                        <el-option label="Published" value="published"></el-option>
                    </el-select>
                </div>

                <!-- Manager Filter (only for admins) -->
                <div class="form-group" v-if="isAdmin && managers.length > 0">
                    <el-select v-model="form.manager" filterable placeholder="Manager" v-remove-readonly>
                        <el-option label="All" value="all"></el-option>
                        <el-option
                            v-for="manager in managers"
                            :key="manager.id"
                            :label="manager.full_name || (manager.name + ' ' + manager.surname)"
                            :value="manager.full_name || (manager.name + ' ' + manager.surname)"
                        ></el-option>
                    </el-select>
                </div>

                <!-- Investor Filter -->
                <div class="form-group">
                    <el-select v-model="form.investor" filterable placeholder="Investor" v-remove-readonly>
                        <el-option label="All" value="all"></el-option>
                        <el-option
                            v-for="investor in investors"
                            :key="investor.id"
                            :label="investor.full_name || (investor.name + ' ' + investor.surname)"
                            :value="investor.full_name || (investor.name + ' ' + investor.surname)"
                        ></el-option>
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
        'isAdmin',
        'isDeveloper'
    ],
    data() {
        return {
            form: {
                search: '',
                status: 'all',
                create_date: '',
                manager: '',
                investor: '',
            },
            managers: [],
            investors: [],
            showFilters: false, // Controls the visibility of the filters, initially hidden
        };
    },
    mounted() {
        this.fetchNewsFilters();
        this.loadFiltersFromQueryParams();

        // Show filters if any filter is already applied
        if (this.form.search ||
            this.form.status !== 'all' ||
            (this.form.create_date && this.form.create_date.length > 0) ||
            this.form.manager ||
            this.form.investor) {
            this.showFilters = true;
        }
    },
    methods: {
        loadFiltersFromQueryParams() {
            const urlParams = new URLSearchParams(window.location.search);
            this.form.search = urlParams.get('search') || '';
            this.form.status = urlParams.get('status') || 'all';
            this.form.create_date = urlParams.get('create_date') ? urlParams.get('create_date').split(',') : '';
            this.form.manager = urlParams.get('manager') || '';
            this.form.investor = urlParams.get('investor') || '';
        },
        applyFilters() {
            const queryParams = new URLSearchParams(this.form).toString();
            window.location.search = queryParams;
        },

        clearFilters() {
            this.form.search = '';
            this.form.status = 'all';
            this.form.create_date = '';
            this.form.manager = '';
            this.form.investor = '';
            this.applyFilters(); // Apply cleared filters
        },

        fetchNewsFilters() {
            // Determine the correct endpoint based on user role
            let endpoint = '/admin/news/filter-options';
            if (this.isDeveloper) {
                endpoint = '/developer/news/filter-options';
            }

            axios.get(endpoint)
                .then(response => {
                    responseParse(response, false);
                    console.log(response);
                    
                    if (response.status === 200) {
                        // Response data.
                        let data = response.data.data;

                        if (data.managers) {
                            this.managers = data.managers;
                        }

                        if (data.investors) {
                            this.investors = data.investors;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching news filters:', error);
                });
        }
    }
};
</script>

<style scoped>
.block {
    margin-bottom: 20px;
}

.form-group {
    margin-right: 10px;
    margin-bottom: 10px;
}

.date-filter {
    min-width: 250px;
}

.button-wrapper {
    margin-left: auto;
}
</style>
