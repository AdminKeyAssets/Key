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
                                type="date"
                                placeholder="Pick a payment day">
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
                        <label class="col-md-1 control-label">Status:</label>
                        <div class="col-md-10 uppercase-medium">
                            <el-switch
                                v-model="form.status"
                                active-color="#13ce66"
                                inactive-color="#ff4949">
                            </el-switch>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Status:</label>
                        <div class="col-md-10 uppercase-medium">
<!--                            <input @change="handleFileUpload" style="display: block !important;" type="file"/>-->
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
        'investors'
    ],
    data() {
        return {
            form: {
                status: false
            },
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
            }
        }
    },
    methods: {

    }
}

</script>
