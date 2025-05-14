<template>
    <div class="block">
        <form class="form-horizontal form-bordered">
            <fieldset>
                <legend>Developer Information</legend>
                <div class="row">
                    <!-- Developer Name -->
                    <div class="form-group col-md-6">
                        <label class="control-label">Developer Name <span class="text-danger">*</span></label>
                        <div>
                            <input type="text" name="name" v-model="form.name" class="form-control" placeholder="Developer Name">
                            <div v-if="errors.name" class="text-danger">{{ errors.name[0] }}</div>
                        </div>
                    </div>

                    <!-- ID Code -->
                    <div class="form-group col-md-6">
                        <label class="control-label">ID Code <span class="text-danger">*</span></label>
                        <div>
                            <input type="text" name="id_code" v-model="form.id_code" class="form-control" placeholder="ID Code">
                            <div v-if="errors.id_code" class="text-danger">{{ errors.id_code[0] }}</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Representative -->
                    <div class="form-group col-md-6">
                        <label class="control-label">Representative <span class="text-danger">*</span></label>
                        <div>
                            <input type="text" name="representative" v-model="form.representative" class="form-control" placeholder="Representative Name">
                            <div v-if="errors.representative" class="text-danger">{{ errors.representative[0] }}</div>
                        </div>
                    </div>

                    <!-- Representative Position -->
                    <div class="form-group col-md-6">
                        <label class="control-label">Representative Position <span class="text-danger">*</span></label>
                        <div>
                            <input type="text" name="representative_position" v-model="form.representative_position" class="form-control" placeholder="Representative Position">
                            <div v-if="errors.representative_position" class="text-danger">{{ errors.representative_position[0] }}</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Tel -->
                    <div class="form-group col-md-6">
                        <label class="control-label">Tel <span class="text-danger">*</span></label>
                        <div>
                            <input type="text" name="tel" v-model="form.tel" class="form-control" placeholder="Telephone">
                            <div v-if="errors.tel" class="text-danger">{{ errors.tel[0] }}</div>
                        </div>
                    </div>

                    <!-- Service Agreement -->
                    <div class="form-group col-md-6">
                        <label class="control-label">Service Agreement</label>
                        <div>
                            <input type="file" name="service_agreement" @change="onFileChange('service_agreement', $event)" class="form-control">
                            <div v-if="errors.service_agreement" class="text-danger">{{ errors.service_agreement[0] }}</div>
                            <div v-if="form.service_agreement" class="mt-2">
                                <a :href="form.service_agreement" target="_blank">View Current Service Agreement</a>
                                <button @click.prevent="clearFile('service_agreement')" class="btn btn-xs btn-danger ml-2">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Logo & Signatures</legend>
                <div class="row">
                    <!-- Logo -->
                    <div class="form-group col-md-4">
                        <label class="control-label">Logo</label>
                        <div>
                            <input type="file" name="logo" @change="onFileChange('logo', $event)" class="form-control">
                            <div v-if="errors.logo" class="text-danger">{{ errors.logo[0] }}</div>
                            <div v-if="form.logo" class="mt-2">
                                <img :src="form.logo" style="max-width: 100px; max-height: 100px;">
                                <button @click.prevent="clearFile('logo')" class="btn btn-xs btn-danger ml-2">Remove</button>
                            </div>
                        </div>
                    </div>

                    <!-- Stamp -->
                    <div class="form-group col-md-4">
                        <label class="control-label">Stamp</label>
                        <div>
                            <input type="file" name="stamp" @change="onFileChange('stamp', $event)" class="form-control">
                            <div v-if="errors.stamp" class="text-danger">{{ errors.stamp[0] }}</div>
                            <div v-if="form.stamp" class="mt-2">
                                <img :src="form.stamp" style="max-width: 100px; max-height: 100px;">
                                <button @click.prevent="clearFile('stamp')" class="btn btn-xs btn-danger ml-2">Remove</button>
                            </div>
                        </div>
                    </div>

                    <!-- Signature -->
                    <div class="form-group col-md-4">
                        <label class="control-label">Signature</label>
                        <div>
                            <input type="file" name="signature" @change="onFileChange('signature', $event)" class="form-control">
                            <div v-if="errors.signature" class="text-danger">{{ errors.signature[0] }}</div>
                            <div v-if="form.signature" class="mt-2">
                                <img :src="form.signature" style="max-width: 100px; max-height: 100px;">
                                <button @click.prevent="clearFile('signature')" class="btn btn-xs btn-danger ml-2">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Login Information</legend>
                <div class="row">
                    <!-- Username -->
                    <div class="form-group col-md-6">
                        <label class="control-label">Username <span class="text-danger">*</span></label>
                        <div>
                            <input type="text" name="username" v-model="form.username" class="form-control" placeholder="Username">
                            <div v-if="errors.username" class="text-danger">{{ errors.username[0] }}</div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group col-md-6">
                        <label class="control-label">Password <span v-if="!id" class="text-danger">*</span></label>
                        <div>
                            <input type="password" name="password" v-model="form.password" class="form-control" placeholder="Password">
                            <div v-if="!id" class="help-block">Required for new developers</div>
                            <div v-else class="help-block">Leave blank to keep current password</div>
                            <div v-if="errors.password" class="text-danger">{{ errors.password[0] }}</div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <div class="form-group form-actions">
                <div>
                    <button type="submit" class="btn btn-primary" @click.prevent="submit">
                        <i class="fa" :class="loading ? 'fa-spinner fa-spin' : 'fa-check'"></i> Save
                    </button>
                    <button type="reset" class="btn btn-warning" @click.prevent="reset">
                        <i class="fa fa-repeat"></i> Reset
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
export default {
    props: {
        id: {
            required: false
        },
        getSaveDataRoute: {
            required: true,
            type: String
        }
    },
    data() {
        return {
            form: {
                name: '',
                id_code: '',
                representative: '',
                representative_position: '',
                tel: '',
                service_agreement: null,
                logo: null,
                stamp: null,
                signature: null,
                username: '',
                password: ''
            },
            errors: {},
            loading: false,
            saveRoute: '',
            redirectRoute: '',
            files: {
                logo: null,
                stamp: null,
                signature: null,
                service_agreement: null
            }
        };
    },
    mounted() {
        this.getFormData();
    },
    methods: {
        getFormData() {
            this.loading = true;
            axios.post(this.getSaveDataRoute, {
                id: this.id
            })
                .then(response => {
                    if (response.data.status === 200) {
                        this.saveRoute = response.data.data.routes.save;
                        this.redirectRoute = response.data.data.routes.create;

                        if (response.data.data.item) {
                            const item = response.data.data.item;
                            this.form = {
                                name: item.name || '',
                                id_code: item.id_code || '',
                                representative: item.representative || '',
                                representative_position: item.representative_position || '',
                                tel: item.tel || '',
                                service_agreement: item.service_agreement || null,
                                logo: item.logo || null,
                                stamp: item.stamp || null,
                                signature: item.signature || null,
                                username: item.username || '',
                                password: ''
                            };
                        }
                    } else {
                        this.$message.error(response.data.message);
                    }
                    this.loading = false;
                })
                .catch(error => {
                    console.error('Error loading developer data:', error);
                    this.loading = false;
                    this.$message.error('Error loading developer data');
                });
        },
        onFileChange(field, event) {
            if (event.target.files.length > 0) {
                this.files[field] = event.target.files[0];
                // Preview for images
                if (['logo', 'stamp', 'signature'].includes(field)) {
                    this.form[field] = URL.createObjectURL(event.target.files[0]);
                } else {
                    // Just mark the file is selected for non-image files
                    this.form[field] = 'file_selected';
                }
            }
        },
        clearFile(field) {
            this.files[field] = null;
            this.form[field] = null;
        },
        submit() {
            this.loading = true;
            this.errors = {};

            const formData = new FormData();
            
            // Append text fields
            formData.append('name', this.form.name);
            formData.append('id_code', this.form.id_code);
            formData.append('representative', this.form.representative);
            formData.append('representative_position', this.form.representative_position);
            formData.append('tel', this.form.tel);
            formData.append('username', this.form.username);
            
            if (this.form.password) {
                formData.append('password', this.form.password);
            }

            if (this.id) {
                formData.append('id', this.id);
            }

            // Append files if they exist
            if (this.files.logo) {
                formData.append('logo', this.files.logo);
            } else if (this.form.logo === null) {
                formData.append('logo', '');
            } else {
                formData.append('logo', this.form.logo || '');
            }

            if (this.files.stamp) {
                formData.append('stamp', this.files.stamp);
            } else if (this.form.stamp === null) {
                formData.append('stamp', '');
            } else {
                formData.append('stamp', this.form.stamp || '');
            }

            if (this.files.signature) {
                formData.append('signature', this.files.signature);
            } else if (this.form.signature === null) {
                formData.append('signature', '');
            } else {
                formData.append('signature', this.form.signature || '');
            }

            if (this.files.service_agreement) {
                formData.append('service_agreement', this.files.service_agreement);
            } else if (this.form.service_agreement === null) {
                formData.append('service_agreement', '');
            } else {
                formData.append('service_agreement', this.form.service_agreement || '');
            }

            axios.post(this.saveRoute, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
                .then(response => {
                    if (response.data.status === 200) {
                        this.$message.success(response.data.message);
                        if (!this.id) {
                            this.reset();
                        }
                    } else {
                        this.$message.error(response.data.message);
                    }
                    this.loading = false;
                })
                .catch(error => {
                    this.loading = false;
                    if (error.response && error.response.data && error.response.data.errors) {
                        this.errors = error.response.data.errors;
                        this.$message.error('Please correct the errors in the form.');
                    } else {
                        this.$message.error('An error occurred while saving the developer.');
                    }
                    console.error('Error saving developer:', error);
                });
        },
        reset() {
            this.form = {
                name: '',
                id_code: '',
                representative: '',
                representative_position: '',
                tel: '',
                service_agreement: null,
                logo: null,
                stamp: null,
                signature: null,
                username: '',
                password: ''
            };
            this.files = {
                logo: null,
                stamp: null,
                signature: null,
                service_agreement: null
            };
            this.errors = {};
        }
    }
};
</script>
