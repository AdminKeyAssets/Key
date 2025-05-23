<template>
    <div class="block">
        <el-form
            v-loading="loading"
            element-loading-text="Loading..."
            element-loading-spinner="el-icon-loading"
            element-loading-background="rgba(0, 0, 0, 0.8)"
            ref="form"
            :model="form"
            class="form-horizontal form-bordered"
        >
            <el-row>

                <!-- Developer Name -->
                <div class="form-group">
                    <label class="col-md-2 control-label">Developer   Name: <span class="text-danger">*</span></label>
                    <div class="col-md-6">
                        <el-input
                            v-model="form.name"
                            :disabled="loading"
                            maxlength="150"
                            show-word-limit
                        />
                    </div>
                </div>

                <!-- ID Code -->
                <div class="form-group">
                    <label class="col-md-2 control-label">ID Code: <span class="text-danger">*</span></label>
                    <div class="col-md-6">
                        <el-input
                            v-model="form.id_code"
                            :disabled="loading"
                            maxlength="50"
                            show-word-limit
                        />
                    </div>
                </div>

                <!-- Representative -->
                <div class="form-group">
                    <label class="col-md-2 control-label">Representative: <span class="text-danger">*</span></label>
                    <div class="col-md-6">
                        <el-input
                            v-model="form.representative"
                            :disabled="loading"
                            maxlength="150"
                            show-word-limit
                        />
                    </div>
                </div>

                <!-- Tel -->
                <div class="form-group">
                    <label class="col-md-2 control-label">Tel: <span class="text-danger">*</span></label>
                    <div class="col-md-6">
                        <el-input
                            type="tel"
                            v-model="form.tel"
                            :disabled="loading"
                        />
                    </div>
                </div>

                <!-- Representative Position -->
                <div class="form-group">
                    <label class="col-md-2 control-label">Representative Position: <span class="text-danger">*</span></label>
                    <div class="col-md-6">
                        <el-input
                            v-model="form.representative_position"
                            :disabled="loading"
                            maxlength="100"
                            show-word-limit
                        />
                    </div>
                </div>

                <!-- Service Agreement -->
                <div class="form-group dashed">
                    <label class="col-md-2 control-label">Service Agreement:</label>
                    <div class="col-md-6">
                        <input type="file" @change="onServiceAgreementChange" />
                        <div v-if="form.service_agreement">
                            <el-button
                                icon="el-icon-delete-solid"
                                size="small"
                                type="danger"
                                @click="removeServiceAgreement"
                            />
                        </div>
                    </div>
                </div>

                <!-- Logo -->
                <div class="form-group dashed">
                    <label class="col-md-2 control-label">Logo:</label>
                    <div class="col-md-6">
                        <input type="file" accept="image/*" @change="onLogoChange" />
                        <div v-if="form.logoPreview">
                            <img :src="form.logoPreview" style="max-width:100px;" />
                            <el-button
                                icon="el-icon-delete-solid"
                                size="small"
                                type="danger"
                                @click="removeLogo"
                            />
                        </div>
                    </div>
                </div>

                <!-- Stamp -->
                <div class="form-group dashed">
                    <label class="col-md-2 control-label">Stamp:</label>
                    <div class="col-md-6">
                        <input type="file" accept="image/*" @change="onStampChange" />
                        <div v-if="form.stampPreview">
                            <img :src="form.stampPreview" style="max-width:100px;" />
                            <el-button
                                icon="el-icon-delete-solid"
                                size="small"
                                type="danger"
                                @click="removeStamp"
                            />
                        </div>
                    </div>
                </div>

                <!-- Signature -->
                <div class="form-group dashed">
                    <label class="col-md-2 control-label">Signature:</label>
                    <div class="col-md-6">
                        <input type="file" accept="image/*" @change="onSignatureChange" />
                        <div v-if="form.signaturePreview">
                            <img :src="form.signaturePreview" style="max-width:100px;" />
                            <el-button
                                icon="el-icon-delete-solid"
                                size="small"
                                type="danger"
                                @click="removeSignature"
                            />
                        </div>
                    </div>
                </div>

                <!-- Username -->
                <div class="form-group">
                    <label class="col-md-2 control-label">Username: <span class="text-danger">*</span></label>
                    <div class="col-md-6">
                        <el-input
                            v-model="form.username"
                            :disabled="loading"
                            maxlength="100"
                            show-word-limit
                        />
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="col-md-2 control-label">Password: <span class="text-danger">*</span></label>
                    <div class="col-md-6">
                        <el-input
                            v-model="form.password"
                            :disabled="loading"
                            maxlength="100"
                            show-word-limit
                        />
                    </div>
                    <div class="col-md-3">
                        <el-button
                            type="warning"
                            @click="generatePassword"
                            :disabled="loading"
                        >
                            Generate
                        </el-button>
                    </div>
                </div>

            </el-row>

            <div class="el-form-item registration-btn">
                <el-button
                    type="primary"
                    @click="save"
                    :disabled="loading"
                    style="margin: 0 1rem"
                >
                    Save
                </el-button>
            </div>
        </el-form>
    </div>
</template>

<script>
import axios from 'axios'
import { responseParse } from '../../../mixins/responseParse'
import { getData } from '../../../mixins/getData'

export default {
    props: ['getSaveDataRoute', 'id'],
    data() {
        return {
            loading: false,
            routes: {},
            form: {
                id: null,
                name: '',
                id_code: '',
                representative: '',
                tel: '',
                representative_position: '',
                username: '',
                password: '',
                service_agreement: null,
                service_agreement: '',
                logo: null,
                logoPreview: null,
                stamp: null,
                stampPreview: null,
                signature: null,
                signaturePreview: null,
            }
        }
    },
    created() {
        if (this.id) this.form.id = this.id
        this.getSaveData()
    },
    methods: {
        async getSaveData() {
            this.loading = true
            const res = await getData({
                method: 'POST',
                url: this.getSaveDataRoute,
                data: this.form
            })
            responseParse(res, false)
            if (res.status === 200 && res.data.data.item) {
                const item = res.data.data.item
                this.form = {
                    ...this.form,
                    ...item,
                    service_agreement: item.service_agreement || '',
                    logoPreview: item.logo || null,
                    stampPreview: item.stamp || null,
                    signaturePreview: item.signature || null
                }
                this.routes = res.data.data.routes
            }
            this.loading = false
        },

        async save() {

          this.loading = true;
          let formData = new FormData();

          for (let key in this.form) {
            if (['service_agreement','logo','stamp','signature'].includes(key) && this.form[key]) {
              formData.append(key, this.form[key]);
            }
            else {
              formData.append(key, this.form[key]);
            }
          }

          await axios.post('/admin/developers/save', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }).then(response => {
            console.log(response)
            this.loading = false;
            responseParse(response);
            setTimeout(() => {
              window.location.href = '/admin/developers';
            }, 1000);
          }).catch(error => {
            this.loading = false;
            responseParse(error.response);
          });
        },

        generatePassword() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
            let pw = ''
            for (let i = 0; i < 10; i++) {
                pw += chars.charAt(Math.floor(Math.random() * chars.length))
            }
            this.form.password = pw
        },

        // file handlers
        onServiceAgreementChange(e) {
            this.form.service_agreement = e.target.files[0] || null
        },
        removeServiceAgreement() {
            this.form.service_agreement = null
            this.form.service_agreement = ''
        },

        onLogoChange(e) {
            const file = e.target.files[0]
            if (!file) return
            this.form.logo = file
            const reader = new FileReader()
            reader.onload = ev => (this.form.logoPreview = ev.target.result)
            reader.readAsDataURL(file)
        },
        removeLogo() {
            this.form.logo = null
            this.form.logoPreview = null
        },

        onStampChange(e) {
            const file = e.target.files[0]
            if (!file) return
            this.form.stamp = file
            const reader = new FileReader()
            reader.onload = ev => (this.form.stampPreview = ev.target.result)
            reader.readAsDataURL(file)
        },
        removeStamp() {
            this.form.stamp = null
            this.form.stampPreview = null
        },

        onSignatureChange(e) {
            const file = e.target.files[0]
            if (!file) return
            this.form.signature = file
            const reader = new FileReader()
            reader.onload = ev => (this.form.signaturePreview = ev.target.result)
            reader.readAsDataURL(file)
        },
        removeSignature() {
            this.form.signature = null
            this.form.signaturePreview = null
        }
    }
}
</script>

<style>
/* adjust as needed */
</style>
