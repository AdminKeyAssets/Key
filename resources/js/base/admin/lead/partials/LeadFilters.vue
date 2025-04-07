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
                <!-- Name search input -->
                <div class="form-group">
                    <el-select v-model="form.search" filterable placeholder="Name" v-remove-readonly>
                        <el-option
                            label="All"
                            value="all"
                        ></el-option>
                        <el-option
                            v-for="lead in leads"
                            :key="lead.name + ' ' + lead.surname"
                            :label="lead.name + ' ' + lead.surname"
                            :value="lead.name + ' ' + lead.surname"
                        ></el-option>
                    </el-select>
                </div>

                <div class="form-group" v-if="this.isAdmin" v-remove-readonly>
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
                    <el-select v-model="form.marketing_channel" filterable placeholder="Marketing Channel"
                               v-remove-readonly>
                        <el-option
                            label="All"
                            value="all"
                        ></el-option>
                        <el-option
                            v-for="marketingChannel in marketingChannels"
                            :key="marketingChannel.marketing_channel"
                            :label="marketingChannel.marketing_channel"
                            :value="marketingChannel.marketing_channel"
                        ></el-option>
                    </el-select>
                </div>

                <div class="form-group">
                    <el-select v-model="form.status" filterable placeholder="Lead Status" v-remove-readonly>
                        <el-option label="All" value="all"></el-option>
                        <el-option label="Active" value="active"></el-option>
                        <el-option label="Archieve" value="archieve"></el-option>
                    </el-select>
                </div>

                <div class="form-group">
                    <el-select v-model="form.communication_status" filterable placeholder="Communication Status"
                               v-remove-readonly>
                        <el-option label="All" value="all"></el-option>
                        <el-option label="Communication" value="Communication"></el-option>
                        <el-option label="New" value="New"></el-option>
                        <el-option label="Not Responding" value="Not Responding"></el-option>
                        <el-option label="Proposal Sent" value="Proposal Sent"></el-option>
                        <el-option label="Refused" value="Refused"></el-option>
                        <el-option label="Signed" value="Signed"></el-option>
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
    props: {
        isAdmin: {
            type: [Boolean, Number],
            default: false
        },
    },
    data() {
        return {
            form: {
                search: '',
                create_date: '',
                manager: '',
                marketing_channel: '',
                status: '',
                communication_status: ''
            },
            managers: [],
            leads: [],
            marketingChannels: [],
            showFilters: false,
        };
    },
    mounted() {
        this.fetchLeadFilters();
        this.loadFiltersFromQueryParams();

        if ((this.form.create_date && this.form.create_date.length > 0) ||
            this.form.manager ||
            this.form.marketing_channel ||
            this.form.communication_status ||
            this.form.status !== 'active' ||
            this.form.search) {
            this.showFilters = true;
        }
    },
    methods: {
        loadFiltersFromQueryParams() {
            const urlParams = new URLSearchParams(window.location.search);
            this.form.search = urlParams.get('search') || '';
            this.form.create_date = urlParams.get('create_date') ? urlParams.get('create_date').split(',') : '';
            this.form.manager = urlParams.get('manager') || '';
            this.form.marketing_channel = urlParams.get('marketing_channel') || '';
            this.form.communication_status = urlParams.get('communication_status') || '';
            this.form.status = urlParams.get('status') || 'active';
        },
        applyFilters() {
            const queryParams = new URLSearchParams(this.form).toString();
            window.location.search = queryParams;
        },

        // Method to clear the filters
        clearFilters() {
            this.form.search = '';
            this.form.create_date = '';
            this.form.manager = '';
            this.form.marketing_channel = '';
            this.form.communication_status = '';
            this.form.status = 'active';
            this.applyFilters(); // Optionally apply cleared filters
        },

        fetchLeadFilters() {
            axios.get('/lead/filter-options')
                .then(response => {
                    responseParse(response, false);
                    if (response.status === 200) {
                        const data = response.data.data;
                        if (data.managers) {
                            this.managers = data.managers;
                        }
                        if (data.leads) {
                            this.leads = data.leads;
                        }
                        if (data.marketingChannels) {
                            this.marketingChannels = data.marketingChannels;
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
