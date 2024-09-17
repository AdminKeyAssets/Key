<template>
    <div>
        <div class="block">
            <el-form v-loading="loading"
                     element-loading-text="Loading..."
                     element-loading-spinner="el-icon-loading"
                     element-loading-background="rgba(0, 0, 0, 0.0)"
                     ref="form" :model="form" class="form-horizontal form-bordered">

                <el-row>


                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Investment Status:</label>
                        <div class="col-md-3 uppercase-medium">
                            <el-select
                                v-model="form.status"
                                placeholder="Select Status">
                                <el-option label="Renovation" value="Renovation"></el-option>
                                <el-option label="Maintenance" value="Maintenance"></el-option>
                                <el-option label="Legal Services" value="Legal Services"></el-option>
                                <el-option label="Administrative Costs" value="Administrative Costs"></el-option>
                                <el-option label="Utilities" value="Utilities"></el-option>
                                <el-option label="Brokerage Fee" value="Brokerage Fee"></el-option>
                                <el-option label="Other" value="Other"></el-option>
                            </el-select>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Price:</label>
                        <div class="col-md-7 uppercase-medium">
                            <input class="form-control" :disabled="loading" v-model="form.amount"></input>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Payment Date:</label>
                        <div class="col-md-10 uppercase-medium">
                            <el-date-picker
                                v-model="form.date"
                                format="yyyy/MM/dd"
                                value-format="yyyy/MM/dd"
                                type="date"
                                placeholder="Pick a investment date from">
                            </el-date-picker>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Description:</label>
                        <div class="col-md-10 uppercase-medium">
                            <el-input type="textarea" v-model="form.description"></el-input>
                        </div>
                    </div>

                </el-row>

                <div class="form-group dashed">
                    <label class="col-md-1 control-label">Attachment:</label>
                    <div class="col-md-10 uppercase-medium">
                        <p v-if="form.attachment">File: <a :href="form.attachment" target="_blank">View
                            Attachment</a>
                            <el-button type="danger" icon="el-icon-delete" size="small"
                                       @click="removeFile"
                            ></el-button>
                        </p>
                        <input v-else type="file" class="form-control" @change="onFileChange">
                    </div>
                </div>

            </el-form>
        </div>
    </div>
</template>


<script>
import {responseParse} from '../../../mixins/responseParse'
import {getData} from '../../../mixins/getData'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {
    props: [
        'routes',
        'updateData',
        'item',
        'assets'
    ],
    data() {
        return {
            form: {
                currency: 'USD'
            },
            loading: false,
            editor: ClassicEditor,
            addDetailIsBtnDisabled: true,
            currencies: {
                "GEL": "GEL",
                "USD": "USD",
            },
        }
    },
    updated() {
        this.updateData(this.form);
    },
    watch: {
        'item'() {
            if (this.item) {
                this.form = this.item;
            }
        }
    },
    methods: {
        onFileChange(e) {
            this.form.attachment = e.target.files[0];
        },
        removeFile() {
            this.form.attachment = null;
        },
    }
}

</script>
