<template>
    <div>
        <div class="col-xs-12">
            <div class="registration-btn project-title-buttons">
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
                                        {{ form.name }} {{ form.surname }}
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Email:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.email }}
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Phone:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.prefix }}{{ form.phone }}
                                    </div>
                                </div>
                                <div class="form-group dashed" v-if="form.status">
                                    <label class="col-md-1 control-label">Status:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.status }}
                                    </div>
                                </div>
                                <div v-if="form.admin_id" class="form-group dashed">
                                    <label class="col-md-1 control-label">Sales Manager: </label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ selectedManager.name }} {{ selectedManager.surname }}
                                    </div>
                                </div>
                                <div class="form-group dashed" v-if="form.marketing_channel">
                                    <label class="col-md-1 control-label">Marketing Channel:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{ form.marketing_channel }}
                                    </div>
                                </div>
                            </el-row>

                            <el-row style="margin-bottom: 30px">
                                <el-card class="box-card">
                                    <div slot="header" class="clearfix main-header">
                                        <span>Comments</span>
                                    </div>
                                    <LeadComments
                                        :id="this.form.id"
                                        :user-id="this.userId"
                                    ></LeadComments>
                                </el-card>
                            </el-row>
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
import AssetComments from "../../asset/partials/AssetComments.vue";
import LeadComments from "../partials/LeadComments.vue";

export default {
    components: {LeadComments},
    props: [
        'getSaveDataRoute',
        'id',
        'userId'
    ],
    data() {
        return {
            item: {},
            data: {},
            loading: false,
            routes: {},
            options: {},
            managers: {},
            /** Form data*/
            form: {
                id: this.id
            },

        }
    },
    created() {
        this.getSaveData();
    },
    computed: {
        selectedManager() {
            return this.managers.find(manager => manager.id === this.form.admin_id);
        }
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

                    if (data.item) {
                        this.form = data.item;
                    }
                    if (data.managers) {
                        this.managers = data.managers;
                    }
                    this.form.id = this.id;
                }
                this.loading = false
            })
        },

        formatPrice(amount) {
            // Do not format if undefined or empty
            if (amount !== undefined && amount !== '') {
                // Ensure amount is a valid number
                if (!isNaN(amount)) {
                    // Check if the amount has decimal places
                    if (amount % 1 === 0) {
                        // No decimal places for whole numbers
                        return new Intl.NumberFormat('en-US', {
                            style: 'decimal',
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0,
                        }).format(amount);
                    } else {
                        // Keep two decimal places for non-whole numbers
                        return new Intl.NumberFormat('en-US', {
                            style: 'decimal',
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        }).format(amount);
                    }
                }
            }
            return '0.00';
        },
    }
}

</script>
