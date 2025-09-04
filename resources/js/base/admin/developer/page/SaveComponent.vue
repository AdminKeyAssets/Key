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
                    <label class="col-md-2 control-label">Developer Name: <span class="text-danger">*</span></label>
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
                    <label class="col-md-2 control-label">ID Code: </label>
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
                    <label class="col-md-2 control-label">Cell: <span class="text-danger">*</span></label>
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
                    <label class="col-md-2 control-label">Representative Position: </label>
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
                <div class="form-group">
                    <label class="col-md-2 control-label">Upload Service Agreement(s):</label>
                    <div class="col-md-8">
                        <el-form-item
                            v-for="(agreement, index) in form.agreements"
                            :key="agreement.id"
                        >
                            <div class="col-md-3">
                                <el-input
                                    class="col-md-12"
                                    v-model="agreement.name"
                                    placeholder="Name for Agreement"
                                />
                            </div>
                            <div class="col-md-3">
                                <input
                                    type="file"
                                    @change="onAgreementFileChange($event, index)"
                                    v-if="!agreement.path"
                                />
                                <div v-else>
                                    <img
                                        v-if="agreement.path.preview"
                                        :src="agreement.path.preview"
                                        alt="preview"
                                        style="max-width: 100px;"
                                    />
                                    <a
                                        v-else
                                        :href="agreement.path"
                                        target="_blank"
                                    >
                                        {{ agreement.name }}
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <el-button
                                    icon="el-icon-delete-solid"
                                    size="small"
                                    type="danger"
                                    @click.prevent="removeAgreement(agreement)"
                                />
                            </div>
                        </el-form-item>
                        <el-button
                            type="primary"
                            size="medium"
                            icon="el-icon-plus"
                            @click="addAgreement"
                        >
                            Add Agreement
                        </el-button>
                    </div>
                </div>

                <!-- Logo -->
                <div class="form-group dashed">
                    <label class="col-md-2 control-label">Profile Picture:</label>
                    <div class="col-md-6">
                        <input type="file" accept="image/*" @change="onLogoChange"/>
                        <div v-if="form.logoPreview">
                            <img :src="form.logoPreview" style="max-width:100px;"/>
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
                        <input type="file" accept="image/*" @change="onStampChange"/>
                        <div v-if="form.stampPreview">
                            <img :src="form.stampPreview" style="max-width:100px;"/>
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
                        <input type="file" accept="image/*" @change="onSignatureChange"/>
                        <div v-if="form.signaturePreview">
                            <img :src="form.signaturePreview" style="max-width:100px;"/>
                            <el-button
                                icon="el-icon-delete-solid"
                                size="small"
                                type="danger"
                                @click="removeSignature"
                            />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label">Select Assets: </label>
                    <div class="col-md-6">
                        <el-select v-model="form.assets" placeholder="Select Asset" multiple filterable v-remove-readonly>
                            <el-option
                                v-for="asset in assets"
                                :key="asset"
                                :label="asset"
                                :value="asset"
                            ></el-option>
                        </el-select>

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
import {responseParse} from '../../../mixins/responseParse'
import {getData} from '../../../mixins/getData'

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
                logo: null,
                logoPreview: null,
                stamp: null,
                stampPreview: null,
                signature: null,
                signaturePreview: null,
                agreements: [],
                assets: [],
            },
            assets: {}
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
            if (res.status === 200 && res.data.data.assets){
                this.assets = res.data.data.assets;
            }
                this.loading = false
        },

        async save() {

            this.loading = true;
            let formData = new FormData();
            for (let key in this.form) {
                if (['service_agreement', 'logo', 'stamp', 'signature'].includes(key) && this.form[key]) {
                    formData.append(key, this.form[key]);
                } else if (key === 'agreements') {
                    this.form.agreements.forEach((agreement, index) => {
                        formData.append(`agreements[${index}][name]`, agreement.name);
                        if (agreement.path) {
                            if (agreement.path.file) {
                                formData.append(`agreements[${index}][path]`, agreement.path.file);
                            } else {
                                formData.append(`agreements[${index}][path]`, agreement.path);
                            }
                        }
                    });
                }else if(key === 'assets'){
                    this.form.assets.forEach(assetValue => {
                        formData.append('assets[]', assetValue);
                    });
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
        onAgreementFileChange(e, i) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = ev => {
                    const ags = [...this.form.agreements];
                    ags[i].path = {
                        file,
                        preview: file.type.startsWith('image/') ? ev.target.result : null,
                        name: file.name
                    };
                    this.form.agreements = ags;
                };
                reader.readAsDataURL(file);
            }
        },

        addAgreement() {
            const ags = Array.isArray(this.form.agreements) ? [...this.form.agreements] : [];
            ags.push({id: Date.now(), name: '', path: null});
            this.form.agreements = ags;
            console.log(1234)
        },

        removeAgreement(item) {
            const ags = Array.isArray(this.form.agreements)
                ? this.form.agreements.filter(a => a.id !== item.id)
                : [];
            this.form.agreements = ags;
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
