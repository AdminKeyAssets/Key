<template>
    <div class="form-group dashed">
        <label class="col-md-1 control-label">Select Asset Manager:</label>
        <div class="col-md-10 uppercase-medium">
            <div v-if="loading">Loading managers...</div>
            <el-select
                v-model="selectedManagerId"
                filterable
                placeholder="Select Asset Manager"
                @change="updateManager"
            >
                <el-option
                    v-for="manager in availableManagers"
                    :key="manager.id"
                    :label="manager.name + ' ' + manager.surname"
                    :value="manager.id"
                ></el-option>
            </el-select>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: "ManagerSelection",
    props: {
        assetId: {
            type: [Number, String],
            default: null
        },
        value: {
            type: [Number, String],
            default: null
        }
    },
    data() {
        return {
            availableManagers: [],
            selectedManagerId: null,
            loading: false
        };
    },
    watch: {
        value: {
            handler(newValue) {
                if (newValue !== null && newValue !== undefined && newValue !== '') {
                    // Always convert to number for internal state
                    const parsedValue = typeof newValue === 'string' ? parseInt(newValue, 10) : newValue;
                    this.selectedManagerId = isNaN(parsedValue) ? null : parsedValue;
                } else {
                    this.selectedManagerId = null;
                }
            },
            immediate: true
        },
        assetId: {
            handler() {
                this.fetchManagers();
            },
            immediate: true
        }
    },
    methods: {
        async fetchManagers() {
            this.loading = true;
            try {
                const params = {};
                if (this.assetId) {
                    params.asset_id = this.assetId;
                }
                const response = await axios.get('/assets/available-managers', { params });
                if (response.data.success) {
                    this.availableManagers = response.data.managers;
                    
                    // If we have already selected a manager from the value prop, we're done
                    if (this.selectedManagerId) return;
                    
                    // Otherwise, pre-select the manager that is marked as assigned
                    const preselectedManager = this.availableManagers.find(manager => manager.assigned);
                    
                    if (preselectedManager) {
                        this.selectedManagerId = preselectedManager.id;
                        this.updateManager();
                    }
                }
            } catch (error) {
                console.error('Error fetching managers:', error);
            } finally {
                this.loading = false;
            }
        },
        updateManager() {
            // Emit the numeric value directly
            this.$emit('input', this.selectedManagerId);
        }
    }
};
</script>
