<template>
    <div class="col-md-2">
        <!-- Button to open modal -->
        <el-button class="import-leads-button"
                   icon="el-icon-document" type="secondary" @click="openModal">
            Import Leads Status
        </el-button>

        <!-- Modal dialog with adjusted width -->
        <el-dialog
            title="Import Leads"
            :visible.sync="dialogVisible"
            :width="dialogWidth"
            @close="resetUpload"
        >
            <!-- File upload option inside modal using a plain file input -->
            <div class="import-leads-uploader" style="margin-bottom: 2rem;">
                <input type="file" @change="onFileChange" accept=".xlsx, .xls, .csv" ref="fileInput">
                <div v-if="file">
                    <p>{{ file.name }}</p>
                    <el-button icon="el-icon-delete-solid" size="small" type="danger" @click="removeFile">
                        Remove File
                    </el-button>
                </div>
            </div>

            <!-- Modal footer with Cancel, Export Sample File, and Import buttons -->
            <span slot="footer" class="dialog-footer import-dialog-footer">
                <el-button @click="cancelImport">Cancel</el-button>
                <el-button type="warning" @click="exportSampleFile">Export Sample File</el-button>
                <el-button type="primary" @click="uploadLeads">Import</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
export default {
    data() {
        return {
            file: null,
            dialogVisible: false,
        };
    },
    methods: {
        // Opens the modal dialog.
        openModal() {
            this.dialogVisible = true;
        },
        // Called when a file is selected.
        onFileChange(event) {
            const selectedFile = event.target.files[0];
            if (selectedFile) {
                this.file = selectedFile;
            }
        },
        // Removes the selected file and resets the file input.
        removeFile() {
            this.file = null;
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = "";
            }
        },
        // Upload leads when the Import button in the modal is clicked.
        async uploadLeads() {
            if (!this.file) {
                this.$message.error('Please select a file first.');
                return;
            }
            const formData = new FormData();
            formData.append('file', this.file);

            try {
                // Post the file to the import endpoint.
                await axios.post('/lead/import-status', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
                this.$message.success('Leads imported successfully!');
                this.resetUpload();
                // this.dialogVisible = false;
                setTimeout(() => {
                    // window.location.href = `/lead/list`;
                    window.location.reload();
                }, 1000);
            } catch (error) {
                console.error('Error importing leads:', error);
                this.$message.error('Failed to import leads.');
            }
        },
        // Closes the modal and resets file selection.
        cancelImport() {
            this.dialogVisible = false;
            this.resetUpload();
        },
        // Reset file selection and clear the file input.
        resetUpload() {
            this.file = null;
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = "";
            }
        },

        async exportSampleFile() {
            try {

                const response = await axios.get('/lead/export-import-status-sample', {
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'sample_leads_status.xlsx');
                document.body.appendChild(link);
                link.click();
            } catch (error) {
                console.error('Error exporting leads:', error);
            }
        },
    },
    computed: {
        // Returns full width on mobile and 30% on larger screens.
        dialogWidth() {
            return window.innerWidth < 768 ? '100%' : '30%';
        }
    },
};
</script>

<style scoped>
/* Add any component-specific styling here if needed */
</style>
