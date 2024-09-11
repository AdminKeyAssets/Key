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
                create_date: '',
                manager: '',
            },
            managers: [],
            showFilters: false, // Controls the visibility of the filters, initially hidden
        };
    },
    mounted() {
        this.fetchLeadFilters();
        this.loadFiltersFromQueryParams();
    },
    methods: {
        loadFiltersFromQueryParams() {
            const urlParams = new URLSearchParams(window.location.search);
            this.form.create_date = urlParams.get('create_date') ? urlParams.get('create_date').split(',') : '';
            this.form.manager = urlParams.get('manager') || '';
        },
        applyFilters() {
            const queryParams = new URLSearchParams(this.form).toString();
            window.location.search = queryParams;
        },

        fetchLeadFilters() {
            axios.get('/lead/filter-options')
                .then(response => {
                    responseParse(response, false);
                    if (response.status === 200) {
                        // Response data.
                        let data = response.data.data;

                        if (data.managers) {
                            this.managers = data.managers;
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
