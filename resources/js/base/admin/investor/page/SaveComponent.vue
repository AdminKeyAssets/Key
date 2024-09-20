<template>
    <div class="block">

        <NotifyInvestorByEmail
            v-if="id"
            :investor-id="id">
        </NotifyInvestorByEmail>

        <el-form v-loading="loading"
                 element-loading-text="Loading..."
                 element-loading-spinner="el-icon-loading"
                 element-loading-background="rgba(0, 0, 0, 0.8)"
                 ref="form" :model="form" class="form-horizontal form-bordered">

            <el-row>

                <div class="form-group">
                    <label class="col-md-2 control-label">Name: <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                  v-model="form.name" @input="capitalizeFirstLetter('name')"></el-input>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label">Surname: <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                  v-model="form.surname" @input="capitalizeFirstLetter('surname')"></el-input>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label">ID/Passport Number: <span
                        class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                  v-model="form.pid"></el-input>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label">Citizenship: <span
                        class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-select v-model="form.citizenship" filterable
                                   placeholder="Select Country">
                            <el-option
                                v-for="country in this.countries"
                                :key="country.country"
                                :label="country.country"
                                :value="country.country"
                            ></el-option>
                        </el-select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label">Address: <span
                        class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                  v-model="form.address"></el-input>
                    </div>
                </div>

                <div class="form-group">

                    <label class="col-md-2 control-label">Email: <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                  v-model="form.email"></el-input>
                    </div>

                </div>

                <div class="form-group">

                    <label class="col-md-2 control-label">Is Demo: <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-switch v-model="form.is_demo">
                        </el-switch>
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

                    <label class="col-md-2 control-label">Select Manager: <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-select v-model="form.admin_id" filterable
                                   placeholder="Manager">
                            <el-option
                                v-for="manager in this.managers"
                                :key="manager.id"
                                :label="manager.name + ' ' + manager.surname"
                                :value="manager.id"
                            ></el-option>
                        </el-select>
                    </div>

                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label">Password <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-input class="el-input--is-round" maxlength="150" show-word-limit
                                  v-model="form.password"></el-input>
                    </div>
                    <div class="col-md-3">
                        <el-button type="warning" @click="generatePassword" :disabled="loading" style="margin: 0 1rem">
                            Generate Password
                        </el-button>
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

                <div class="form-group dashed">
                    <label class="col-md-1 control-label">Upload Passport:</label>
                    <div class="col-md-10 uppercase-medium">
                        <input type="file" @change="onPassportChange" accept="image/*">
                        <div v-if="form.passport">
                            <img v-if="form.passportPreview" :src="form.passportPreview" alt="Icon Preview" style="max-width: 100px;"/>
                            <img v-else :src="form.passport" alt="Passport Preview" style="max-width: 100px;"/>
                            <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                       @click="removePassport"></el-button>
                        </div>
                    </div>
                </div>

                <div class="form-group dashed">
                    <label class="col-md-1 control-label">Service Agreement:</label>
                    <div class="col-md-10 uppercase-medium">
                        <input type="file" @change="onServiceAgreementChange">
                        <div v-if="form.service_agreement">
                            <p v-if="form.service_agreement"><a :href="form.service_agreement" target="_blank">View Agreement</a></p>
                            <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                       @click="removeServiceAgreement"></el-button>
                        </div>
                    </div>
                </div>
            </el-row>

            <div class="el-form-item registration-btn">
                <el-button type="primary" @click="save" :disabled="loading"
                           style="margin: 0 1rem">Save
                </el-button>
            </div>
        </el-form>
    </div>
</template>

<style>
.form-group.phone .el-select .el-input {
    width: 110px;
}
.form-group.phone .input-with-select .el-input-group__prepend {
    background-color: #fff;
}
</style>
<script>
import { responseParse } from '../../../mixins/responseParse'
import { getData } from '../../../mixins/getData'
import { hasPermission } from '../../../mixins/hasPermission'
import { Notification } from 'element-ui'
import NotifyInvestorByEmail from "../partials/NotifyInvestorByEmail.vue";

export default {
    components: {NotifyInvestorByEmail},
    props: [
        'getSaveDataRoute',
        'id'
    ],

    data() {
        return {
            data: [],
            loading: false,
            lang: {},
            routes: {},
            options: {},
            countries: [],
            prefixes: [],
            managers: [],
            form: {
                guard_name: 'admin',
                password: '',
                name: '',
                surname: '',
                pid: '',
                citizenship: '',
                address: '',
                email: '',
                phone: '',
                prefix: '',
                admin_id: '',
                profile_picture: null,
                profilePicturePreview: null,
                passport: null,
                passportPreview: null,
                is_demo: false
            },
        }
    },
    created() {
        if (this.id) {
            this.form.id = this.id;
        }
        this.getSaveData();
    },
    methods: {
        async getSaveData() {
            this.loading = true;
            await getData({
                method: 'POST',
                url: this.getSaveDataRoute,
                data: this.form
            }).then(response => {
                responseParse(response, false);
                if (response.status == 200) {
                    let data = response.data.data;

                    this.routes = data.routes;
                    this.options = data.options;
                    if (data.item) {
                        this.form = { ...this.form, ...data.item };
                        this.form.is_demo = Boolean(data.item.is_demo === true || data.item.is_demo === "true" || data.item.is_demo === 1);
                    }
                    if (data.countries) {
                        this.countries = data.countries;
                    }
                    if (data.prefixes) {
                        this.prefixes = data.prefixes;
                    }
                    if (data.managers) {
                        this.managers = data.managers;
                    }
                }
                this.loading = false
            });
        },
        async save() {
            this.loading = true;
            let formData = new FormData();

            for (let key in this.form) {
                if ((key === 'passport' || key === 'profile_picture') && this.form[key]) {
                    formData.append(key, this.form[key]);
                }
                else {
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
                    window.location.href = '/admin/investors';
                }, 1000);
            }).catch(error => {
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
        onServiceAgreementChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.service_agreement = file;
                };
                reader.onerror = (error) => {
                    console.error('Error loading file:', error);
                };
                reader.readAsDataURL(file);
            }
        },

        onPassportChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.passport = file;
                    this.form.passportPreview = e.target.result;
                };
                reader.onerror = (error) => {
                    console.error('Error loading file:', error);
                };
                reader.readAsDataURL(file);
            }
        },

        removePassport() {
            this.form.passport = null;
            this.form.passportPreview = null;
        },

        removeServiceAgreement() {
            this.form.service_agreement = null;
        },
        capitalizeFirstLetter(field) {
            if (this.form[field]) {
                this.form[field] = this.form[field].charAt(0).toUpperCase() + this.form[field].slice(1);
            }
        },
    }
}
</script>
