<template>
    <div>
        <!-- File selection -->
        <el-upload
            ref="upload"
            :before-upload="beforeUpload"
            :auto-upload="false"
            accept=".xlsx, .xls, .csv"
            style="margin-bottom: 2rem;"
        >
            <el-button icon="el-icon-upload">Select Excel File</el-button>
        </el-upload>

        <!-- Import button -->
        <el-button
            icon="el-icon-document"
            type="secondary"
            @click="uploadLeads"
        >
            Import Leads
        </el-button>
    </div>
</template>

<script>
export default {
    data() {
        return {
            file: null,
        };
    },
    methods: {
        // Called when a file is selected. Returns false to prevent auto-upload.
        beforeUpload(file) {
            this.file = file;
            return false;
        },
        async uploadLeads() {
            if (!this.file) {
                this.$message.error('Please select a file first.');
                return;
            }
            const formData = new FormData();
            formData.append('file', this.file);

            try {
                // Post the file to the import endpoint.
                const response = await axios.post('/lead/import', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
                this.$message.success('Leads imported successfully!');
                // Reset the file input.
                this.file = null;
                this.$refs.upload.clearFiles();
            } catch (error) {
                console.error('Error importing leads:', error);
                this.$message.error('Failed to import leads.');
            }
        },
    },
};
</script>

<style scoped>
/* Add any component-specific styling here if needed */
</style>
