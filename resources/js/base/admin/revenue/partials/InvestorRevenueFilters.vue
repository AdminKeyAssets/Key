<template>
    <div class="block">
        <!-- Button to toggle the visibility of the filters -->
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
                        v-model="form.agreement_date"
                        type="daterange"
                        format="yyyy/MM/dd"
                        value-format="yyyy/MM/dd"
                        start-placeholder="Asset Created At Start date"
                        end-placeholder="Asset Created At End date">
                    </el-date-picker>
                </div>

                <div class="form-group">
                    <el-select
                        v-model="form.asset"
                        filterable
                        placeholder="Asset Name"
                        v-remove-readonly
                        multiple
                        collapse-tags
                        @change="(val) => { handleAllOptionSelect('asset'); onAssetChange(val); }">
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
                    <el-select 
                        v-model="form.status" 
                        filterable 
                        placeholder="Status" 
                        v-remove-readonly 
                        multiple 
                        collapse-tags
                        @change="handleAllOptionSelect('status')">
                        <el-option label="All" value="all"></el-option>
                        <el-option label="Active" value="active"></el-option>
                        <el-option label="Sold" value="sold"></el-option>
                    </el-select>
                </div>

                <div class="form-group">
                    <el-select 
                        v-model="form.asset_type" 
                        filterable 
                        placeholder="Asset Type" 
                        v-remove-readonly 
                        multiple 
                        collapse-tags
                        @change="handleAllOptionSelect('asset_type')">
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
                    <el-select 
                        v-model="form.agreement_status" 
                        filterable 
                        placeholder="Agreement Status"
                        v-remove-readonly 
                        multiple 
                        collapse-tags
                        @change="handleAllOptionSelect('agreement_status')">
                        <el-option label="All" value="all"></el-option>
                        <el-option label="Complete" value="Complete"></el-option>
                        <el-option label="Installments" value="Installments"></el-option>
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
                status: ['active'],
                agreement_date: '',
                asset_type: [],
                agreement_status: [],
                asset: [],
            },
            showFilters: false,
            assets: [],
            types: [],
            assetStatusMap: {}, // Will store the sale_status for each asset
        };
    },
    mounted() {
        this.loadFiltersFromQueryParams();
        this.fetchAssetFilters();

        if (this.form.agreement_date ||
            this.form.status !== 'active' ||
            this.form.asset ||
            this.form.asset_status ||
            this.form.agreement_status)
        {
            this.showFilters = true;
        }
    },
    watch: {
        // Watch for changes in select fields that have "All" option
        'form.asset': function(val) {
            if (val && val.includes('all')) {
                this.form.asset = ['all'];
            }
        },
        'form.status': function(val) {
            if (val && val.includes('all')) {
                this.form.status = ['all'];
            }
        },
        'form.asset_type': function(val) {
            if (val && val.includes('all')) {
                this.form.asset_type = ['all'];
            }
        },
        'form.agreement_status': function(val) {
            if (val && val.includes('all')) {
                this.form.agreement_status = ['all'];
            }
        }
    },
    methods: {
        loadFiltersFromQueryParams() {
            const urlParams = new URLSearchParams(window.location.search);
            this.form.status = urlParams.get('status') ? urlParams.get('status').split(',') : ['active'];
            this.form.asset = urlParams.get('asset') ? urlParams.get('asset').split(',') : [];
            this.form.asset_type = urlParams.get('asset_type') ? urlParams.get('asset_type').split(',') : [];
            this.form.agreement_status = urlParams.get('agreement_status') ? urlParams.get('agreement_status').split(',') : [];
            this.form.agreement_date = urlParams.get('agreement_date') ? urlParams.get('agreement_date').split(',') : '';
        },
        applyFilters() {
            // Create a copy of the form data for serialization
            const formData = {};
            
            // Handle arrays by joining with commas for the URL
            Object.keys(this.form).forEach(key => {
                if (Array.isArray(this.form[key])) {
                    formData[key] = this.form[key].join(',');
                } else {
                    formData[key] = this.form[key];
                }
            });
            
            const queryParams = new URLSearchParams(formData).toString();
            window.location.search = queryParams;
        },
        
        // Method to handle "All" option selection
        handleAllOptionSelect(field) {
            // If 'all' is in the selection, make it the only selection
            if (this.form[field].includes('all')) {
                this.form[field] = ['all'];
            }
        },

        // Clear filters and reset status to default (active)
        clearFilters() {
            this.form.status = ['active'];
            this.form.asset = [];
            this.form.asset_type = [];
            this.form.agreement_status = [];
            this.form.agreement_date = '';
            this.applyFilters();
        },
        fetchAssetFilters() {
            axios.get('/assets/investor/filter-options')
                .then(response => {
                    responseParse(response, false);
                    if (response.status === 200) {
                        let data = response.data.data;

                        if (data.assets) {
                            this.assets = data.assets;
                            // Fetch asset statuses for automatic filtering
                            this.fetchAssetStatuses();
                        }
                        if (data.types) {
                            this.types = data.types;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching filters:', error);
                });
        },
        fetchAssetStatuses() {
            // Get status for each asset
            axios.get('/assets/revenues/investor/filter-options')
                .then(response => {
                    responseParse(response, false);
                    if (response.status === 200 && response.data.data && response.data.data.assets) {
                        const assets = response.data.data.assets;

                        // Create a map of asset names to their sale status
                        assets.forEach(asset => {
                            if (asset.sale_status) {
                                if (!this.assetStatusMap[asset.project_name]) {
                                    this.assetStatusMap[asset.project_name] = [];
                                }
                                this.assetStatusMap[asset.project_name].push(asset.sale_status);
                            }
                        });

                        // If an asset is already selected on page load, update status accordingly
                        if (this.form.asset && this.form.asset !== 'all') {
                            this.onAssetChange(this.form.asset);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching asset statuses:', error);
                });
        },
        onAssetChange(assetNames) {
            // If "All" is selected or no assets selected, don't change anything
            if (!assetNames || assetNames.length === 0 || assetNames.includes('all')) {
                return;
            }
            
            // Process each selected asset
            const statusesToSet = [];
            
            assetNames.forEach(assetName => {
                // Check if we have status information for this asset
                if (this.assetStatusMap[assetName]) {
                    const statuses = this.assetStatusMap[assetName];
                    
                    // Add the status to our list if not already there
                    statuses.forEach(status => {
                        if (!statusesToSet.includes(status)) {
                            statusesToSet.push(status);
                        }
                    });
                }
            });
            
            // If statuses found, update the status filter
            if (statusesToSet.length > 0) {
                this.form.status = statusesToSet;
            }
        }
    }
};
</script>

<style scoped>
.block {
    margin-bottom: 20px;
}
</style>
