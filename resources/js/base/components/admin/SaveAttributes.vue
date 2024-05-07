<template>
    <div>
        <div class="block" >
            <el-form v-loading="loading"
                     element-loading-text="Loading..."
                     element-loading-spinner="el-icon-loading"
                     element-loading-background="rgba(0, 0, 0, 0.0)"
                     ref="form" class="form-horizontal form-bordered">

                <el-tabs v-model="activeTabName">
                    <template v-for="locale in locales">
                        <el-tab-pane class="" :label="locale" :name="locale">

                            <el-row>

                                <template v-for="(attribute,key) in form">

                                    <div class="form-group dashed">
                                        <label class="control-label" :class="attribute.front_options && attribute.front_options.label_grid ? attribute.front_options.label_grid : 'col-md-2'">{{ attribute.frontend_label }}:
                                            <template v-if="attribute.is_translation">
                                                ({{locale}})
                                            </template>
                                        </label>
                                        <div class="uppercase-medium" :class="attribute.front_options && attribute.front_options.input_grid ? attribute.front_options.input_grid : 'col-md-10'">

                                            <template v-if="attribute.frontend_type == 'varchar'">
                                                <template v-if="attribute.is_translation">
                                                        <input class="form-control" :disabled="loading" v-model="attribute.default_value[locale].value"></input>
                                                </template>
                                                <template v-else>
                                                    <input class="form-control" :disabled="loading" v-model="attribute.default_value"></input>
                                                </template>
                                            </template>

                                            <template v-if="attribute.frontend_type == 'wysiwyg'">
                                                <template v-if="attribute.is_translation">
                                                    <ckeditor :config="editorConfigData" :editor="editor" v-model="attribute.default_value[locale].value"></ckeditor>
                                                </template>
                                                <template v-else>
                                                    <ckeditor :config="editorConfigData" :editor="editor" v-model="attribute.default_value"></ckeditor>
                                                </template>
                                            </template>

                                            <template v-if="attribute.frontend_type == 'int'">
                                                <template v-if="attribute.is_translation">
                                                    <el-input-number  :disabled="loading" v-model="attribute.default_value[locale].value"></el-input-number>
                                                </template>
                                                <template v-else>
                                                    <el-input-number  :disabled="loading" v-model="attribute.default_value"></el-input-number>
                                                </template>
                                            </template>

                                            <template v-if="['select'].includes(attribute.frontend_type)">
                                                <el-select
                                                    v-model="attribute.default_value"
                                                    clearable
                                                    filterable
                                                    :placeholder="attribute.frontend_label">
                                                    <el-option
                                                        v-for="(item,key) in attribute.options"
                                                        :key="key"
                                                        :label="item.label"
                                                        :value="item.value.toString()">
                                                    </el-option>
                                                </el-select>
                                            </template>

                                            <template v-if="['multi_select'].includes(attribute.frontend_type)">
                                                <el-select
                                                    v-model="attribute.default_value"
                                                    clearable
                                                    filterable
                                                    multiple
                                                    :placeholder="attribute.frontend_label">
                                                    <el-option
                                                        v-for="(item,key) in attribute.options"
                                                        :key="key"
                                                        :label="item.label"
                                                        :value="item.value">
                                                    </el-option>
                                                </el-select>
                                            </template>

                                            <template v-if="attribute.frontend_type == 'gallery'">
                                                    <multi-image-upload
                                                        :updateData="updateElementData"
                                                        :lang="lang"
                                                        :upload-image-route="routes.upload_image"
                                                        :locales="locales"
                                                        :defaultLocale="defaultLocale"
                                                        :attribute-key="key"
                                                        :attribute="attribute">
                                                    </multi-image-upload>
                                                </template>

                                            <template v-if="attribute.frontend_type == 'iframe'">
                                                <single-iframe
                                                    :updateData="updateElementData"
                                                    :lang="lang"
                                                    :locales="locales"
                                                    :default-locale="defaultLocale"
                                                    :attribute-key="key"
                                                    :attribute="attribute"
                                                ></single-iframe>
                                            </template>

                                            <template v-if="attribute.frontend_type == 'multi_iframe'">
                                                <multi-iframe
                                                    :updateData="updateElementData"
                                                    :lang="lang"
                                                    :locales="locales"
                                                    :default-locale="defaultLocale"
                                                    :attribute-key="key"
                                                    :attribute="attribute"
                                                ></multi-iframe>
                                            </template>

                                            <template v-if="attribute.frontend_type == 'single_image'">

                                                <single-image-upload
                                                    :updateData="updateElementData"
                                                    :lang="lang"
                                                    :upload-image-route="routes.upload_image"
                                                    :locales="locales"
                                                    :default-locale="defaultLocale"
                                                    :attribute-key="key"
                                                    :attribute="attribute">
                                                </single-image-upload>

                                            </template>

                                        </div>
                                    </div>

                                </template>

                            </el-row>

                        </el-tab-pane>
                    </template>
                </el-tabs>

            </el-form>
        </div>
    </div>
</template>


<script>
import {responseParse} from '../../mixins/responseParse'
import {getData} from '../../mixins/getData'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import MyUploadAdapter from "../../mixins/EditorCustomUpload";
import MultiImageUpload from "./Gallery/MultiImageUpload";
import SingleIframe from "./Iframe/SingleIframe";
import SingleImageUpload from "./Gallery/SingleImageUpload";
import MultiIframe from "./Iframe/MultiIframe";

export default {
    components: {MultiIframe, SingleImageUpload, MultiImageUpload,SingleIframe},
    props: [
        'lang',
        'locales',
        'defaultLocale',
        'routes',
        'updateData',
        'item',
        'editorConfig'
    ],
    data(){
        return {
            form: {},
            activeTabName: '',
            loading: false,
            editor: ClassicEditor,
            editorConfigData: {}
        }
    },
    updated() {
        this.updateData('entity_attributes', this.form);
    },
    created(){
    },
    watch: {
        'editorConfig'(){
            if(this.editorConfig) {
                this.editorConfigData = this.editorConfig;
                this.editorConfigData.extraPlugins = [this.meyCustomUploadAdapterPlugin];
            }
        },
        'defaultLocale'(){
            this.activeTabName = this.defaultLocale;
        },
        'locales'(){
            if (!this.item || Object.keys(this.item).length == 0 || (this.item && this.item.options)) {
                this.locales.forEach((value,index) => {
                    if(!this.form[value]) {
                        this.form[value] = {};
                    }
                });
            }
        },
        'item'(){
            if (this.item) {
                this.form = this.item;
            }
        }
    },
    methods: {
        meyCustomUploadAdapterPlugin(editor) {

            editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                // Configure the URL to the upload script in your back-end here!
                return new MyUploadAdapter(loader);
            };

        },
        updateElementData(attributeKey, data) {
            this.form[attributeKey] = data;
        },

        async handleFileUpload(inputId) {
            let file = this.$refs.file.filter((fileItem, index) => {
                return fileItem.id == inputId;
            })[0].files[0];

            var data = new FormData();
            data.append('file', file);
            data.append('type', inputId);

            await getData({
                method: 'POST',
                url: this.routes.upload_image,
                data: data
            }).then(response => {
                // Parse response notification.
                responseParse(response);
                if (response.status == 200) {
                    var itemObject = {};
                    itemObject[inputId] = {item: response.data.item};
                    this.form.images = {...this.form.images, ...itemObject}
                    this.form = {...this.form};
                }
                this.loading = false
            });

        },
    }
}

</script>
