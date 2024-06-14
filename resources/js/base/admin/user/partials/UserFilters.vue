<template>
    <div class="block">
        <el-form
            ref="form" :model="form" class="form-inline form-bordered"
            @submit.prevent="applyFilters">
            <el-row>
                <div class="form-group">
                    <el-select v-model="form.role" filterable
                               placeholder="Roles">
                        <el-option
                            v-for="role in this.roles"
                            :key="role.name"
                            :label="role.name"
                            :value="role.name"
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
                role: '',
                create_date: '',
            },
            roles: [],
        };
    },
    mounted() {
        this.fetchUsersFilters();
        this.loadFiltersFromQueryParams();
    },
    methods: {
        loadFiltersFromQueryParams() {
            const urlParams = new URLSearchParams(window.location.search);
            this.form.role = urlParams.get('role') || '';
            this.form.create_date = urlParams.get('create_date') ? urlParams.get('create_date').split(',') : '';
        },
        applyFilters() {
            const queryParams = new URLSearchParams(this.form).toString();
            window.location.search = queryParams;
        },

        fetchUsersFilters() {
            axios.get('/admin/users/filter-options')
                .then(response => {
                    responseParse(response, false);
                    if (response.status === 200) {
                        // Response data.
                        let data = response.data.data;

                        if (data.roles) {
                            this.roles = data.roles;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching users:', error);
                });
        }
    }
};
</script>

<style>

</style>
