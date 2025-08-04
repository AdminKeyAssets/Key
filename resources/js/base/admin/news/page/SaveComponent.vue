<template>
    <div class="block">
        <el-form  v-loading="loading"
                  element-loading-text="Loading..."
                  element-loading-spinner="el-icon-loading"
                  element-loading-background="rgba(0, 0, 0, 0.8)"
                  ref="form" 
                  :model="form" 
                  :rules="formRules"
                  class="form-horizontal form-bordered">

            <el-row>

                <!-- Title -->
                <el-form-item class="form-group" prop="title">
                    <label class="col-md-2 control-label">{{ lang.title }} <span class="text-danger">*</span>:</label>
                    <div class="col-md-8">
                        <el-input class="el-input--is-round" maxlength="255" show-word-limit :disabled="loading"
                                  v-model="form.title"></el-input>
                    </div>
                </el-form-item>

                <!-- Content -->
                <el-form-item class="form-group" prop="content">
                    <label class="col-md-2 control-label">{{ lang.content }} <span class="text-danger">*</span>:</label>
                    <div class="col-md-8">
                        <el-input type="textarea" :rows="6" maxlength="5000" show-word-limit :disabled="loading"
                                  v-model="form.content" placeholder="Enter news content..."></el-input>
                    </div>
                </el-form-item>

                <!-- Image -->
                <div class="form-group">
                    <label class="col-md-2 control-label">{{ lang.image }}:</label>
                    <div class="col-md-6">
                        <input type="file" @change="handleImageUpload" accept="image/*" class="form-control" :disabled="loading">
                        <small class="text-muted">Max size: 2MB. Supported formats: jpeg, png, jpg, gif</small>
                        <div v-if="imagePreview" class="mt-2">
                            <img :src="imagePreview" alt="Preview" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <el-form-item class="form-group" prop="status">
                    <label class="col-md-2 control-label">{{ lang.status }} <span class="text-danger">*</span>:</label>
                    <div class="col-md-4">
                        <el-select v-model="form.status" :disabled="loading" class="el-input--is-round">
                            <el-option label="Published" value="published"></el-option>
                            <el-option label="Draft" value="draft"></el-option>
                            <el-option label="Archived" value="archived"></el-option>
                        </el-select>
                    </div>
                </el-form-item>

                <!-- Manager (Only for administrators) -->
                <div class="form-group" v-if="options.managers && userRole === 'administrator'">
                    <label class="col-md-2 control-label">{{ lang.manager }}:</label>
                    <div class="col-md-6">
                        <el-select 
                            v-model="form.admin_id" 
                            :disabled="loading" 
                            filterable
                            placeholder="Select Manager"
                            class="el-input--is-round">
                            <el-option
                                v-for="manager in options.managers"
                                :key="manager.id"
                                :label="manager.name + ' ' + manager.surname"
                                :value="manager.id">
                            </el-option>
                        </el-select>
                        <small class="text-muted d-block">Select a manager to assign this news item</small>
                    </div>
                </div>

                <!-- Investors -->
                <div class="form-group">
                    <label class="col-md-2 control-label">{{ lang.investors }}:</label>
                    <div class="col-md-9">
                        <el-select
                            v-model="form.investor_ids"
                            multiple
                            filterable
                            placeholder="Select Investors"
                            class="full-width"
                            :disabled="loading">
                            <el-option
                                v-for="investor in options.investors"
                                :key="investor.id"
                                :label="investor.name + ' ' + investor.surname"
                                :value="investor.id">
                            </el-option>
                        </el-select>
                        <small class="text-muted d-block">Leave empty to make news available to all investors</small>
                    </div>
                </div>

            </el-row>

            <div class="el-form-item registration-btn">
                <el-button type="primary" @click="save" :disabled="loading" style="margin: 0 1rem">{{ lang.save_text }}</el-button>
            </div>
        </el-form>
    </div>
</template>

<style>
    .full-width {
        width: 100%;
    }
    .full-width .el-select {
        width: 100%;
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
            'news'
        ],

        data(){
            return {
                loading: false,
                lang: {},
                routes: {},
                options: {},
                userRole: '',
                imagePreview: null,

                /**
                 * Form validation rules
                 */
                formRules: {
                    title: [
                        { required: true, message: 'News title is required', trigger: 'blur' },
                        { min: 3, max: 255, message: 'Title must be between 3 and 255 characters', trigger: 'blur' }
                    ],
                    content: [
                        { required: true, message: 'News content is required', trigger: 'blur' },
                        { min: 10, max: 5000, message: 'Content must be between 10 and 5000 characters', trigger: 'blur' }
                    ],
                    status: [
                        { required: true, message: 'Please select a status', trigger: 'change' }
                    ]
                },

                /**
                 * Form data
                 */
                form: {
                    title: '',
                    content: '',
                    status: 'published',
                    admin_id: null,
                    investor_ids: [],
                    image: null
                },

            }
        },
        created(){
          this.getSaveData();
        },

        methods: {

            modifyCreateData(){
                this.form = {
                    id: this.news ? this.news.id : '',
                    title: this.news ? this.news.title : '',
                    content: this.news ? this.news.content : '',
                    status: this.news ? this.news.status : 'published',
                    admin_id: this.news ? this.news.admin_id : null,
                    investor_ids: this.news && this.news.investors_ids ? this.news.investors_ids : [],
                    image: null
                }

                // Show existing image preview if editing
                if (this.news && this.news.image) {
                    this.imagePreview = '/storage/' + this.news.image;
                }

            },

            handleImageUpload(event) {
                const file = event.target.files[0];
                if (file) {
                    // Validate file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        Notification.error({
                            title: 'Error',
                            message: 'File size cannot exceed 2MB'
                        });
                        event.target.value = '';
                        return;
                    }

                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        Notification.error({
                            title: 'Error',
                            message: 'Only JPEG, PNG, JPG and GIF images are allowed'
                        });
                        event.target.value = '';
                        return;
                    }

                    this.form.image = file;

                    // Create preview
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imagePreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
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
                        this.userRole = data.userRole || '';

                        // Modify create data.
                        this.modifyCreateData();
                    }
                    this.loading = false
                })
            },

            async save(){
                // Validate form first
                this.$refs.form.validate((valid) => {
                    if (!valid) {
                        Notification.error({
                            title: 'Validation Error',
                            message: 'Please check the form and correct any errors'
                        });
                        return false;
                    }

                    // If validation passes, show confirmation dialog
                    this.$confirm(this.lang.confirm_save, {
                        confirmButtonText: this.lang.confirm_save_yes,
                        cancelButtonText: this.lang.confirm_save_no,
                        type: 'warning'
                    })
                        .then(async () => {

                            this.loading = true;

                            // Create FormData for file upload
                            const formData = new FormData();
                            formData.append('id', this.form.id || '');
                            formData.append('title', this.form.title);
                            formData.append('content', this.form.content);
                            formData.append('status', this.form.status);
                            
                            // Add admin_id if set (for administrators assigning to managers)
                            if (this.form.admin_id) {
                                formData.append('admin_id', this.form.admin_id);
                            }
                            
                            if (this.form.image) {
                                formData.append('image', this.form.image);
                            }

                            // Add investor IDs
                            this.form.investor_ids.forEach((id, index) => {
                                formData.append(`investor_ids[${index}]`, id);
                            });

                            await getData({
                                method: 'POST',
                                url: this.routes.save,
                                data: formData,
                                headers: {
                                    'Content-Type': 'multipart/form-data'
                                }
                            }).then(response => {
                                // Parse response notification.
                                responseParse(response);
                                if (response.status == 200) {
                                    window.location.href = this.routes.index;
                                }
                                this.loading = false
                            })
                        }).catch(() => {
                            // User cancelled the confirmation
                        });
                });
            },

            resetFields(){
            }

        }

    }
</script>
