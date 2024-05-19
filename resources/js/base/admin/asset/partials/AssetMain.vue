<template>
    <div>
        <div class="block">
            <el-form v-loading="loading"
                     element-loading-text="Loading..."
                     element-loading-spinner="el-icon-loading"
                     element-loading-background="rgba(0, 0, 0, 0.0)"
                     ref="form" :model="form" class="form-horizontal form-bordered">

                <el-row>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Name:</label>
                        <div class="col-md-10 uppercase-medium">
                            <input class="form-control" :disabled="loading" v-model="form.name"></input>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">City:</label>
                        <div class="col-md-10 uppercase-medium">
                            <input class="form-control" :disabled="loading" v-model="form.city"></input>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Address:</label>
                        <div class="col-md-10 uppercase-medium">
                            <input class="form-control" :disabled="loading" v-model="form.address"></input>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Delivery Date:</label>
                        <div class="col-md-10 uppercase-medium">
                            <el-date-picker
                                v-model="form.delivery_date"
                                type="date"
                                placeholder="Pick a delivery date">
                            </el-date-picker>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Area (m2):</label>
                        <div class="col-md-10 uppercase-medium">
                            <input class="form-control" :disabled="loading" v-model="form.area"></input>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Price:</label>
                        <div class="col-md-10 uppercase-medium">
                            <input class="form-control" :disabled="loading" v-model="form.total_price"></input>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Cadastral Number:</label>
                        <div class="col-md-10 uppercase-medium">
                            <input class="form-control" :disabled="loading" v-model="form.cadastral_number"></input>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Select Investor:</label>
                        <div class="col-md-10 uppercase-medium">
                            <el-select v-model="form.investor_id" :value="form.investor_id" filterable placeholder="Select">
                                <el-option
                                    v-for="item in investors"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id">
                                </el-option>
                            </el-select>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Attachments:</label>
                        <div class="col-md-10 uppercase-medium">
                            <input type="file" @change="onFileChange" multiple>
                            <div v-if="form.attachments">
                                <ul>
                                    <li v-for="(file, index) in form.attachments" :key="index">
                                        <img v-if="file.preview" :src="file.preview" alt="preview" style="max-width: 100px;"/>
                                        <a v-else :href="file.url" target="_blank">{{ file.name }}</a>
                                        <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                                   @click="removeAttachment(index)"></el-button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Extra Details:</label>
                        <div class="col-md-10 uppercase-medium">

                            <el-form-item
                                v-for="(extraDetail) in form.extraDetails"
                                :key="extraDetail.id">
                                <div class="col-md-5 uppercase-medium">
                                    <el-input class="col-md-5" v-model="extraDetail.key" placeholder="Name for extra detail"></el-input>
                                </div>
                                <div class="col-md-5 uppercase-medium">
                                    <el-input class="col-md-5" v-model="extraDetail.value" placeholder="Value for extra detail"></el-input>
                                </div>
                                <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                           @click.prevent="removeDetail(extraDetail)"></el-button>
                            </el-form-item>
                            <el-button type="primary" size="medium" icon="el-icon-plus" @click="addDetail">Add Extra Details</el-button>
                        </div>
                    </div>

                </el-row>
            </el-form>
        </div>
    </div>
</template>


<script>
import {responseParse} from '../../../mixins/responseParse'
import {getData} from '../../../mixins/getData'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {
    props: [
        'routes',
        'updateData',
        'item',
        'investors'
    ],
    data() {
        return {
            form: {
                extraDetails: [],
                attachments: []
            },
            loading: false,
            editor: ClassicEditor,
            addDetailIsBtnDisabled: true,
            fileList: [],
        }
    },
    updated() {
        this.updateData(this.form);
    },
    watch: {
        'item'() {
            if (this.item) {
                this.form = this.item;
            }
        }
    },
    methods: {
        removeDetail(item) {
            var index = this.form.extraDetails.indexOf(item);
            if (index !== 0 && index !== -1) {
                this.form.extraDetails.splice(index, 1);
            }
        },
        addDetail() {
                this.form.extraDetails.push({
                    id: Date.now(),
                    key: '',
                    value: ''
                });
        },

        onFileChange(e) {
            const files = e.target.files;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.attachments.push({
                        file: file,
                        preview: file.type.startsWith('image/') ? e.target.result : null,
                        name: file.name,
                    });
                };
                reader.readAsDataURL(file);
            }
        },
        removeAttachment(index) {
            this.form.attachments.splice(index, 1);
        },
    }
}

</script>
