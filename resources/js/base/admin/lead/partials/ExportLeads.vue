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
            filters: {
                search: '',
                create_date: '',
                manager: '',
                marketing_channel: '',
                status: '',
                communication_status: ''
            }
        };
    },
    methods: {

        async exportLeads() {
            try {
                this.loadFiltersFromQueryParams();

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
        },
        loadFiltersFromQueryParams() {
            const urlParams = new URLSearchParams(window.location.search);
            this.filters.search = urlParams.get('search') || '';
            this.filters.create_date = urlParams.get('create_date') ? urlParams.get('create_date').split(',') : '';
            this.filters.manager = urlParams.get('manager') || '';
            this.filters.marketing_channel = urlParams.get('marketing_channel') || '';
            this.filters.communication_status = urlParams.get('communication_status') || '';
            this.filters.status = urlParams.get('status') || 'active';
        },
    },
};
</script>
