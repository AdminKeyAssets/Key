<template>
    <div>
        <div class="col-xs-12">
            <div class="registration-btn project-title-buttons">

                <div class="project-title">

                </div>

                <RentalMain
                    :routes="routes"
                    :updateData="updateData"
                    :rentals="this.rentals"
                    :item="this.form && this.form ? this.form : undefined"
                ></RentalMain>

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
import RentalMain from "../partials/RentalMain.vue";

export default {
    components: {RentalMain},
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
            rentals: {}

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

                    if (data.item) {
                        this.form = data.item;
                    }
                    if(data.rentals){
                        this.rentals = data.rentals;
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
                formData.append(key, this.form[key]);
            }

            axios.post(this.routes.save, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
                .then(response => {
                    responseParse(response);
                    const data = response.data.data;
                    setTimeout(() => {
                        window.location.href = `assets/${data.item.asset_id}/rental`;
                        // window.location.reload();
                    }, 1000);

                    this.loading = false;
                })
                .catch(error => {
                    this.loading = false;
                    // Handle error
                    responseParse(error.response);
                    console.error(error);
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
