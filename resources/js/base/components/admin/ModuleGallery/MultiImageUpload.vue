<template>
    <div>
        <div>
            <el-row v-loading="loading"
                    element-loading-text="Loading..."
                    element-loading-spinner="el-icon-loading"
                    element-loading-background="rgba(0, 0, 0, 0)"
                    class="form-horizontal form-bordered">
                <el-upload
                    action="#"
                    list-type="picture-card"
                    :file-list="fileList"
                    :on-change="handleChange"
                    multiple
                    style="margin-top: 30px;"
                    :auto-upload="false">
                    <i slot="default" class="el-icon-plus" :title="lang.multi_upload"></i>
                    <div slot="file" slot-scope="{file}">
                        <img
                            class="el-upload-list__item-thumbnail"
                            :src="file.url" alt=""
                        >
                        <span class="el-upload-list__item-actions">
                        <span
                            class="el-upload-list__item-preview"
                            @click="handlePictureCardPreview(file)"
                        >
                      <i class="el-icon-zoom-in"></i>
                    </span>
                  </span>
                    </div>
                </el-upload>
                <el-dialog :visible.sync="dialogVisible">
                    <img width="100%" :src="dialogImageUrl" alt="">
                </el-dialog>

                <draggable tag="div" :list="form.images" handle=".handle">

                    <div class="padding-t col-md-4 col-sm-6 module-images" v-for="(element, idx) in form.images">

                        <div class="block">

                            <el-button
                                :title="lang.image_drag"
                                size="small"
                                type="info is-plain"
                                icon="el-icon-sort"
                                class="handle">
                            </el-button>

                            <el-button
                                v-if="field.additional_fields"
                                @click="showEditImage(idx)"
                                :title="lang.image_edit"
                                size="small"
                                type="primary"
                                icon="el-icon-edit">
                            </el-button>

                            <el-button
                                @click="removeImage(idx)"
                                :title="lang.image_delete"
                                size="small"
                                type="danger"
                                icon="el-icon-delete">
                            </el-button>

                            <el-row :gutter="20">
                                <el-col :sm="24" :span="24" class="padding-tb">
                                    <div style="height: 200px; overflow: hidden;">
                                        <img :src="element.url" style="width: 100%;">
                                    </div>
                                    <span>{{ element[fieldKey] ? element[fieldKey].title  : ''}}</span>
                                </el-col>
                            </el-row>

                        </div>

                    </div>
                    <div style="clear: left;"></div>
                </draggable>

                <template v-if="field.additional_fields">
                    <AddImageComponent
                        :item_field="field.additional_fields"
                        v-if="showModal"
                        :formData="formData"
                        :updateImageData="updateImageData"
                        :lang="lang"
                        :upload_image_route="uploadImageRoute"
                        :locales="locales"
                        :edit_data="edit_data"
                        :default_locale="defaultLocale">
                    </AddImageComponent>
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
    import AddImageComponent from "./AddImageComponent";
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
    import MyUploadAdapter from '../../../mixins/EditorCustomUpload';

    export default {
        components: {AddImageComponent, draggable},
        props: [
            'field',
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
                activeTabName: 'en',
                dialogVisible: false,
                loading: false,
                form: {
                    images: []
                },
                editor: ClassicEditor,
                showModal: false,

                dialogImageUrl: '',
                disabled: false,
                fileList: [],
                edit_data: {}
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
            handleRemove(file) {
                this.fileList = this.fileList.filter(function(item){
                    return item.uid != file.uid;
                });
            },
            handlePictureCardPreview(file) {
                this.dialogImageUrl = file.url;
                this.dialogVisible = true;
            },

            async handleChange(file, fileList) {
                let fileItem = file.raw;

                var data = new FormData();
                data.append('file', fileItem);
                data.append('type', 'gallery_image');

                await getData({
                    method: 'POST',
                    url: this.uploadImageRoute,
                    data: data
                }).then(response => {
                    // Parse response notification.
                    responseParse(response);
                    if (response.status == 200) {
                        // Response data.
                        let data = response.data;

                        this.updateImageData({
                            image_id:   data.item.id,
                            url:        data.item.full_src
                        });
                    }
                    this.loading = false
                });

                this.fileList = [];
            },

            showAdd() {
                this.edit_data = undefined;
                this.forceRerender(true);
            },

            showEditImage(index) {
                this.edit_data = '';
                this.form.images[index].index = index;
                this.edit_data = Object.assign({}, JSON.parse(JSON.stringify(this.form.images[index])));
                this.forceRerender(true);
            },

            removeImage(index) {
                this.$confirm(this.lang.remove_image_confirm, {
                    confirmButtonText: this.lang.remove_image_confirm_yes,
                    cancelButtonText: this.lang.remove_image_confirm_no,
                    type: 'warning'
                })
                    .then(async () => {
                        this.form.images = this.form.images.filter((item, i) => {
                            return i != index;
                        });
                    });
            },

            /**
             *
             * @param data
             */
            updateImageData(data = undefined, index = '') {

                if (data && !data.inputs) {
                    data.inputs = this.field.inputs;
                }

                if (data && index !== '') {
                    this.form.images[index] = data;
                } else if (data) {
                    data.additional_fields = this.form.additional_fields
                    this.form.images.push(data);
                } else {
                    let oldData = this.form;
                    this.form = {};
                    this.form = oldData;
                }

                this.updateData(this.moduleName, this.fieldKey,this.form);
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
