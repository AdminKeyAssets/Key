<template>
    <div>
        <div class="block" >
            <el-form v-loading="loading"
                     element-loading-text="Loading..."
                     element-loading-spinner="el-icon-loading"
                     element-loading-background="rgba(0, 0, 0, 0.0)"
                     ref="form" :model="form" class="form-horizontal form-bordered">

                <el-tabs v-model="activeTabName">
                    <template v-for="locale in locales">
                        <el-tab-pane class="" :label="locale" :name="locale">

                            <el-row>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">{{ lang.seo_title }} <span class="grey">{{ locale }}</span>:</label>
                                    <div class="col-md-6 uppercase-medium">
                                        <input class="form-control" :disabled="loading || form.options.seo_title == 2" v-model="form[locale].title"></input>
                                    </div>
                                    <div class="col-md-4 uppercase-medium">
                                        <el-radio v-model="form.options.seo_title" :label="1">{{ lang.seo_title_enable }}</el-radio>
                                        <el-radio v-model="form.options.seo_title" :label="2">{{ lang.seo_title_disable }}</el-radio>
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">{{ lang.seo_description }} <span class="grey">{{ locale }}</span>:</label>
                                    <div class="col-md-6 uppercase-medium">
                                        <textarea rows="2"  class="form-control" :disabled="loading || form.options.seo_description == 2" v-model="form[locale].description"></textarea>
                                    </div>
                                    <div class="col-md-4 uppercase-medium">
                                        <el-radio v-model="form.options.seo_description" :label="1">{{ lang.seo_description_enable }}</el-radio>
                                        <el-radio v-model="form.options.seo_description" :label="2">{{ lang.seo_description_disable }}</el-radio>
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">{{ lang.seo_keywords }} <span class="grey">{{ locale }}</span>:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <textarea rows="2"  class="form-control" :disabled="loading" v-model="form[locale].keywords"></textarea>
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">{{ lang.seo_alt_image }} <span class="grey">{{ locale }}</span>:</label>
                                    <div class="col-md-6 uppercase-medium">
                                        <input  class="form-control" :disabled="loading || form.options.seo_alt_image == 2" v-model="form[locale].alt_image"></input>
                                    </div>
                                    <div class="col-md-4 uppercase-medium">
                                        <el-radio v-model="form.options.seo_alt_image" :label="1">{{ lang.seo_alt_image_enable }}</el-radio>
                                        <el-radio v-model="form.options.seo_alt_image" :label="2">{{ lang.seo_alt_image_disable }}</el-radio>
                                    </div>
                                </div>

                                <template v-if="defaultLocale == locale">

                                    <div class="form-group dashed">
                                        <label class="col-md-1 control-label">{{ lang.seo_robots }}:</label>
                                        <div class="col-md-10 uppercase-medium">
                                            <input class="form-control" maxlength="150"
                                                   :disabled="loading"
                                                   v-model="form.robots"></input>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-2 control-label">{{ lang.seo_image }}</label>
                                        <div class="col-md-4" v-if="form.options.seo_image == 1">
                                            <input :didasbled="form.options.seo_image == 2" style="display: block !important;" id="seo_image" ref="file" type="file"
                                                   v-on:change="handleFileUpload('seo_image')"/>
                                        </div>
                                        <div class="col-md-4 uppercase-medium">
                                            <el-radio v-model="form.options.seo_image" :label="1">{{ lang.seo_image_enable }}</el-radio>
                                            <el-radio v-model="form.options.seo_image" :label="2">{{ lang.seo_image_disable }}</el-radio>
                                        </div>
                                        <div v-if="form.images && form.images.seo_image">
                                            <div class="col-md-offset-2 col-md-4 padding-t">
                                                <img :src="form.images.seo_image.item.full_src" style="width: 100%;">
                                            </div>
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

export default {
    props: [
        'lang',
        'locales',
        'defaultLocale',
        'routes',
        'updateData',
        'item'
    ],
    data(){
        return {
            form: {},
            activeTabName: '',
            loading: false,
            editor: ClassicEditor,
        }
    },
    updated() {
        this.updateData('seo', this.form);
    },
    created(){
        this.setDefaultOptions();
    },
    watch: {
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
            this.setDefaultOptions();
        },
        'item'(){
            if (this.item) {
                this.form = this.item;
            }
            this.setDefaultOptions();
        }
    },
    methods: {

        setDefaultOptions(){
            if (!this.form.options) {
                this.form.options = {
                    "seo_title": 2,
                    "seo_description": 2,
                    "seo_alt_image": 2,
                    "seo_image": 2
                };
            }
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
