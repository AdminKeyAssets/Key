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

                                <div class="form-group">
                                    <label class="col-md-2 control-label">Project: </label>
                                    <div class="col-md-6 uppercase-medium">
                                        {{ form.project }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label">Investor: </label>
                                    <div class="col-md-6 uppercase-medium">
                                        {{ form.investor }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label">Asset Type: </label>
                                    <div class="col-md-6 uppercase-medium">
                                        {{ form.type }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label">Area (m2):</label>
                                    <div class="col-md-6 uppercase-medium">
                                        {{ form.size }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label">M2 Price:</label>
                                    <div class="col-md-6 uppercase-medium">
                                        {{ form.price }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label">Total Price:</label>
                                    <div class="col-md-6 uppercase-medium">
                                        {{ form.total_price }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label">Agreement Date:</label>
                                    <div class="col-md-6 uppercase-medium">
                                        {{ form.agreement_date }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label">Agreement Status:</label>
                                    <div class="col-md-6 uppercase-medium">
                                        {{ form.agreement_status }}
                                    </div>
                                </div>

                                <div class="form-group dashed" v-if="form.marketing_channel">
                                    <label class="col-md-2 control-label">Marketing Channel:</label>
                                    <div class="col-md-6 uppercase-medium">
                                        {{ form.marketing_channel }}
                                    </div>
                                </div>

                                <template v-if="form.agreement_status === 'Installments'">
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Down Payment:</label>
                                        <div class="col-md-6 uppercase-medium">
                                            {{ form.down_payment }}
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Period:</label>
                                        <div class="col-md-6 uppercase-medium">
                                            {{ form.period }}
                                        </div>
                                    </div>
                                </template>

                                <template v-if="this.canSeeComplete">
                                    <div class="form-group" v-if="form.commission">
                                        <label class="col-md-2 control-label">Commission:</label>
                                        <div class="col-md-6 uppercase-medium">
                                            {{ form.commission }}
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Is Completed: </label>
                                        <div class="col-md-6">
                                            <el-switch v-model="form.complete" disabled="">
                                            </el-switch>
                                        </div>
                                    </div>

                                    <div class="form-group dashed" v-if="form.attachment">
                                        <label class="col-md-2 control-label">Attachment:</label>
                                        <div class="col-md-6 uppercase-medium">
                                            <div>
                                                <p v-if="form.attachment"><a :href="form.attachment" target="_blank">View
                                                    Attachment</a></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
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

export default {
    props: [
        'getSaveDataRoute',
        'id',
        'userId',
        'canSeeComplete'
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
                id: this.id,
                complete: false
            },

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

                    if (data.item) {
                        this.form = data.item;
                        this.form.complete = Boolean(data.item.complete === true || data.item.complete === "true" || data.item.complete === 1);
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
