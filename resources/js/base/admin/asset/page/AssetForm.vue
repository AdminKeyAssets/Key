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
                    >Payments</el-link>

                    <el-link
                        type="success"
                        size="medium"
                        style="border: 1px solid; padding: 7px 15px; border-radius: 5px"
                        icon="el-icon-home"
                        v-if="this.form.id"
                        :href="'/assets/' + this.form.id + '/rental'"
                    >Rentals
                    </el-link>
                </div>

                <AssetMain
                    :routes="routes"
                    :updateData="updateData"
                    :investors="investors"
                    :item="this.form && this.form ? this.form : undefined"
                ></AssetMain>

                <div class="project-buttons">
                    <el-button type="primary" size="medium" icon="el-icon-check"
                               @click="save"
                               :disabled="loading"
                               style="margin: 21px 1rem">Save
                    </el-button>
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
            investors: {}

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
                config: {
                    headers: { 'content-type': 'multipart/form-data' }
                },
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

                    if (data.item) {
                        this.form = data.item;
                        if(data.item.files){
                            const attachments = data.item.attachments;
                            this.form.attachments = data.item.files;
                            this.form.existingAttachments = attachments || [];
                        }
                    }
                    this.form.id = this.id;
                }
                this.loading = false
            })
        },

        async save() {
            this.loading = true;

            let formData = new FormData();
            for (let key in this.form) {
                if (key === 'attachments') {
                    // Append each attachment to the formData
                    this.form.attachments.forEach(fileObj => {
                        formData.append('attachments[]', fileObj.file);
                    });
                }
                else if (key === 'attachmentsToRemove') {
                    formData.append(key, JSON.stringify(this.form.attachmentsToRemove));
                }
                else if (key === 'icon' && this.form[key]) {
                    formData.append(key, this.form[key]);
                }
                else if (key === 'extraDetails') {
                    formData.append(key, JSON.stringify(this.form[key]));
                } else {
                    formData.append(key, this.form[key]);
                }
            }

           await axios.post(this.routes.save, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
               this.loading = false;
               responseParse(response);
                    setTimeout(() => {
                        window.location.href ='/assets/list';
                    }, 1000);
                })
                .catch(error => {
                    this.loading = false;
                    responseParse(error.response);
                });
        },

        /**
         *
         * @param data
         */
        updateData(data) {
            this.form = data;
        },
    }
}

</script>
