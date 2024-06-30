<template>
    <div class="block">
        <el-form  v-loading="loading"
                  element-loading-text="Loading..."
                  element-loading-spinner="el-icon-loading"
                  element-loading-background="rgba(0, 0, 0, 0.8)"
                  ref="form" :model="form" class="form-horizontal form-bordered">

            <el-row>

                <div class="form-group" v-if="!form.id">
                    <label class="col-md-2 control-label">Name: <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                            <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                      v-model="form.name" @input="capitalizeFirstLetter('name')"></el-input>
                    </div>
                </div>

                <div class="form-group" v-if="!form.id">
                    <label class="col-md-2 control-label">Surname: <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                  v-model="form.surname" @input="capitalizeFirstLetter('surname')"></el-input>
                    </div>
                </div>

                <div class="form-group" v-if="!form.id">
                    <label class="col-md-2 control-label">ID/Passport Number: <span
                        class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                  v-model="form.pid"></el-input>
                    </div>
                </div>

                <div class="form-group">

                    <label class="col-md-2 control-label">{{ lang.email }} <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                  v-model="form.email"></el-input>
                    </div>

                </div>

                <div class="form-group phone">
                    <label class="col-md-2 control-label">Phone: </label>
                    <div class="col-md-6">
                        <el-input placeholder="Phone" v-model="form.phone" class="input-with-select">
                            <el-select v-model="form.prefix" slot="prepend" filterable
                                       placeholder="Prefix">
                                <el-option
                                    v-for="prefix in this.prefixes"
                                    :key="prefix.prefix"
                                    :label="prefix.prefix"
                                    :value="prefix.prefix"
                                ></el-option>
                            </el-select>
                        </el-input>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label">{{ lang.password }} <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-input class="el-input--is-round" maxlength="150" show-word-limit
                                  v-model="form.password"></el-input>
                    </div>
                    <div class="col-md-3">
                        <el-button type="warning" @click="generatePassword" :disabled="loading " style="margin: 0 1rem">
                            Generate Password
                        </el-button>
                    </div>
                </div>
            </el-row>

            <div class="form-group">
                <label class="col-md-2 control-label">Select Role:</label>
                <div class="col-md-6">
                    <el-select style="width:100%;" v-model="form.roles" filterable
                               placeholder="Select Role">
                        <el-option
                            v-for="role in this.data"
                            :key="role.key"
                            :label="role.label"
                            :value="role.key"
                        ></el-option>
                    </el-select>
                </div>
            </div>

            <div class="form-group dashed">
                <label class="col-md-1 control-label">Profile Picture:</label>
                <div class="col-md-10 uppercase-medium">
                    <input type="file" @change="onProfilePictureChange" accept="image/*">
                    <div v-if="form.profile_picture">
                        <img v-if="form.profilePicturePreview" :src="form.profilePicturePreview" alt="Icon Preview" style="max-width: 100px;"/>
                        <img v-else :src="form.profile_picture" alt="Icon Preview" style="max-width: 100px;"/>
                        <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                   @click="removeProfilePicture"></el-button>
                    </div>
                </div>
            </div>

            <div class="el-form-item registration-btn">
                <el-button type="primary" @click="save" :disabled="loading" style="margin: 0 1rem">{{ lang.save_text }}</el-button>
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
                countries: [],
                prefixes: [],

                /**
                 * Form data
                 */
                form: {
                    guard_name: 'admin',
                    password: '',
                    roles: null
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
                    surname: this.user ? this.user.surname : '',
                    email: this.user ? this.user.email : '',
                    prefix: this.user ? this.user.prefix : '',
                    phone: this.user ? this.user.phone : '',
                    pid: this.user ? this.user.pid : '',
                    profile_picture: this.user ? this.user.profile_picture : '',
                    roles: this.user && this.user.rolesId ? this.user.rolesId[0] : '',
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

                        if(data.prefixes){
                            this.prefixes = data.prefixes;
                        }
                        // Modify create data.
                        this.modifyCreateData();
                    }
                    this.loading = false
                })
            },

            async save() {
                this.loading = true;

                let formData = new FormData();
                for (let key in this.form) {
                    if (key === 'profile_picture' && this.form[key]) {
                        formData.append(key, this.form[key]);
                    } else {
                        formData.append(key, this.form[key]);
                    }
                }

                await axios.post(this.routes.save, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(response => {
                    this.loading = false;
                    responseParse(response);
                    setTimeout(() => {
                        window.location.href ='/admin/users';
                    }, 1000);
                })
                    .catch(error => {
                        this.loading = false;
                        responseParse(error.response);
                    });
            },

            generatePassword() {
                let charactersArray = 'a-z,A-Z,0-9,#'.split(',');
                let CharacterSet = '';
                let password = '';

                if (charactersArray.indexOf('a-z') >= 0) {
                    CharacterSet += 'abcdefghijklmnopqrstuvwxyz';
                }
                if (charactersArray.indexOf('A-Z') >= 0) {
                    CharacterSet += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                }
                if (charactersArray.indexOf('0-9') >= 0) {
                    CharacterSet += '0123456789';
                }

                for (let i = 0; i < 10; i++) {
                    password += CharacterSet.charAt(Math.floor(Math.random() * CharacterSet.length));
                }

                this.form.password = password;
            },

            onProfilePictureChange(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.form.profile_picture = file;
                        this.form.profilePicturePreview = e.target.result;
                    };
                    reader.onerror = (error) => {
                        console.error('Error loading file:', error);
                    };
                    reader.readAsDataURL(file);
                }
            },
            removeProfilePicture() {
                this.form.profile_picture = null;
                this.form.profilePicturePreview = null;
            },
            capitalizeFirstLetter(field) {
                if (this.form[field]) {
                    this.form[field] = this.form[field].charAt(0).toUpperCase() + this.form[field].slice(1);
                }
            },
        }

    }
</script>
