<template>
    <div>
        <el-button
            icon="el-icon-document"
            style="margin-bottom: 2rem; margin-right: 3rem;"
            type="secondary"
            class="pull-right"
            @click="exportInvestors">Export Revenues</el-button>
    </div>
</template>

<script>
export default {
    data() {
        return {
            investors: {},
            filters: {
                agreement_date: '',
            }
        };
    },
    methods: {

        async exportInvestors() {
            try {
                const response = await axios.get('/assets/revenues/export', {
                    params: this.filters,
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'revenues.xlsx');
                document.body.appendChild(link);
                link.click();
            } catch (error) {
                console.error('Error exporting revenues:', error);
            }
        }
    },
};
</script>
