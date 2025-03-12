<template>
    <div>
        <el-button
            icon="el-icon-document"
            style="margin-bottom: 2rem; margin-right: 3rem;"
            type="secondary"
            class="pull-right"
            @click="exportInvestors">Export Users
        </el-button>
    </div>
</template>

<script>
export default {
    data() {
        return {
            investors: {},
            filters: {
                search: '',
                role: '',
                create_date: '',
            }
        };
    },
    methods: {

        async exportInvestors() {
            try {

                this.loadFiltersFromQueryParams();

                const response = await axios.get('/admin/users/export', {
                    params: this.filters,
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'users.xlsx');
                document.body.appendChild(link);
                link.click();
            } catch (error) {
                console.error('Error exporting investors:', error);
            }
        },
        loadFiltersFromQueryParams() {
            const urlParams = new URLSearchParams(window.location.search);
            this.filters.search = urlParams.get('search') || '';
            this.filters.role = urlParams.get('role') || '';
            this.filters.create_date = urlParams.get('create_date') ? urlParams.get('create_date').split(',') : '';
        }
    },
};
</script>
