<template>
    <div>
        <el-button
            icon="el-icon-document"
            style="margin-bottom: 2rem; margin-right: 3rem;"
            type="secondary"
            class="pull-right"
            @click="exportLeads">Export Leads</el-button>
    </div>
</template>

<script>
export default {
    data() {
        return {
            sales: {},
            filters: {}
        };
    },
    methods: {

        async exportLeads() {
            try {
                const response = await axios.get('/lead/export', {
                    params: this.filters,
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'leads.xlsx');
                document.body.appendChild(link);
                link.click();
            } catch (error) {
                console.error('Error exporting leads:', error);
            }
        }
    },
};
</script>
