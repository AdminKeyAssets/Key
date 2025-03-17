<template>
    <div>
        <div class="block">
            <el-form
                v-loading="loading"
                element-loading-text="Loading..."
                element-loading-spinner="el-icon-loading"
                element-loading-background="rgba(0, 0, 0, 0.0)"
                ref="form"
                :model="form"
                class="form-horizontal form-bordered"
            >
                <el-row>
                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Status:</label>
                        <div class="col-md-6 uppercase-medium">
                            <el-select
                                v-model="form.status"
                                placeholder="Select Status"
                                @focus="storePreviousStatus"
                                @change="handleStatusChange"
                            >
                                <el-option label="New" value="New"></el-option>
                                <el-option label="Not Responding" value="Not Responding"></el-option>
                                <el-option label="Communication" value="Communication"></el-option>
                                <el-option label="Proposal Sent" value="Proposal Sent"></el-option>
                                <el-option label="Refused" value="Refused"></el-option>
                                <el-option label="Signed" value="Signed"></el-option>
                                <el-option label="Archieve" value="Archieve"></el-option>
                            </el-select>
                        </div>
                    </div>
                </el-row>
            </el-form>
        </div>
    </div>
</template>

<script>
import { responseParse } from '../../../mixins/responseParse'
import { getData } from '../../../mixins/getData'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic'
import axios from 'axios'  // Ensure axios is installed

export default {
    props: ['routes', 'item'],
    data() {
        return {
            form: {},
            loading: false,
            editor: ClassicEditor,
            previousStatus: ''
        }
    },
    created(){
        this.form = this.item;
    },
    watch: {
        'item'() {
            if (this.item) {
                this.form = this.item;
            }
        }
    },
    methods: {
        storePreviousStatus() {
            this.previousStatus = this.form.status;
        },

        handleStatusChange(newStatus) {
            const oldStatus = this.previousStatus;
            this.$confirm(
                `Are you sure you want to change the status to "${newStatus}"?`,
                'Confirm',
                {
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    type: 'warning'
                }
            )
                .then(() => {
                    this.updateLeadStatus(newStatus);
                })
                .catch(() => {
                    this.form.status = oldStatus;
                });
        },
        updateLeadStatus(newStatus) {
            this.loading = true;
            let url = `/lead/status-update/${this.form.id}`;
            axios
                .post(url, this.form)
                .then(response => {
                    this.loading = false;
                    responseParse(response);

                    this.previousStatus = newStatus;
                })
                .catch(error => {
                    this.loading = false;

                    this.form.status = this.previousStatus;
                    this.$message({
                        type: 'error',
                        message: 'Failed to update status!'
                    });
                });
        }
    }
}
</script>
