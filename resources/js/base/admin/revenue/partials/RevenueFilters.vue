<template>
    <div class="block">
        <!-- Button to toggle the visibility of the filters -->
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
                    <el-date-picker
                        v-model="form.agreement_date"
                        type="daterange"
                        format="yyyy/MM/dd"
                        value-format="yyyy/MM/dd"
                        start-placeholder="Start date"
                        end-placeholder="End date">
                    </el-date-picker>
                </div>

                <div class="form-group">
                    <el-select v-model="form.investor" filterable placeholder="Investor">
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

                <el-button type="primary" icon="el-icon-search" @click="applyFilters">Apply Filters</el-button>
                <el-button type="danger" icon="el-icon-delete" @click="clearFilters">Clear Filters</el-button>
            </el-row>
        </el-form>
    </div>
</template>

<script>
import { responseParse } from "../../../mixins/responseParse";

export default {
    data() {
        return {
            form: {
                agreement_date: '',
                investor: '',
            },
            showFilters: false,
            investors: []
        };
    },
    mounted() {
        this.loadFiltersFromQueryParams();
        this.fetchRevenueFilters();

        if ((this.form.agreement_date && this.form.agreement_date.length > 0) ||
            this.form.investor) {
            this.showFilters = true;
        }
    },
    methods: {
        loadFiltersFromQueryParams() {
            const urlParams = new URLSearchParams(window.location.search);
            this.form.agreement_date = urlParams.get('agreement_date') ? urlParams.get('agreement_date').split(',') : '';
            this.form.investor = urlParams.get('investor') || '';
        },
        applyFilters() {
            const queryParams = new URLSearchParams(this.form).toString();
            window.location.search = queryParams;
        },

        // New method to clear filters
        clearFilters() {
            this.form.agreement_date = '';
            this.form.investor = '';
            this.applyFilters(); // Optionally apply empty filters after clearing
        },

        fetchRevenueFilters() {
            axios.get('/assets/revenues/filter-options')
                .then(response => {
                    responseParse(response, false);
                    if (response.status === 200) {
                        let data = response.data.data;

                        if (data.investors) {
                            this.investors = data.investors;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching managers:', error);
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
