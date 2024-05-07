<template>
    <div>
        <div>
            <el-row v-loading="loading"
                    element-loading-text="Loading..."
                    element-loading-spinner="el-icon-loading"
                    element-loading-background="rgba(0, 0, 0, 0)"
                    class="form-horizontal form-bordered">
                <draggable tag="div" :list="form.fields" handle=".handle">

                    <div class="padding-t col-md-4 col-sm-6 module-images" v-for="(element, idx) in form.fields">

                        <div class="block">

                            <el-button
                                size="small"
                                type="info is-plain"
                                icon="el-icon-sort"
                                class="handle">
                            </el-button>

                            <el-button
                                v-if="item.additional_fields"
                                @click="showEditField(idx)"
                                size="small"
                                type="primary"
                                icon="el-icon-edit">
                            </el-button>

                            <el-button
                                @click="removeField(idx)"
                                size="small"
                                type="danger"
                                icon="el-icon-delete">
                            </el-button>

                            <el-row :gutter="20">
                                <el-col :sm="24" :span="24" class="padding-tb">
                                    <div style="height: 200px; overflow: hidden;">
                                        {{ getTitle(element) }}
                                    </div>
                                    <span>{{ element[fieldKey] ? element[fieldKey].title  : ''}}</span>
                                </el-col>
                            </el-row>

                        </div>

                    </div>
                    <div style="clear: left;"></div>
                </draggable>

                <div class="padding-trl">
                    <div class="block padding-b">
                        <div class="col-12">
                            <el-button
                                type="primary is-plain"
                                size="small"
                                icon="el-icon-plus"
                                @click="showAdd(true)">
                            </el-button>
                        </div>
                    </div>
                </div>

                <template v-if="item.additional_fields">
                    <AddField
                        :item_field="item.additional_fields"
                        v-if="showModal"
                        :formData="formData"
                        :updateFieldData="updateFieldData"
                        :lang="lang"
                        :upload_image_route="uploadImageRoute"
                        :locales="locales"
                        :edit_data="edit_data"
                        :indexKey="indexKey"
                        :default_locale="defaultLocale">
                    </AddField>
                </template>
            </el-row>
        </div>
    </div>
</template>

<style>
.block .el-upload__input{
    display: none;
}
</style>

<script>
import {responseParse} from '../../../mixins/responseParse'
import {getData} from '../../../mixins/getData'
import draggable from "vuedraggable";
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import MyUploadAdapter from '../../../mixins/EditorCustomUpload';
import AddField from "./AddField";

export default {
    components: {AddField, draggable},
    props: [
        'formData',
        'updateData',
        'lang',
        'uploadImageRoute',
        'locales',
        'defaultLocale',
        'moduleName',
        'old_data',
        'options',
        'item',
        'fieldKey'
    ],
    watch: {
        item() {
            if (this.item) {
                this.form = this.item
            }
        }
    },
    data() {
        return {
            indexKey: '',
            activeTabName: 'en',
            dialogVisible: false,
            loading: false,
            form: {
                fields: []
            },
            editor: ClassicEditor,
            showModal: false,
            disabled: false,
            fileList: [],
            edit_data: {},
            additionalFields: this.item.additional_fields
        }
    },
    created() {
        if (this.item) {
            this.form = this.item
        }
    },
    updated() {
        this.updateData(this.moduleName, this.fieldKey,this.form);
    },

    methods: {
        getTitle(element) {
            let title = '';
            let allElements = [];

            if (!(element instanceof Array)) {
                 Object.keys(element).map((key) => {
                     allElements.push(element[key]);
                })
            } else {
                allElements = element;
            }

            console.log(allElements,3)

            allElements.every((item) => {
                if (item.value) {
                    title = item.value;
                    return false;
                } else if (item.locales && item.locales[this.defaultLocale]) {
                    title = item.locales[this.defaultLocale];
                    return false;
                }

                return true;
            })

            return title;
        },
        handleRemove(file) {
            this.fileList = this.fileList.filter(function(item){
                return item.uid != file.uid;
            });
        },
        showAdd() {
            this.indexKey = '';
            let item = Object.assign({}, JSON.parse(JSON.stringify(this.item)));
            this.edit_data.additional_fields = item.additional_fields
            this.forceRerender(true);
        },

        showEditField(index) {
            this.indexKey = index;
            this.edit_data = {};
            this.edit_data.fields = Object.assign({}, JSON.parse(JSON.stringify(this.form.fields[index])));
            this.forceRerender(true);
        },

        removeField(index) {
            this.$confirm(this.lang.remove_field_confirm, {
                confirmButtonText: this.lang.remove_field_confirm_yes,
                cancelButtonText: this.lang.remove_field_confirm_no,
                type: 'warning'
            })
                .then(async () => {
                    this.form.fields = this.form.fields.filter((item, i) => {
                        return i != index;
                    });
                });
        },

        /**
         *
         * @param data
         */
        updateFieldData(data = undefined, index = '') {
            if (data && index !== '') {
                this.form.fields[index] = data;
            } else if (data) {
                this.form.fields.push(data);
            } else {
                let oldData = this.form;
                this.form = {};
                this.form = oldData;
            }

            this.updateData(this.moduleName, this.fieldKey,this.form);
            this.indexKey = '';
        },

        forceRerender(showComponent = false) {
            // Remove my-component from the DOM
            this.showModal = !showComponent;
            this.$nextTick(() => {
                // Add the component back in
                this.showModal = showComponent;
            });
        },

        /**
         *
         * @param items
         * @param locale
         */
        getTranslationItem(items, locale) {
            let searchItem = {};
            items.forEach((item) => {
                if (item.locale == locale) {
                    searchItem = item;
                }
            });
            return searchItem;
        }

    }

}

</script>
