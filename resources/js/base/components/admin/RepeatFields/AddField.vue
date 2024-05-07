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
                            <template v-for="(field,index) in field_form.fields">
                                <div class="form-group popup" v-if="field.is_translation || (!field.is_translation && localeKey == 0)">
                                    <label class="col-md-2 control-label">{{ field.label }} {{ field.is_translation ? locale : '' }}
                                        <template v-if="field.is_required">
                                            <span class="text-danger">*</span>:
                                        </template>
                                    </label>
                                    <div class="col-md-10">

                                        <template v-if="field.type == 'text'">
                                            <template v-if="field.is_translation">
                                                <input
                                                    class="form-control"
                                                    :disabled="loading"
                                                    :placeholder="field.placeholder ? field.placeholder : ''"
                                                    v-model="field_form.fields[index].locales[locale]"></input>
                                            </template>
                                            <template v-else>
                                                <input
                                                    class="form-control"
                                                    :disabled="loading"
                                                    :placeholder="field.placeholder ? field.placeholder : ''"
                                                    v-model="field_form.fields[index].value"></input>
                                            </template>
                                        </template>

                                        <template v-else-if="field.type == 'textarea'">
                                            <template v-if="field.is_translation">
                                                <textarea class="form-control" :disabled="loading" :placeholder="field.placeholder ? field.placeholder : ''"
                                                          v-model="field_form.fields[index].locales[locale]"></textarea>
                                            </template>
                                            <template v-else>
                                                <textarea class="form-control" :disabled="loading" :placeholder="field.placeholder ? field.placeholder : ''"
                                                          v-model="field_form.fields[index].value"></textarea>
                                            </template>
                                        </template>

                                        <template v-else-if="field.type == 'wysiwig'">
                                            <template v-if="field.is_translation">
                                                <ckeditor
                                                    :editor="editor"
                                                    v-model="field_form.fields[index].locales[locale]">
                                                </ckeditor>
                                            </template>
                                            <template v-else>
                                                <ckeditor
                                                    :editor="editor"
                                                    v-model="field_form.fields[index].value">
                                                </ckeditor>
                                            </template>
                                        </template>
                                    </div>

                                </div>
                            </template>

                        </el-tab-pane>

                    </template>
                </el-tabs>
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="demo-drawer__footer">
                            <el-button size="medium" type="info is-plain" @click="modal(false)">{{ lang.close_fields }}
                            </el-button>
                            <el-button size="medium" type="primary is-plain" icon="el-icon-check" @click="saveField"
                                       :disabled="loading" :loading="loading">{{ lang.save_fields }}
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
        'updateFieldData',
        'formData',
        'indexKey'
    ],
    data() {
        return {
            editor: ClassicEditor,
            activeTabName: 'ka',
            dialogVisible: false,
            loading: false,
            field_form: {
            },
            def_locale: this.default_locale
        }
    },
    created() {
        let editData = Object.assign({}, JSON.parse(JSON.stringify(this.edit_data)));
        let additionalFields = editData.additional_fields;
        let fields = editData.fields;
        this.dialogVisible = true;
        if (typeof this.indexKey === 'number') {
            this.field_form.fields = fields;
        } else {
            this.field_form.fields = additionalFields;
        }
    },
    methods: {
        clearInputs() {
            this.field_form = {}
        },

        /**
         * Modal close/show
         */
        modal(status = true) {

            if (status) {
                this.clearInputs();
            } else {
                this.updateFieldData();
            }

            this.dialogVisible = status;
        },

        /**
         *
         * @param done
         */
        handleClose(done) {
            this.updateFieldData();
            done();
        },

        /**
         * Save field data.
         */
        saveField() {
            this.updateFieldData(this.field_form.fields, this.indexKey);
            this.modal(false);
        }

    }


}

</script>
