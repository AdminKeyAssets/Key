export default {
    methods: {
        errorHandler(response) {
            /**
             * Check If laravel Validation Errors Isset
             */
            if (Object.entries(response.data.errors || []).length) {
                // Loop Each
                Object.values(response.data.errors || []).map(response => {
                    this.$notify.error({
                        message: response[0]
                    });
                })
            }
        }
    }
}
