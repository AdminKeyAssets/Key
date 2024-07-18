<template>
    <div>
        <div class="col-xs-12">
            <div class="registration-btn project-title-buttons">
                <AssetMain
                    :routes="routes"
                    :updateData="updateData"
                    :investors="investors"
                    :item="this.form && this.form ? this.form : null"
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
                id: this.id ?? null
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
                    headers: {'content-type': 'multipart/form-data'}
                },
                url: this.getSaveDataRoute,
                data: this.form
            }).then(response => {
                // Parse response notification.
                responseParse(response, false);

                if (response.status === 200) {
                    // Response data.
                    let data = response.data.data;

                    this.routes = data.routes;
                    this.options = data.options;
                    this.investors = data.investors;

                    if (data.item) {
                        this.form = data.item;
                        if (data.item.files) {
                            const attachments = data.item.attachments;
                            this.form.attachments = data.item.files;
                            this.form.existingAttachments = attachments || [];
                        }
                        if (data.item.tenant) {
                            this.form.tenant = data.item.tenant;
                        }
                        if (data.item.rentals) {
                            this.form.rentals = data.item.rentals;
                        }
                    }
                    this.form.id = this.id ?? '';
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
                } else if (key === 'attachmentsToRemove') {
                    formData.append(key, JSON.stringify(this.form.attachmentsToRemove));
                } else if (key === 'icon' && this.form[key]) {
                    formData.append(key, this.form[key]);
                } else if (key === 'floor_plan' && this.form[key]) {
                    formData.append(key, this.form[key]);
                } else if (key === 'ownership_certificate' && this.form[key]) {
                    formData.append(key, this.form[key]);
                } else if (key === 'extraDetails') {
                    this.form.extraDetails.forEach((detail, index) => {
                        formData.append(`extraDetails[${index}][key]`, detail.key);
                        formData.append(`extraDetails[${index}][value]`, detail.value);
                        if (detail.attachment) {
                            if (detail.attachment.file) {
                                formData.append(`extraDetails[${index}][attachment]`, detail.attachment.file);
                            } else {
                                formData.append(`extraDetails[${index}][attachment]`, detail.attachment);
                            }
                        }
                    });
                } else if (key === 'agreements') {
                    this.form.agreements.forEach((agreement, index) => {
                        formData.append(`agreements[${index}][name]`, agreement.name);
                        if (agreement.attachment) {
                            if (agreement.attachment.file) {
                                formData.append(`agreements[${index}][attachment]`, agreement.attachment.file);
                            } else {
                                formData.append(`agreements[${index}][attachment]`, agreement.attachment);
                            }
                        }
                    });
                } else if (key === 'payments') {
                    formData.append(key, JSON.stringify(this.form[key]));
                } else if (key === 'rentals') {
                    formData.append(key, JSON.stringify(this.form[key]));
                } else if (key === 'tenant') {
                    for (let tenantKey in this.form.tenant) {
                        if (tenantKey === 'passport' && this.form.tenant[tenantKey] && this.form.tenant[tenantKey].file) {
                            formData.append('tenant[passport]', this.form.tenant[tenantKey].file);
                        } else {
                            formData.append(`tenant[${tenantKey}]`, this.form.tenant[tenantKey]);
                        }
                    }
                    // formData.append(key, JSON.stringify(this.form[key]));
                } else if (key === 'location') {
                    formData.append('location[lat]', this.form[key]['lat']);
                    formData.append('location[lng]', this.form[key]['lng']);
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
                    window.location.href = '/assets/list';
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
         * @param key
         */
        updateData(data, key = null) {
            if (key) {
                this.$set(this.form, key, data);
            } else {
                this.form = data;
            }
        },
    }
}

</script>
