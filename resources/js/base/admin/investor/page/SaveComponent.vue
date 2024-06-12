<template>
    <div class="block">
        <el-form  v-loading="loading"
                  element-loading-text="Loading..."
                  element-loading-spinner="el-icon-loading"
                  element-loading-background="rgba(0, 0, 0, 0.8)"
                  ref="form" :model="form" class="form-horizontal form-bordered">

            <el-row>

                <div class="form-group">

                    <label class="col-md-2 control-label">{{ lang.name }} <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                            <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                      v-model="form.name"></el-input>
                    </div>

                </div>

                <div class="form-group">

                    <label class="col-md-2 control-label">{{ lang.email }} <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                  v-model="form.email"></el-input>
                    </div>

                </div>

                <div class="form-group">

                    <label class="col-md-2 control-label">{{ lang.phone }}:</label>
                    <div class="col-md-6">
                        <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                  v-model="form.phone"></el-input>
                    </div>

                </div>

                <div class="form-group">

                    <label class="col-md-2 control-label">{{ lang.password }} <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-input class="el-input--is-round" maxlength="150" show-word-limit
                                  v-model="form.password"></el-input>
                    </div>

                </div>

            </el-row>

            <div class="form-group">

                <label class="col-md-2 control-label">{{ lang.roles }} <span class="text-danger">*</span>:</label>

                <div class="col-md-9">

                    <el-transfer
                            :filter-placeholder="lang.select_role_placeholder"
                            style="text-align: left; display: inline-block"
                            v-model="form.roles"
                            filterable
                            :titles="[lang.select_roles, lang.selected_roles]"
                            :button-texts="[lang.role_remove, lang.role_add]"
                            :format="{ noChecked: '${total}', hasChecked: '${checked}/${total}'}"
                            :data="data">
                    </el-transfer>

                </div>

            </div>


            <div class="el-form-item registration-btn">
                <el-button type="primary" @click="save" :disabled="loading || !form.name || !form.email" style="margin: 0 1rem">{{ lang.save_text }}</el-button>
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
                lang: {},
                routes: {},
                options: {},

                /**
                 * Form data
                 */
                form: {
                    guard_name: 'admin',
                    password: ''
                },

            }
        },
        created(){
          this.getSaveData();
        },

        methods: {

            modifyCreateData(){

                this.options.roles.forEach((item) => {
                   this.data.push({
                        key: item.id,
                       label: item.name
                   });
                });

                this.form = {
                    id: this.user? this.user.id : '',
                    name: this.user ? this.user.name : '',
                    email: this.user ? this.user.email : '',
                    phone: this.user ? this.user.phone : '',
                    roles: this.user ? this.user.rolesId : [],
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

                        this.lang = data.trans_text;
                        this.routes = data.routes;
                        this.options = data.options;

                        // Modify create data.
                        this.modifyCreateData();
                    }
                    this.loading = false
                })
            },

            async save(){

                this.loading = true;

                this.$confirm(this.lang.confirm_save, {
                    confirmButtonText: this.lang.confirm_save_yes,
                    cancelButtonText: this.lang.confirm_save_no,
                    type: 'warning'
                })
                    .then(async () => {

                        await getData({
                            method: 'POST',
                            url: this.routes.save,
                            data: this.form
                        }).then(response => {
                            // Parse response notification.
                            responseParse(response);
                            if (response.status == 200) {
                                window.location.reload();
                            }
                            this.loading = false
                        })

                }).catch(() => {
                    this.loading = false;
                });
            },

            resetFields(){

            }

        }

    }
</script>
