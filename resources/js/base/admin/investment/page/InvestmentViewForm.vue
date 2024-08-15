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
                                    <label class="col-md-1 control-label">Price:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{this.formatPrice(form.amount)}} {{form.currency}}
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Date From:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{form.date}}
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Status:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        {{form.status}}
                                    </div>
                                </div>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">Attachment:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <a target="_blank" :href="form.attachment">
                                            {{form.attachment}}
                                        </a>
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

export default {
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
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0,
                    }).format(amount);
                }
            }
            return '0.00';
        }
    }
}

</script>
