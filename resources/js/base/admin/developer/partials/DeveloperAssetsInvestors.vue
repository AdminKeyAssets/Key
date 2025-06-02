<template>
    <div>
        <!-- Display current asset's name -->
        <a @click="openModal" style="cursor: pointer">{{ localAssetName }}</a>

        <!-- Modal for selecting a new asset -->
        <el-dialog
            title="Investor(s) List"
            :visible.sync="showModal"
            width="30%"
            :before-close="handleClose"
        >

            <div class="investors-wrapper">
                <div class="investor-item" v-for="(investor, idx) in investors">
                    <span>
                        <i class="el-icon-user"> {{ investor }}</i>
                    </span>
                </div>
            </div>


            <span slot="footer" class="dialog-footer">
                <el-button @click="showModal = false">Close</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
import { responseParse } from "../../../mixins/responseParse";
import { getData } from "../../../mixins/getData";

export default {
    props: {
        assetName: {
            type: [String],
            required: true
        },
        developerId: {
            type: [String, Number],
            required: true
        }
    },
    data() {
        return {
            form: {
                asset: this.assetName
            },
            showModal: false,
            investors: [],
            localAssetName: this.assetName,
        };
    },

    methods: {
        openModal() {
            this.showModal = true;
            this.fetchInvestors();
        },
        handleClose() {
            this.showModal = false;
        },

        async fetchInvestors() {
            await getData({
                method: 'POST',
                config: {
                    headers: { 'content-type': 'multipart/form-data' }
                },
                url: '/admin/developers/asset-investors',
                data: {
                    asset: this.form.asset,
                    developer_id: this.developerId
                }
            }).then(response => {
                responseParse(response, false);

                if (response.status === 200) {
                    let data = response.data.data;
                    if (data.investors) {
                        this.investors = data.investors;
                    }
                }
            });
        }
    }
};
</script>
