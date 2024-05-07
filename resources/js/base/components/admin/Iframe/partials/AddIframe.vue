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
                <el-tabs v-model="activeTabName" v-if="attribute && attribute.front_options && attribute.front_options.has_caption">
                    <template v-for="(locale,localeKey) in locales">
                        <el-tab-pane class="" :label="locale" :name="locale">
                            <div class="form-group">
                                <label class="col-md-2 control-label">{{ lang.attribute_multi_iframe_title }} {{locale}}</label>
                                <div class="col-md-6">
                                    <input
                                        class="form-control"
                                        :disabled="loading"
                                        v-model="form.fields[locale].title">
                                    </input>
                                </div>
                            </div>
                        </el-tab-pane>
                    </template>
                </el-tabs>

                <div class="form-group">
                    <label class="col-md-1 control-label">{{ lang.attribute_multi_iframe_url }}</label>
                    <div class="col-md-5">
                        <textarea class="form-control" :disabled="loading" v-model="form.iframe"></textarea>
                    </div>
                    <div class="col-md-6">
                        <span v-html="form.iframe"></span>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <div class="demo-drawer__footer">
                            <el-button size="medium" type="info is-plain" @click="modal(false)">{{ lang.attribute_multi_iframe_cancel }}
                            </el-button>
                            <el-button size="medium" type="primary is-plain" icon="el-icon-check" @click="saveImage"
                                       :disabled="loading" :loading="loading">{{ lang.attribute_multi_iframe_save }}
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
            'defaultLocale',
            'tab_name',
            'edit_data',
            'options',
            'updateImageData',
            'formData',
            'attribute'
        ],
        data() {
            return {
                activeTabName: this.defaultLocale,
                dialogVisible: false,
                loading: false,
                form: {
                    ka: {},
                    en: {},
                    url: ''
                }
            }
        },
        created() {
            this.dialogVisible = true;
            this.form = this.edit_data;
            if (!this.form ) {
                this.form = {
                    fields: {}
                }
                this.locales.forEach((locale) => {
                    this.form.fields[locale] = {
                        title: ''
                    }
                })
            }
        },
        methods: {
            clearInputs() {
                this.locales.forEach((locale) => {
                    this.form[locale] = {}
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
                let data = Object.assign({}, this.form);
                this.updateImageData(data, this.form.index);
                this.modal(false);
            }

        }


    }

</script>
