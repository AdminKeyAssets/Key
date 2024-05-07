<template>
    <div>
        <div class="block">
            <el-row v-loading="loading"
                    element-loading-text="Loading..."
                    element-loading-spinner="el-icon-loading"
                    element-loading-background="rgba(0, 0, 0, 0)"
                    class="form-horizontal form-bordered">
                <el-tabs v-model="activeTabName" v-if="form && form.front_options && form.front_options.has_caption">
                    <template v-for="(locale,localeKey) in locales">
                        <el-tab-pane class="" :label="locale" :name="locale">
                            <div class="form-group">
                                <label class="col-md-2 control-label">{{ lang.attribute_single_image_title }} {{locale}}</label>
                                <div class="col-md-6">
                                    <input
                                        class="form-control"
                                        :disabled="loading"
                                        v-model="form.default_value.translations[locale].title">
                                    </input>
                                </div>
                            </div>
                        </el-tab-pane>
                    </template>
                </el-tabs>

                <div class="form-group">
                    <label class="col-md-2 control-label">{{ lang.attribute_single_image_upload }}</label>
                    <div class="col-md-6">
                        <input style="display: block !important;" id="image" ref="file" type="file"
                               v-on:change="handleFileUpload"/>
                    </div>

                    <div v-if="form.default_value && form.default_value.item">
                        <div class="col-md-offset-2 col-md-4 padding-t">
                            <img :src="form.default_value.item.full_src" style="width: 100%;">
                        </div>
                    </div>
                </div>

            </el-row>
        </div>
    </div>
</template>

<script>
import {getData} from "../../../mixins/getData";
import {responseParse} from "../../../mixins/responseParse";

export default {
    props: [
        'updateData',
        'lang',
        'locales',
        'defaultLocale',
        'attribute',
        'attributeKey',
        'uploadImageRoute'
    ],
    data() {
        return {
            loading: false,
            activeTabName: this.defaultLocale,
            form: {}
        }
    },
    created () {
        this.form = this.attribute;

        if (!this.form.default_value) {
            this.form.default_value = {
                translations: {}
            }
            this.locales.forEach((locale) => {
                this.form.default_value.translations[locale] = {
                    title: ''
                }
            })
        } else if (this.form.default_value && !this.form.default_value.translations) {
            this.locales.forEach((locale) => {
                this.form.default_value.translations[locale] = {
                    title: ''
                }
            })
        }
    },
    updated() {
        this.updateData(this.attributeKey, this.form);
    },
    methods: {
        async handleFileUpload(inputId) {
            let file = this.$refs.file.files[0];

            var data = new FormData();
            data.append('file', file);
            data.append('type', 'single_image');

            await getData({
                method: 'POST',
                url: this.uploadImageRoute,
                data: data
            }).then(response => {
                // Parse response notification.
                responseParse(response);
                if (response.status == 200) {
                    let itemObject = {item: response.data.item};
                    this.form.default_value = {...this.form.default_value, ...itemObject}
                    this.form = {...this.form};
                    console.log(this.form)
                }
                this.loading = false
            });

        },
    }
}
</script>
