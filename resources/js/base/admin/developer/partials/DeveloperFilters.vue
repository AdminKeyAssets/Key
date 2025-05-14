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
            },
            countries: [],
            managers: [],
            developers: [],
            showFilters: false, // Controls the visibility of the filters, initially hidden
        };
    },
    mounted() {
        this.fetchDeveloperFilters();
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
            this.applyFilters(); // Optionally apply cleared filters
        },

        fetchDeveloperFilters() {
            axios.get('/admin/developers/filter-options')
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

                        if (data.developers) {
                            this.developers = data.developers;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching developers:', error);
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
