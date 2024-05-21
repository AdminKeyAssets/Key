<template>
    <div>
        <div class="col-xs-12">
            <div class="registration-btn project-title-buttons">
                <div class="project-title">

                </div>
                <div class="project-buttons">
                    <el-link
                        type="success"
                        size="medium"
                        style="border: 1px solid; padding: 7px 15px; border-radius: 5px"
                        icon="el-icon-money"
                        v-if="this.form.id"
                        :href="'/assets/' + this.form.id + '/payments'"
                    >Payments
                    </el-link>
                </div>
                <div>
                    <div class="block col-md-9">
                        <el-form v-loading="loading"
                                 element-loading-text="Loading..."
                                 element-loading-spinner="el-icon-loading"
                                 element-loading-background="rgba(0, 0, 0, 0.0)"
                                 class="form-horizontal form-bordered">

                            <el-row>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Icon:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <img :src="form.icon" width="100px">
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Name:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.name }}
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">City:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.city }}
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Address:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.address }}
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Delivery Date:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <el-date-picker
                                            v-model="form.delivery_date"
                                            format="yyyy/MM/dd"
                                            type="date"
                                            readonly>
                                        </el-date-picker>
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Area:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.area }}
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Price:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.price }}
                                    </div>
                                </div>


                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Cadastral Number:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.cadastral_number }}
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Investor:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <el-select v-model="form.investor_id" :value="form.investor_id" filterable
                                                   placeholder="Select" disabled>
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
                                    <label class="col-md-1 control-label">Attached Media:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <li style="display: inline-block; margin-right: 10px"
                                            v-for="(file, index) in form.attachments" :key="index">
                                            <img v-if="file.preview" :src="file.preview" alt="preview"
                                                 style="max-width: 200px;"/>
                                            <img v-else-if="file.type === 'image'" :src="file.path" alt="preview"
                                                 style="max-width: 200px;"/>
                                            <a v-else :href="file.path" target="_blank">{{ file.name }}</a>
                                        </li>
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Extra Details:</label>
                                    <div class="col-md-10 uppercase-medium">

                                        <el-form-item
                                            v-for="(extraDetail) in form.extraDetails"
                                            :key="extraDetail.id">
                                            <div class="col-md-3 uppercase-medium">
                                                {{ extraDetail.key }}:
                                            </div>
                                            <div class="col-md-5 uppercase-medium">
                                                {{ extraDetail.value }}
                                            </div>
                                        </el-form-item>
                                    </div>
                                </div>

                            </el-row>
                        </el-form>
                    </div>
                    <div class="block col-md-3">
                        <el-form v-loading="loading"
                                 element-loading-text="Loading..."
                                 element-loading-spinner="el-icon-loading"
                                 element-loading-background="rgba(0, 0, 0, 0.0)"
                                 class="form-horizontal form-bordered">
                            <el-card class="box-card">
                                <div slot="header" class="clearfix">
                                    <span>Sales Manager</span>
                                </div>
                                <div class="text item" v-if="salesManager">
                                    <p v-if="salesManager.name">
                                        Name: {{this.salesManager.name}}
                                    </p>
                                    <p v-if="salesManager.phone">
                                        Phone: {{this.salesManager.phone}}
                                    </p>
                                    <p v-if="salesManager.email">
                                        Email: {{this.salesManager.email}}
                                    </p>
                                </div>
                            </el-card>
                        </el-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import {responseParse} from '../../../mixins/responseParse'
import {getData} from '../../../mixins/getData'
import AssetMain from "../partials/AssetMain.vue";

export default {
    components: {AssetMain},
    props: [
        'getSaveDataRoute',
        'id'
    ],
    data() {
        return {
            item: {},
            data: {},
            loading: false,
            routes: {},
            options: {},
            /** Form data*/
            form: {
                id: this.id
            },
            investors: {},
            salesManager: {}

        }
    },
    created() {
        this.getSaveData();
    },
    methods: {
        /**
         *
         * Get save data.
         *
         * @returns {Promise<void>}
         */
        async getSaveData() {
            this.loading = true;

            await getData({
                method: 'POST',
                url: this.getSaveDataRoute,
                data: this.form
            }).then(response => {
                // Parse response notification.
                responseParse(response, false);

                if (response.status == 200) {
                    // Response data.
                    let data = response.data.data;

                    this.routes = data.routes;
                    this.options = data.options;
                    this.investors = data.investors;
                    this.salesManager = data.salesManager;
                    if (data.item) {
                        this.form = data.item;
                        if (data.item.files) {
                            this.form.attachments = data.item.files;
                        }
                    }

                    this.form.id = this.id;
                }
                this.loading = false
            })
        },

    }
}

</script>
