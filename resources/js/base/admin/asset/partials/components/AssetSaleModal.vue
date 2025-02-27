<template>
    <el-tooltip content="Sale" placement="top" effect="light">
        <div style="display: inline-block">
            <el-button type="primary" @click="openModal" class="list-delete-button" size="small">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none"
                     stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 12l5-5h6l5 5"/>
                    <path d="M4 12v6h6l4-4"/>
                    <rect x="12" y="8" width="10" height="6" rx="1" ry="1"/>
                </svg>
            </el-button>

            <el-dialog
                :visible.sync="visible"
                title="Sell Asset"
                width="500px"
                @close="resetForm"
            >
                <el-form ref="saleForm" :model="form" :rules="rules" label-width="120px">

                    <el-form-item label="Sale Date" prop="sale_date">
                        <el-date-picker
                            v-model="form.sale_date"
                            format="yyyy/MM/dd"
                            value-format="yyyy/MM/dd"
                            type="date"
                            placeholder="Pick a sale date">
                        </el-date-picker>
                    </el-form-item>

                    <el-form-item label="Sale Price" prop="sale_price">
                        <el-input v-model="form.sale_price" type="number" placeholder="Enter sale price"></el-input>
                    </el-form-item>

                    <el-form-item label="Purchaser" prop="purchaser">
                        <el-input v-model="form.purchaser" placeholder="Enter purchaser name"></el-input>
                    </el-form-item>

                    <div class="form-group dashed">
                        <label class="col-md-4 control-label">Attachment:</label>
                        <div class="col-md-7 uppercase-medium">
                            <p v-if="form.sale_agreement">File: <a :href="form.sale_agreement" target="_blank">View
                                Attachment</a>
                                <el-button type="danger" icon="el-icon-delete" size="small"
                                           @click="removeFile"
                                ></el-button>
                            </p>
                            <input v-else type="file" class="form-control" @change="onFileChange">
                        </div>
                    </div>
                </el-form>

                <span slot="footer" class="dialog-footer">
                    <el-button @click="visible = false">Cancel</el-button>
                    <el-button type="primary" :disabled="loading" @click="save">Save</el-button>
                </span>
            </el-dialog>
        </div>
    </el-tooltip>
</template>

<script>
import axios from 'axios';
import { responseParse } from '../../../../mixins/responseParse';

export default {
    props: {
        assetId: Number,
    },
    data() {
        return {
            visible: false,
            loading: false,
            form: {
                sale_date: '',
                sale_price: '',
                purchaser: '',
                sale_agreement: null
            },
            rules: {
                sale_date: [{ required: true, message: 'Please select a sale date', trigger: 'change' }],
                sale_price: [{ required: true, message: 'Please enter sale price', trigger: 'blur' }],
                purchaser: [{ required: true, message: 'Please enter purchaser name', trigger: 'blur' }]
            }
        };
    },
    methods: {
        openModal() {
            this.visible = true;
        },
        resetForm() {
            this.form = {
                sale_date: '',
                sale_price: '',
                purchaser: '',
                sale_agreement: null
            };
        },
        onFileChange(e) {
            this.form.sale_agreement = e.target.files[0];
        },
        removeFile() {
            this.form.sale_agreement = null;
        },
        async save() {
            this.$refs.saleForm.validate(async (valid) => {
                if (!valid) return;

                this.loading = true;

                let formData = new FormData();
                for (let key in this.form) {
                    formData.append(key, this.form[key]);
                }
                formData.append('asset_id', this.assetId);

                try {
                    const response = await axios.post(`/assets/sell/${this.assetId}`, formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                    responseParse(response);

                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);

                } catch (error) {
                    responseParse(error.response);
                    console.error(error);
                } finally {
                    this.loading = false;
                }
            });
        }
    }
};
</script>
