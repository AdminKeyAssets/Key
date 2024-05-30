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
                                    <label class="col-md-1 control-label">Payment #:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{form.month}}
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Payment Date:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{form.payment_date}}
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Amount:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{this.formatPrice(form.amount)}} {{form.currency}}
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Status:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <el-switch
                                            disabled
                                            v-model="form.status"
                                            active-color="#13ce66"
                                            inactive-color="#ff4949">
                                        </el-switch>
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">File:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <p v-if="form.attachment"><a :href="form.attachment" target="_blank">View
                                            Attachment</a>
                                        </p>
                                    </div>
                                </div>

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
import PaymentMain from "../partials/PaymentMain.vue";

export default {
    components: {PaymentMain},
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
                    }
                    this.form.status = data.item.status ? true : false
                    this.form.id = this.id;
                }
                this.loading = false
            })
        },
        formatPrice(amount) {
            if (amount !== undefined && amount !== '') {
                // const value = parseFloat(amount.replace(/,/g, ''));
                if (!isNaN(amount)) {
                    return new Intl.NumberFormat('en-US', {
                        style: 'decimal',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }).format(amount);
                }
            }
            return '0.00';
        }
    }
}

</script>
