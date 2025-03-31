<template>
    <div class="block">
        <el-button type="primary" @click="showFilters = !showFilters" style="margin-bottom: 20px">
            {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
        </el-button>

        <el-form
            v-if="showFilters"
            ref="form" :model="form" class="form-inline form-bordered"
            @submit.prevent="applyFilters">
            <el-row>
                <!-- Search Box -->
                <div class="form-group">
                    <el-select v-model="form.search" filterable placeholder="User Names" v-remove-readonly>
                        <el-option
                            label="All"
                            value="all"
                        ></el-option>
                        <el-option
                            v-for="user in users"
                            :key="user.name + (user.surname ? ' ' + user.surname : '')"
                            :label="user.name + (user.surname ? ' ' + user.surname : '')"
                            :value="user.name + (user.surname ? ' ' + user.surname : '')"
                        ></el-option>

                    </el-select>
                </div>

                <div class="form-group">
                    <el-select v-model="form.role" filterable placeholder="Roles" v-remove-readonly>
                        <el-option
                            v-for="role in roles"
                            :key="role.name"
                            :label="role.name"
                            :value="role.name">
                        </el-option>
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
                search: '',
                role: '',
                create_date: '',
            },
            roles: [],
            users: [],
            showFilters: false,
        };
    },
    mounted() {
        this.fetchUsersFilters();
        this.loadFiltersFromQueryParams();

        if ((this.form.create_date && this.form.create_date.length > 0) ||
            this.form.role ||
            this.form.search) {
            this.showFilters = true;
        }
    },
    directives: {
        removeReadonly: {
            inserted(el) {
                if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                    // Delay slightly to ensure inner input is rendered
                    setTimeout(() => {
                        const input = el.querySelector('.el-input__inner');
                        if (input) {
                            input.removeAttribute('readonly');
                        }
                    }, 0);
                }
            }
        }
    },
    methods: {
        openKeyboard() {
            this.$nextTick(() => {
                const input = this.$refs.investorSelect.$el.querySelector('.el-input__inner');
                if (input) {
                    input.focus();
                }
            });
        },
        loadFiltersFromQueryParams() {
            const urlParams = new URLSearchParams(window.location.search);
            this.form.search = urlParams.get('search') || '';
            this.form.role = urlParams.get('role') || '';
            this.form.create_date = urlParams.get('create_date') ? urlParams.get('create_date').split(',') : '';
        },
        applyFilters() {
            const queryParams = new URLSearchParams(this.form).toString();
            window.location.search = queryParams;
        },

        clearFilters() {
            this.form.search = '';
            this.form.role = '';
            this.form.create_date = '';
            this.applyFilters(); // Optionally apply cleared filters
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
                        if (data.users) {
                            this.users = data.users;
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
/* Add your styles here */
</style>
