<template>
    <div>
        <el-button
            icon="el-icon-document"
            style="margin-bottom: 2rem; margin-right: 3rem;"
            type="secondary"
            class="pull-right"
            @click="exportInvestors">Export Assets</el-button>
    </div>
</template>

<script>
export default {
    data() {
        return {
            investors: {},
            filters: {
                agreement_date: '',
                payment_date: '',
                investor: '',
                status: 'active',
                asset: '',
                manager: '',
                asset_status: '',
                asset_type: '',
                agreement_status: ''
            }
        };
    },
    methods: {

        async exportInvestors() {
            try {
                this.loadFiltersFromQueryParams();
                const response = await axios.get('/assets/export', {
                    params: this.filters,
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'assets.xlsx');
                document.body.appendChild(link);
                link.click();
            } catch (error) {
                console.error('Error exporting assets:', error);
            }
        },
        loadFiltersFromQueryParams() {
            const urlParams = new URLSearchParams(window.location.search);
            this.filters.agreement_date = urlParams.get('agreement_date') && urlParams.get('agreement_date') !== 'null'
                ? urlParams.get('agreement_date').split(',')
                : '';
            this.filters.payment_date = urlParams.get('payment_date') && urlParams.get('payment_date') !== 'null'
                ? urlParams.get('payment_date').split(',')
                : '';
            this.filters.investor = urlParams.get('investor') || '';
            this.filters.status = urlParams.get('status') || 'active';
            this.filters.asset = urlParams.get('asset') || '';
            this.filters.asset_status = urlParams.get('asset_status') || '';
            this.filters.asset_type = urlParams.get('asset_type') || '';
            this.filters.agreement_status = urlParams.get('agreement_status') || '';
            this.filters.manager = urlParams.get('manager') || '';
        },
    },
};
</script>
