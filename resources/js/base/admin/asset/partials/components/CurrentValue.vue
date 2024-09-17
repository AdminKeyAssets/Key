<template>
    <el-row class="dashed">
        <el-row>
            <div class="form-group">
                <label class="col-md-2 control-label">Current Value:</label>
                <div class="col-md-4 uppercase-medium">
                    <input class="form-control" :disabled="loading" v-model="form.current_value"></input>
                </div>
            </div>

            <div class="form-group dashed">
                <label class="col-md-1 control-label">Add Attachment:</label>
                <div class="col-md-10 uppercase-medium">
                    <input type="file" @change="onCurrentValueAttachmentChange">
                    <div v-if="form.current_value_attachment">
                        <p v-if="form.current_value_attachment">File: <a :href="form.current_value_attachment"
                                                                         target="_blank">View Attachment</a></p>
                        <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                   @click="removeCurrentValueAttachment"></el-button>
                    </div>
                </div>
            </div>
        </el-row>
        <el-row>
            <div v-if="form.currentValues">
                <el-table border :data="form.currentValues" style="width: 100%">
                    <el-table-column prop="value" label="Value">
                        <template slot-scope="scope">
                            <el-input v-if="editableRow === scope.$index" v-model="scope.row.value"></el-input>
                            <span v-else>{{ scope.row.value }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="date" label="Date"/>
                    <el-table-column prop="attachment" label="Attachment">
                        <template slot-scope="scope">
                            <div v-if="editableRow === scope.$index">
                                <input type="file" @change="onAttachmentChange(scope.$index, $event)">
                            </div>
                            <a v-else-if="scope.row.attachment" :href="scope.row.attachment" target="_blank">View
                                Attachment</a>
                        </template>
                    </el-table-column>
                    <el-table-column label="Actions">
                        <template slot-scope="scope">
                            <el-button v-if="editableRow !== scope.$index" size="small" type="primary"
                                       @click="editRow(scope.$index)">
                                <i class="el-icon-edit"></i>
                            </el-button>
                            <el-button v-if="editableRow !== scope.$index" size="small" type="danger"
                                       @click="confirmDelete(scope.$index)">
                                <i class="el-icon-delete"></i>
                            </el-button>
                            <el-button v-if="editableRow === scope.$index" size="small" type="success"
                                       @click="saveRow(scope.$index)">
                                <i class="el-icon-check"></i>
                            </el-button>
                            <el-button v-if="editableRow === scope.$index" size="small" type="warning"
                                       @click="cancelEdit()">
                                <i class="el-icon-close"></i>
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </el-row>
    </el-row>
</template>

<script>
import axios from 'axios';
import {responseParse} from "../../../../mixins/responseParse";

export default {
    props: ['form', 'loading', 'currencies', 'updateData'],
    data() {
        return {
            editableRow: null,
            tempRow: null
        };
    },
    watch: {
        'form'() {
            if (this.form) {
                if (!this.form.current_value_currency) {
                    this.$emit('update-form', {...this.form, current_value_currency: 'USD'});
                }
            }
        }
    },
    methods: {
        onCurrentValueAttachmentChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.$emit('update-form', {
                        ...this.form,
                        current_value_attachment: file,
                        currentValueAttachmentPreview: e.target.result
                    });
                };
                reader.onerror = (error) => {
                    console.error('Error loading file:', error);
                };
                reader.readAsDataURL(file);
            }
        },
        removeCurrentValueAttachment() {
            this.$emit('update-form', {
                ...this.form,
                current_value_attachment: null,
                currentValueAttachmentPreview: null
            });
        },
        onAttachmentChange(index, event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.currentValues[index].attachment = file;
                };
                reader.readAsDataURL(file);
            }
        },
        editRow(index) {
            this.editableRow = index;
            this.tempRow = JSON.parse(JSON.stringify(this.form.currentValues[index]));
        },
        async saveRow(index) {
            this.$confirm('Are you sure you want to update this row?', 'Warning', {
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                type: 'warning'
            }).then(async () => {
                const row = this.form.currentValues[index];
                const formData = new FormData();
                formData.append('value', row.value);
                formData.append('date', row.date);
                if (row.attachment) {
                    formData.append('attachment', row.attachment);
                }

                try {
                    await axios.post(`/assets/current-value/update/${row.id}`, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }).then(response => {
                        responseParse(response);
                        const data = response.data.data;
                        this.editableRow = null;
                        this.tempRow = null;
                        this.form.currentValues[index] = data;
                        this.$emit('update-form', {...this.form});
                    });

                } catch (error) {
                    console.error('Error updating row:', error);
                }
            }).catch(() => {
            });
        },
        cancelEdit() {
            if (this.tempRow !== null) {
                this.form.currentValues[this.editableRow] = this.tempRow;
                this.tempRow = null;
            }
            this.editableRow = null;
        },
        confirmDelete(index) {
            this.$confirm('Are you sure you want to delete this row?', 'Warning', {
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                type: 'warning'
            }).then(async () => {
                const row = this.form.currentValues[index];
                try {
                    await axios.delete(`/assets/current-value/delete/${row.id}`);
                    this.form.currentValues.splice(index, 1);
                    this.$emit('update-form', {...this.form});
                } catch (error) {
                    console.error('Error deleting row:', error);
                }
            }).catch(() => {
            });
        },
    }
}
</script>
