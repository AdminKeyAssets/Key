<template>
    <div>
        <el-button
            icon="el-icon-document"
            style="margin-bottom: 2rem; margin-right: 3rem;"
            type="secondary"
            class="pull-right"
            @click="exportInvestors">Export Investors</el-button>
    </div>
</template>

<script>
export default {
    data() {
        return {
            investors: {},
            filters: {
                email: '',
                phone: ''
            }
        };
    },
    methods: {

        async exportInvestors() {
            try {
                const response = await axios.get('/admin/investors/export', {
                    params: this.filters,
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'investors.xlsx');
                document.body.appendChild(link);
                link.click();
            } catch (error) {
                console.error('Error exporting investors:', error);
            }
        }
    },
    mounted() {
        this.fetchInvestors(); // Fetch all investors on component mount
    }
};
</script>
