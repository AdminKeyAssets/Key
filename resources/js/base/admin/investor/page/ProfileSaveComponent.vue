<template>
    <div class="block">
        <el-form  v-loading="loading"
                  element-loading-text="Loading..."
                  element-loading-spinner="el-icon-loading"
                  element-loading-background="rgba(0, 0, 0, 0.8)"
                  ref="form" :model="form" class="form-horizontal form-bordered">

            <el-row>

                <div class="form-group">

                    <label class="col-md-2 control-label">Name <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                  v-model="form.name"></el-input>
                    </div>

                </div>

                <div class="form-group">

                    <label class="col-md-2 control-label">Email <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                  v-model="form.email"></el-input>
                    </div>

                </div>

                <div class="form-group">

                    <label class="col-md-2 control-label">Password <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-input class="el-input--is-round" maxlength="150" show-word-limit
                                  v-model="form.password"></el-input>
                    </div>

                </div>

            </el-row>


            <div class="el-form-item registration-btn">
                <el-button type="primary" @click="save" :disabled="loading || !form.name || !form.email" style="margin: 0 1rem">Save</el-button>
            </div>
        </el-form>
    </div>
</template>

<style>
.el-transfer-panel{
    width: 387px;
}
</style>
<script>
import {responseParse} from '../../../mixins/responseParse'
import {getData} from '../../../mixins/getData'
import {hasPermission} from '../../../mixins/hasPermission'
import {Notification} from 'element-ui'

export default {
    props: [
        'getSaveDataRoute',
        'user'
    ],

    data(){
        return {
            data: [],
            loading: false,
            routes: {},

            /**
             * Form data
             */
            form: {
                password: ''
            },

        }
    },
    created(){
        this.getSaveData();
    },

    methods: {

        modifyCreateData(){
            this.form = {
                name: this.user ? this.user.name : '',
                email: this.user ? this.user.email : '',
                password: ''
            }

        },

        /**
         *
         * Get save data.
         *
         * @returns {Promise<void>}
         */
        async getSaveData(){
            this.loading = true;

            await getData({
                method: 'POST',
                url: this.getSaveDataRoute
            }).then(response => {
                // Parse response notification.
                responseParse(response, false);
                if (response.status == 200) {
                    // Response data.
                    let data = response.data.data;

                    this.routes = data.routes;

                    // Modify create data.
                    this.modifyCreateData();
                }
                this.loading = false
            })
        },

        async save(){

            this.$confirm(this.lang.confirm_save, {
                confirmButtonText: this.lang.confirm_save_yes,
                cancelButtonText: this.lang.confirm_save_no,
                type: 'warning'
            })
                .then(async () => {

                    this.loading = true;

                    await getData({
                        method: 'POST',
                        url: this.routes.save,
                        data: this.form
                    }).then(response => {
                        // Parse response notification.
                        responseParse(response);
                        if (response.status == 200) {
                            // Response data.
                            let data = response.data;

                            window.location.reload();
                        }

                        this.loading = false
                    })
                });
        },


        resetFields(){

        }

    }

}
</script>
