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
                                <label class="col-md-2 control-label">{{ lang.attribute_iframe_title }} {{locale}}</label>
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
                    <label class="col-md-1 control-label">{{ lang.attribute_iframe_url }}</label>
                    <div class="col-md-5">
                        <textarea class="form-control" :disabled="loading" v-model="form.default_value.iframe"></textarea>
                    </div>
                    <div class="col-md-6">
                        <span v-html="form.default_value.iframe"></span>
                    </div>
                </div>

            </el-row>
        </div>
    </div>
</template>

<script>
export default {
    props: [
        'updateData',
        'lang',
        'locales',
        'defaultLocale',
        'attribute',
        'attributeKey'
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
}
</script>
