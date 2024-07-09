<template>
    <el-row class="dashed">
        <el-row>
            <div class="form-group">
                <label class="col-md-2 control-label">Current Value:</label>
                <div class="col-md-4 uppercase-medium">
                    <input class="form-control" :disabled="loading" v-model="form.current_value"></input>
                </div>
                <div class="col-md-3 uppercase-medium">
                    <el-select v-model="form.current_value_currency"
                               :value="form.current_value_currency"
                               filterable
                               placeholder="Select">
                        <el-option
                            v-for="(currency, index) in currencies"
                            :key="index"
                            :label="currency"
                            :value="index">
                        </el-option>
                    </el-select>
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
                    <el-table-column prop="value" label="Value"/>
                    <el-table-column prop="date" label="Date"/>
                    <el-table-column prop="attachment" label="Attachment">
                        <template slot-scope="scope">
                            <a v-if="scope.row.attachment" :href="scope.row.attachment" target="_blank">View
                                Attachment</a>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </el-row>
    </el-row>


</template>

<script>
export default {
    props: ['form', 'loading', 'currencies', 'updateData'],
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
    }
}
</script>
