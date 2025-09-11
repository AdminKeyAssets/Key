<template>
    <el-tooltip
        :content="developerAccess
            ? (title + ' Disabled')
            : (title + ' Active')"
        placement="top"
        effect="light"
    >
        <a 
            href="javascript:void(0)" 
            @click="toggleDeveloperAccess"
            :class="['btn', 'btn-switch', developerAccess ? 'active-icon' : 'disabled-icon']"
        >
            <i class="el-icon-switch-button"></i>
        </a>
    </el-tooltip>
</template>

<script>
import { notifications } from '../../mixins/notifications';

export default {
    props: {
        assetId: {
            type: Number,
            required: true
        },
        developerAccess: {
            type: Boolean,
            required: true
        },
        title: {
            type: String,
            default: 'Switch Developer Access to'
        },
        route: {
            type: String,
            required: true
        }
    },
    
    data() {
        return {
            isLoading: false
        }
    },
    
    methods: {
        async toggleDeveloperAccess() {
            if (this.isLoading) return;
            
            const action = this.developerAccess ? 'disable' : 'enable';
            
            try {
                const result = await this.$confirm(
                    `Choose how you want to ${action} developer access:`,
                    'Developer Access Action',
                    {
                        customClass: 'developer-access-modal',
                        distinguishCancelAndClose: true,
                        confirmButtonText: `${action.charAt(0).toUpperCase() + action.slice(1)} This Asset`,
                        cancelButtonText: `${action.charAt(0).toUpperCase() + action.slice(1)} All with Same Name`,
                        type: 'warning'
                    }
                );
                
                // If confirmed (single asset)
                this.performSingleToggle();
                
            } catch (error) {
                if (error === 'cancel') {
                    // If cancelled (bulk action)
                    this.performBulkToggle();
                } else if (error === 'close') {
                    // If closed (do nothing)
                    return;
                }
            }
        },

        async performSingleToggle() {
            this.isLoading = true;
            
            try {
                const response = await axios.get(this.route);
                
                if (response.data.code === 200) {
                    notifications({
                        status: 'success',
                        message: response.data.message
                    });
                    
                    this.$emit('access-changed', !this.developerAccess);
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    notifications({
                        status: 'error',
                        message: response.data.message
                    });
                }
            } catch (error) {
                if (error.response && error.response.data && error.response.data.message) {
                    notifications({
                        status: 'error',
                        message: error.response.data.message
                    });
                } else {
                    notifications({
                        status: 'error',
                        message: 'An error occurred while updating developer access.'
                    });
                }
            } finally {
                this.isLoading = false;
            }
        },

        async performBulkToggle() {
            this.isLoading = true;
            
            try {
                const bulkRoute = this.route.replace('/developer_access/', '/developer_access_bulk/');
                const response = await axios.post(bulkRoute, {
                    asset_id: this.assetId,
                    developer_access: !this.developerAccess
                });
                
                if (response.data.code === 200) {
                    notifications({
                        status: 'success',
                        message: response.data.message
                    });
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    notifications({
                        status: 'error',
                        message: response.data.message
                    });
                }
            } catch (error) {
                if (error.response && error.response.data && error.response.data.message) {
                    notifications({
                        status: 'error',
                        message: error.response.data.message
                    });
                } else {
                    notifications({
                        status: 'error',
                        message: 'An error occurred while updating developer access for all assets.'
                    });
                }
            } finally {
                this.isLoading = false;
            }
        }
    }
}
</script>
