<template>
    <div>
        <div class="col-xs-12">
            <div class="registration-btn project-title-buttons">
                <div class="project-title"></div>

                <RentalMain
                    :routes="routes"
                    :updateData="updateData"
                    :rentals="rentals"
                    :next-payment="nextPayment"
                    :item="form"
                ></RentalMain>

                <div class="project-buttons">
                    <el-button type="primary" size="medium" icon="el-icon-check"
                               @click="save"
                               :disabled="loading"
                               :width="dialogWidth"
                               style="margin: 21px 1rem">Save
                    </el-button>
                </div>

                <el-dialog
                    title="Confirm Action"
                    :visible.sync="confirmVisible"
                >
                    <span>Please complete the rent or prolong the rent's schedule.</span>
                    <span slot="footer" class="dialog-footer rental-dialog-footer">
                        <el-button @click="confirmVisible = false">Cancel</el-button>
                        <el-button type="primary" @click="confirmSave">Ok</el-button>
                    </span>
                </el-dialog>
            </div>
        </div>
    </div>
</template>

<script>
import { responseParse } from '../../../mixins/responseParse'
import { getData } from '../../../mixins/getData'
import RentalMain from "../partials/RentalMain.vue";

export default {
    components: { RentalMain },
    props: ['getSaveDataRoute', 'id'],
    data() {
        return {
            loading: false,
            routes: {},
            options: {},
            form: { id: this.id },
            rentals: {},
            nextPayment: 0,
            totalAmountToPay: 0,
            confirmVisible: false,
        }
    },
    created() {
        this.getSaveData();
    },
    methods: {
        async getSaveData() {
            this.loading = true;
            await getData({
                method: 'POST',
                config: { headers: { 'content-type': 'multipart/form-data' } },
                url: this.getSaveDataRoute,
                data: this.form
            }).then(response => {
                responseParse(response, false);
                if (response.status === 200) {
                    const data = response.data.data;
                    this.routes = data.routes;
                    this.options = data.options;
                    if (data.item) this.form = data.item;
                    if (data.rentals) this.rentals = data.rentals;
                    if (data.nextPayment) this.nextPayment = data.nextPayment;
                    if (data.totalAmountToPay) this.totalAmountToPay = data.totalAmountToPay;
                    this.form.id = this.id;
                }
                this.loading = false;
            })
        },

        save() {
            if (parseFloat(this.form.amount) === parseFloat(this.totalAmountToPay)) {
                this.confirmVisible = true;
            } else {
                this.confirmSave();
            }
        },

        async confirmSave() {
            this.confirmVisible = false;
            this.loading = true;
            const formData = new FormData();
            for (let key in this.form) {
                formData.append(key, this.form[key]);
            }

            axios.post(this.routes.save, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
                .then(response => {
                    responseParse(response);
                    const data = response.data.data;
                    setTimeout(() => {
                        window.location.href = `assets/${data.item.asset_id}/rental`;
                    }, 1000);
                })
                .catch(error => {
                    responseParse(error.response);
                    console.error(error);
                })
                .finally(() => this.loading = false);
        },

        updateData(data) {
            this.form = data;
        },
    },
    computed: {
        // Returns full width on mobile and 30% on larger screens.
        dialogWidth() {
            return window.innerWidth < 768 ? '100%' : '30%';
        }
    },
}
</script>
