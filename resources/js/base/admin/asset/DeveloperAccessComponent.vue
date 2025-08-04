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
            
            this.isLoading = true;
            
            try {
                const response = await axios.get(this.route);
                
                if (response.data.code === 200) {
                    notifications({
                        status: 'success',
                        message: response.data.message
                    });
                    
                    // Update the local state
                    this.$emit('access-changed', !this.developerAccess);
                    
                    // Optionally reload the page to reflect changes
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
        }
    }
}
</script>
