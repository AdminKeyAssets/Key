<template>
    <div class="block">
        <!-- Button to toggle filters visibility -->
        <el-button type="primary" @click="showFilters = !showFilters" style="margin-bottom: 20px">
            {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
        </el-button>

        <!-- Filters form, shown only if showFilters is true -->
        <el-form
            v-if="showFilters"
            ref="form" :model="form" class="form-inline form-bordered"
            @submit.prevent="applyFilters">
            <el-row>
                <div class="form-group">
                    <el-select v-model="form.citizenship" filterable placeholder="Citizenship">
                        <el-option
                            v-for="country in this.countries"
                            :key="country.country"
                            :label="country.country"
                            :value="country.country"
                        ></el-option>
                    </el-select>
                </div>

                <div class="form-group">
                    <el-date-picker
                        v-model="form.create_date"
                        type="daterange"
                        format="yyyy/MM/dd"
                        value-format="yyyy/MM/dd"
                        start-placeholder="Start date"
                        end-placeholder="End date">
                    </el-date-picker>
                </div>

                <div class="form-group">
                    <el-select v-model="form.manager" filterable placeholder="Manager">
                        <el-option
                            v-for="manager in this.managers"
                            :key="manager.name + ' ' + manager.surname"
                            :label="manager.name + ' ' + manager.surname"
                            :value="manager.name + ' ' + manager.surname"
                        ></el-option>
                    </el-select>
                </div>

                <div class="form-group">
                    <el-input class="el-input--is-round" placeholder="Number of Assets" maxlength="150"
                              show-word-limit
                              v-model="form.assets"></el-input>
                </div>

                <el-button type="secondary" icon="el-icon-search" @click="applyFilters">Apply Filters</el-button>
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
                assets: '',
                create_date: '',
                citizenship: '',
                manager: '',
            },
            countries: [],
            managers: [],
            showFilters: false, // Controls the visibility of the filters, initially hidden
        };
    },
    mounted() {
        this.fetchInvestorFilters();
        this.loadFiltersFromQueryParams();

        if (this.form.assets ||
            (this.form.create_date && this.form.create_date.length > 0) ||
            this.form.citizenship ||
            this.form.manager) {
            this.showFilters = true;
        }
    },
    methods: {
        loadFiltersFromQueryParams() {
            const urlParams = new URLSearchParams(window.location.search);
            this.form.assets = urlParams.get('assets') || '';
            this.form.create_date = urlParams.get('create_date') ? urlParams.get('create_date').split(',') : '';
            this.form.citizenship = urlParams.get('citizenship') || '';
            this.form.manager = urlParams.get('manager') || '';
        },
        applyFilters() {
            const queryParams = new URLSearchParams(this.form).toString();
            window.location.search = queryParams;
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
