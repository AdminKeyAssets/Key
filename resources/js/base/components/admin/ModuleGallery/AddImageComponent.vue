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

                <el-tabs v-model="def_locale" v-if="item_field">
                    <template v-for="(locale,localeKey) in locales">

                        <el-tab-pane class="" :label="locale" :name="locale">
                            <template v-for="(field,index) in edit_data.additional_fields">
                                <div class="form-group popup" v-if="field.is_translation || (!field.is_translation && localeKey == 0)">
                                    <label class="col-md-2 control-label">{{ field.label }} {{ field.is_translation ? locale : '' }}
                                        <template v-if="field.is_required">
                                            <span class="text-danger">*</span>:
                                        </template>
                                    </label>
                                    <div class="col-md-10">

                                        <template v-if="field.type == 'text'">
                                            <template v-if="field.is_translation">
                                                <el-input
                                                    class="el-input--is-round"
                                                    :disabled="loading"
                                                    :placeholder="field.placeholder ? field.placeholder : ''"
                                                    v-model="image_form.additional_fields[index].locales[locale]"></el-input>
                                            </template>
                                            <template v-else>
                                                <el-input
                                                    class="el-input--is-round"
                                                    :disabled="loading"
                                                    :placeholder="field.placeholder ? field.placeholder : ''"
                                                    v-model="image_form.additional_fields[index].value"></el-input>
                                            </template>
                                        </template>

                                        <template v-else-if="field.type == 'textarea'">
                                            <template v-if="field.is_translation">
                                                <el-input type="textarea" class="el-input--is-round" :disabled="loading" :placeholder="field.placeholder ? field.placeholder : ''"
                                                          v-model="image_form.additional_fields[index].locales[locale]"></el-input>
                                            </template>
                                            <template v-else>
                                                <el-input type="textarea" class="el-input--is-round" :disabled="loading" :placeholder="field.placeholder ? field.placeholder : ''"
                                                          v-model="image_form.additional_fields[index].value"></el-input>
                                            </template>
                                        </template>

                                        <template v-else-if="field.type == 'wysiwig'">
                                            <template v-if="field.is_translation">
                                                <ckeditor
                                                    :editor="editor"
                                                    v-model="image_form.additional_fields[index].locales[locale]">
                                                </ckeditor>
                                            </template>
                                            <template v-else>
                                                <ckeditor
                                                    :editor="editor"
                                                    v-model="image_form.additional_fields[index].value">
                                                </ckeditor>
                                            </template>
                                        </template>

                                        <template v-else-if="field.type == 'select'">
                                            <template v-if="field.is_translation">
                                                <el-select
                                                    :placeholder="field.label"
                                                    v-model="image_form.additional_fields[index].locales[locale]"
                                                    clearable
                                                    filterable>
                                                    <el-option
                                                        v-for="(item,key) in field.options"
                                                        :key="key"
                                                        :label="item.label"
                                                        :value="item.value">
                                                    </el-option>
                                                </el-select>
                                            </template>
                                            <template v-else>
                                                <el-select
                                                    :placeholder="field.label"
                                                    v-model="image_form.additional_fields[index].value"
                                                    clearable
                                                    filterable>
                                                    <el-option
                                                        v-for="(item,key) in field.options"
                                                        :key="key"
                                                        :label="item.label"
                                                        :value="item.value">
                                                    </el-option>
                                                </el-select>
                                            </template>
                                        </template>

                                        <template v-else-if="field.type == 'image'">
                                            <template v-if="!field.is_translation">
                                                <input style="display: block !important;"
                                                       :id="'image_' + index" :ref="'image_' + index" type="file"
                                                       v-on:change="handleFieldFileUpload(index,field)"/>
                                                <template v-if="field.image">
                                                    <img :src="field.image.full_src"
                                                         style="width: 100%;">
                                                </template>

                                            </template>
                                        </template>

                                    </div>

                                </div>
                            </template>

                            <div class="form-group" v-if="default_locale === locale">

                                <label class="col-md-2 control-label">{{ lang.image_url }}<span
                                    class="text-danger">*</span>:</label>
                                <div class="col-md-6">
                                    <input style="display: block !important;" id="file" ref="file" type="file"
                                           v-on:change="handleFileUpload()"/>
                                </div>

                                <div v-if="image_form.url">
                                    <div class="col-md-offset-2 col-md-6 padding-t">
                                        <img :src="image_form.url" style="width: 100%;">
                                    </div>
                                </div>
                            </div>
                        </el-tab-pane>

                    </template>
                </el-tabs>
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="demo-drawer__footer">
                            <el-button size="medium" type="info is-plain" @click="modal(false)">{{ lang.close_image }}
                            </el-button>
                            <el-button size="medium" type="primary is-plain" icon="el-icon-check" @click="saveimage"
                                       :disabled="loading" :loading="loading">{{ lang.save_image }}
                            </el-button>
                        </div>
                    </div>
                </div>
            </div>
        </el-drawer>
    </div>
</template>

<script>
    import {responseParse} from '../../../mixins/responseParse'
    import {getData} from '../../../mixins/getData'
    import ClassicEditor from "@ckeditor/ckeditor5-build-classic";

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
                editor: ClassicEditor,
                activeTabName: 'ka',
                dialogVisible: false,
                loading: false,
                image_form: {
                },
                def_locale: this.default_locale
            }
        },
        created() {
            this.dialogVisible = true;
            this.image_form = this.edit_data;
        },
        methods: {

            async handleFieldFileUpload (index,field) {

                console.log(this.$refs['image_' + index], this.$refs)

                let file = this.$refs['image_' + index][0].files[0];

                var data = new FormData();
                data.append('file', file);
                data.append('type', 'custom_field_' + field.name);

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
                        this.image_form.additional_fields[index].image = {...this.image_form.additional_fields[index].image,...response.data.item}
                        this.modules[moduleKey].inputs[fieldKey].image = {...this.modules[moduleKey].inputs[fieldKey], ...response.data.item}
                    }
                    this.loading = false
                });
            },

            /**
             * File upload.
             */
            async handleFileUpload() {

                let file = this.$refs.file[0].files[0];

                this.image_form = {...this.image_form, ...{url: URL.createObjectURL(file)}};

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
                        this.image_form.image_id = data.item.id;
                        this.image_form.url = data.item.full_src;
                    }
                    this.loading = false
                });

            },

            clearInputs() {
                // this.locales.forEach((locale) => {
                //     this.image_form[locale] = {}
                // });
                // this.image_form = {...this.image_form, ...{link: '', index: '', url: ''}};
            },

            /**
             * Modal close/show
             */
            modal(status = true) {

                if (status) {
                    this.clearInputs();
                } else {
                    this.updateImageData();
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
            saveimage() {
                // return;
                let data = Object.assign({}, this.image_form);
                this.updateImageData(data, this.image_form ? this.image_form.index : '');
                this.modal(false);
            }

        }


    }

</script>
