<template>
    <el-tooltip :content="isArchived ? 'Unarchive asset' : 'Archive asset'" placement="top">
        <button @click="confirmArchive" :class="['btn', 'btn-xs', 'btn-archive', isArchived ? 'btn-info' : 'btn-warning']">
            <svg v-if="isArchived" width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M21 6H3C2.44772 6 2 6.44772 2 7V7C2 7.55228 2.44772 8 3 8H21C21.5523 8 22 7.55228 22 7V7C22 6.44772 21.5523 6 21 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M4 8H20V19C20 19.5304 19.7893 20.0391 19.4142 20.4142C19.0391 20.7893 18.5304 21 18 21H6C5.46957 21 4.96086 20.7893 4.58579 20.4142C4.21071 20.0391 4 19.5304 4 19V8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9 14H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M3 3L21 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M21 6H3C2.44772 6 2 6.44772 2 7V7C2 7.55228 2.44772 8 3 8H21C21.5523 8 22 7.55228 22 7V7C22 6.44772 21.5523 6 21 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M4 8H20V19C20 19.5304 19.7893 20.0391 19.4142 20.4142C19.0391 20.7893 18.5304 21 18 21H6C5.46957 21 4.96086 20.7893 4.58579 20.4142C4.21071 20.0391 4 19.5304 4 19V8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9 14H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9 12V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M15 12V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </el-tooltip>
</template>

<script>
export default {
    name: 'archive-asset-component',
    props: {
        assetId: {
            type: Number,
            required: true
        },
        isArchived: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        confirmArchive() {
            const action = this.isArchived ? 'unarchive' : 'archive';
            const actionCapitalized = this.isArchived ? 'Unarchive' : 'Archive';
            
            this.$confirm(`Are you sure you want to ${action} this asset?`, `Confirm ${actionCapitalized}`, {
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel',
                type: 'warning'
            }).then(() => {
                this.archiveAsset();
            }).catch(() => {
                // User canceled the action
            });
        },
        
        archiveAsset() {
            const action = this.isArchived ? 'unarchive' : 'archive';
            const url = `/assets/${action}/${this.assetId}`;
            
            axios.post(url)
                .then(response => {
                    if (response.data.success) {
                        this.$notify({
                            title: 'Success',
                            message: response.data.message,
                            type: 'success'
                        });
                        
                        // Emit event instead of reloading the page
                        this.$emit('asset-archived', {
                            assetId: this.assetId, 
                            isArchived: !this.isArchived
                        });
                    } else {
                        this.$notify.error({
                            title: 'Error',
                            message: response.data.message
                        });
                    }
                })
                .catch(error => {
                    this.$notify.error({
                        title: 'Error',
                        message: error.response?.data?.message || 'An error occurred while processing your request.'
                    });
                });
        }
    }
}
</script>

<style scoped>
.btn-info.btn-archive {
    background-color: #5bc0de;
    border-color: #46b8da;
}
.btn-warning.btn-archive {
    background-color: #f0ad4e;
    border-color: #eea236;
}
</style>
