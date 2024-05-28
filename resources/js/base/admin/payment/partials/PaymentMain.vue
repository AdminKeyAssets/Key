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
                        <label class="col-md-1 control-label">Month:</label>
                        <div class="col-md-10 uppercase-medium">
                            <input class="form-control" :disabled="loading" v-model="form.month"></input>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Date:</label>
                        <div class="col-md-10 uppercase-medium">
                            <el-date-picker
                                v-model="form.payment_date"
                                format="yyyy/MM/dd"
                                value-format="yyyy/MM/dd"
                                type="date"
                                placeholder="Pick a payment date">
                            </el-date-picker>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Amount:</label>
                        <div class="col-md-10 uppercase-medium">
                            <input class="form-control" :disabled="loading" v-model="form.amount"></input>
                        </div>
                    </div>


                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Paid:</label>
                        <div class="col-md-10 uppercase-medium">
                            <el-switch
                                v-model="form.status"
                                active-color="#13ce66"
                                inactive-color="#ff4949">
                            </el-switch>
                        </div>
                    </div>

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

                </el-row>
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
    ],
    data() {
        return {
            form: {},
            loading: false,
            editor: ClassicEditor,
            addDetailIsBtnDisabled: true,
        }
    },
    updated() {
        this.updateData(this.form);
    },
    watch: {
        'item'() {
            if (this.item) {
                this.form = this.item;
                if(this.item.status){
                    this.form.status = true;
                }
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
