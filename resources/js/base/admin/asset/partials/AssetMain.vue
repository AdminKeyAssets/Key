<template>
    <div>
        <div class="block">
            <el-form v-loading="loading"
                     element-loading-text="Loading..."
                     element-loading-spinner="el-icon-loading"
                     element-loading-background="rgba(0, 0, 0, 0.0)"
                     ref="form" :model="form" class="form-horizontal form-bordered">

                <el-row style="margin-left: 15px">

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Name:</label>
                        <div class="col-md-10 uppercase-medium">
                            <input class="form-control" :disabled="loading" v-model="form.name"></input>
                        </div>
                    </div>


                    <el-collapse v-model="activeNames">
                        <el-collapse-item title="Project Details" name="1">
                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Upload Icon:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input type="file" @change="onIconChange" accept="image/*">
                                    <div v-if="form.icon">
                                        <img v-if="form.iconPreview" :src="form.iconPreview" alt="Icon Preview"
                                             style="max-width: 100px;"/>
                                        <img v-else :src="form.icon" alt="Icon Preview" style="max-width: 100px;"/>
                                        <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                                   @click="removeIcon"></el-button>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Project Name:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input class="form-control" :disabled="loading" v-model="form.project_name"></input>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Project Description:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <el-input
                                        type="textarea"
                                        autosize
                                        placeholder="Project Description"
                                        :disabled="loading"
                                        v-model="form.description">
                                    </el-input>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Project Link:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input class="form-control" :disabled="loading" v-model="form.project_link"></input>
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
                                        format="yyyy/MM/dd"
                                        type="date"
                                        value-format="yyyy/MM/dd"
                                        placeholder="Pick a delivery date">
                                    </el-date-picker>
                                </div>
                            </div>
                        </el-collapse-item>

                        <el-collapse-item title="Asset Details" name="2">

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Type:</label>
                                <div class="col-md-3 uppercase-medium">
                                    <el-select v-model="form.type"
                                               :value="form.type"
                                               filterable
                                               placeholder="Select Type">
                                        <el-option
                                            v-for="type in types"
                                            :key="type"
                                            :label="type"
                                            :value="type">
                                        </el-option>
                                    </el-select>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Floor:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input class="form-control" :disabled="loading" v-model="form.floor"></input>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Flat number:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input class="form-control" :disabled="loading" v-model="form.flat_number"></input>
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
                                <div class="col-md-7 uppercase-medium">
                                    <input class="form-control" :disabled="loading" v-model="form.price"></input>
                                </div>
                                <div class="col-md-3 uppercase-medium">
                                    <el-select v-model="form.currency"
                                               :value="form.currency"
                                               filterable
                                               placeholder="Select">
                                        <el-option
                                            v-for="(currency, index) in currencies"
                                            :key="index"
                                            :label="currency"
                                            :value="index">
                                        </el-option>
                                    </el-select>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Total Price:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input class="form-control" :disabled="loading" v-model="form.total_price"></input>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Delivery Condition:</label>
                                <div class="col-md-3 uppercase-medium">
                                    <el-select v-model="form.condition"
                                               :value="form.condition"
                                               filterable
                                               placeholder="Delivery Condition">
                                        <el-option
                                            v-for="condition in conditions"
                                            :key="condition"
                                            :label="condition"
                                            :value="condition">
                                        </el-option>
                                    </el-select>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Cadastral Number:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input class="form-control" :disabled="loading"
                                           v-model="form.cadastral_number"></input>
                                </div>
                            </div>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Select Investor:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <el-select v-model="form.investor_id" :value="form.investor_id" filterable
                                               placeholder="Select">
                                        <el-option
                                            v-for="item in investors"
                                            :key="item.id"
                                            :label="item.name"
                                            :value="item.id">
                                        </el-option>
                                    </el-select>
                                </div>
                            </div>

                        </el-collapse-item>
                        <el-collapse-item title="Extra" name="3">
                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Attachments:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <input type="file" @change="onFileChange" multiple>
                                    <div v-if="form.attachments">
                                        <ul>
                                            <li v-for="(file, index) in form.attachments" :key="index"
                                                style="display: inline-block; margin-right: 10px">
                                                <img v-if="file.preview" :src="file.preview" alt="preview"
                                                     style="max-width: 100px;"/>
                                                <img v-else-if="file.type === 'image'" :src="file.path" alt="preview"
                                                     style="max-width: 100px;"/>
                                                <a v-else :href="file.path" target="_blank">{{ file.name }}</a>
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
                                            <el-input class="col-md-5" v-model="extraDetail.key"
                                                      placeholder="Name for extra detail"></el-input>
                                        </div>
                                        <div class="col-md-5 uppercase-medium">
                                            <el-input class="col-md-5" v-model="extraDetail.value"
                                                      placeholder="Value for extra detail"></el-input>
                                        </div>
                                        <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                                   @click.prevent="removeDetail(extraDetail)"></el-button>
                                    </el-form-item>
                                    <el-button type="primary" size="medium" icon="el-icon-plus" @click="addDetail">Add
                                        Extra
                                        Details
                                    </el-button>
                                </div>
                            </div>
                        </el-collapse-item>
                        <el-collapse-item title="Payments" name="4">
                        </el-collapse-item>
                    </el-collapse>
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
                attachments: [],
                existingAttachments: [],
                attachmentsToRemove: [],
                icon: null,
                iconPreview: null
            },
            loading: false,
            editor: ClassicEditor,
            addDetailIsBtnDisabled: true,
            fileList: [],
            currencies: {
                "USD": "USD",
                "GEL": "GEL",
            },
            types: {
                "Flat": "Flat",
                "Land": "Land",
                "Office": "Office",
                "Commercial Space": "Commercial Space",
                "Villa": "Villa"
            },
            conditions: {
                "Black Frame": "Black Frame",
                "White Frame": "White Frame",
                "Green Frame": "Green Frame",
                "Renovated": "Renovated"
            },
            agreementStatuses: {
                "Complete": "Complete",
                "Installments": "Installments"
            },
            activeNames: ['1'],
        }
    },
    updated() {
        this.updateData(this.form);
    },
    watch: {
        'item'() {
            if (this.item) {
                this.form = this.item;
                this.form.existingAttachments = this.item.existingAttachments || [];
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
            if (this.form.existingAttachments.length) {
                this.fileList.push(this.form.existingAttachments[index].id);
                this.form.attachmentsToRemove = this.fileList;
                this.form.existingAttachments.splice(index, 1);
            }
        },

        onIconChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.icon = file;
                    this.form.iconPreview = e.target.result;
                };
                reader.onerror = (error) => {
                    console.error('Error loading file:', error);
                };
                reader.readAsDataURL(file);
            }
        },
        removeIcon() {
            this.form.icon = null;
            this.form.iconPreview = null;
        },
    }
}

</script>
