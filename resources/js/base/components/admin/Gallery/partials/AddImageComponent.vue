<template>
    <div>
        <el-drawer
            v-loading="loading"
            element-loading-text="Loading..."
            element-loading-spinner="el-icon-loading"
            element-loading-background="rgba(0, 0, 0, 0)"
            :title="lang.add_image"
            :visible.sync="dialogVisible"
            size="60%"
            direction="rtl"
            custom-class="demo-drawer"
            ref="drawer"
            :before-close="handleClose">
            <div class="demo-drawer__content">
                <el-tabs v-model="activeTabName" >
                    <template v-for="(locale,localeKey) in locales">

                        <el-tab-pane class="" :label="locale" :name="locale">

                                <div class="form-group">
                                    <label class="col-md-2 control-label">{{ lang.attribute_multi_image_title }} {{locale}}</label>
                                    <div class="col-md-10">
                                            <input
                                                class="form-control"
                                                :disabled="loading"
                                                v-model="image_form.fields[locale].title">
                                            </input>
                                    </div>
                                </div>
                        </el-tab-pane>
                    </template>
                </el-tabs>

                <div class="form-group">

                    <label class="col-md-2 control-label">{{ lang.attribute_multi_image_upload }}<span
                        class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <input style="display: block !important;" id="file" ref="file" type="file"
                               v-on:change="handleFileUpload()"/>
                    </div>

                    <div v-if="image_form.full_src">
                        <div class="col-md-offset-2 col-md-6 padding-t">
                            <img :src="image_form.full_src" style="width: 100%;">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <div class="demo-drawer__footer">
                            <el-button size="medium" type="info is-plain" @click="modal(false)">{{ lang.attribute_multi_image_cancel }}
                            </el-button>
                            <el-button size="medium" type="primary is-plain" icon="el-icon-check" @click="saveImage"
                                       :disabled="loading" :loading="loading">{{ lang.attribute_multi_image_save }}
                            </el-button>
                        </div>
                    </div>
                </div>
            </div>
        </el-drawer>
    </div>
</template>

<script>
    import {responseParse} from '../../../../mixins/responseParse'
    import {getData} from '../../../../mixins/getData'

    export default {
        props: [
            'item_field',
            'lang',
            'upload_image_route',
            'locales',
            'default_locale',
            'tab_name',
            'edit_data',
            'options',
            'updateImageData',
            'formData'
        ],
        data() {
            return {
                activeTabName: 'ka',
                dialogVisible: false,
                loading: false,
                image_form: {
                    ka: {},
                    en: {},
                    url: ''
                },
                def_locale: this.default_locale
            }
        },
        created() {
            this.dialogVisible = true;
            this.image_form = this.edit_data;
            if (!this.image_form.fields) {
                this.image_form.fields = {}
                this.locales.forEach((locale) => {
                    this.image_form.fields[locale] = {
                        title: ''
                    }
                })
            }
        },
        methods: {
            /**
             * File upload.
             */
            async handleFileUpload() {
                let file = this.$refs.file.files[0];
                var data = new FormData();
                data.append('file', file);
                data.append('type', 'gallery_image');
                await getData({
                    method: 'POST',
                    url: this.upload_image_route,
                    data: data
                }).then(response => {
                    // Parse response notification.
                    responseParse(response);
                    if (response.status == 200) {
                        // Response data.
                        let data = response.data;
                        // Set image.
                        let fields = this.image_form.fields;
                        let index = this.image_form.index;
                        this.image_form = data.item;
                        this.image_form.fields = fields;
                        this.image_form.index = index;
                    }
                    this.loading = false
                });

            },

            clearInputs() {
                this.locales.forEach((locale) => {
                    this.image_form[locale] = {}
                });
                this.image_form = {...this.image_form, ...{link: '', index: '', url: ''}};
            },

            /**
             * Modal close/show
             */
            modal(status = true) {
                if (status) {
                    this.clearInputs();
                }
                this.dialogVisible = status;
            },

            /**
             *
             * @param done
             */
            handleClose(done) {
                this.updateImageData();
                done();
            },

            /**
             * Save image data.
             */
            saveImage() {
                let data = Object.assign({}, this.image_form);
                this.updateImageData(data, this.image_form.index);
                this.modal(false);
            }

        }


    }

</script>
