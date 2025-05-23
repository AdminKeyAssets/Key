<template>
    <div>
        <!-- Display current asset's name -->
        <a @click="openModal" style="cursor: pointer">{{ localAssetName }}</a>

        <!-- Modal for selecting a new asset -->
        <el-dialog
            title="Change Asset"
            :visible.sync="showModal"
            width="30%"
            :before-close="handleClose"
        >
            <el-form>
                <el-form-item label="Select new asset:">
                    <el-select v-model="form.asset" placeholder="Select Asset" multiple filterable v-remove-readonly>
                        <el-option
                            v-for="asset in assets"
                            :key="asset"
                            :label="asset"
                            :value="asset"
                        ></el-option>
                    </el-select>
                </el-form-item>
            </el-form>

            <span slot="footer" class="dialog-footer">
                <el-button @click="showModal = false">Cancel</el-button>
                <el-button type="primary" @click="changeAsset">Change</el-button>
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
            type: Array,
            default: () => []
        },
        developerId: {
            type: [String, Number],
            required: true
        }
    },
    data() {
        return {
            form: {
                asset: [...this.assetName]
            },
            showModal: false,
            assets: [],
            localAssetName: this.assetName.length ? this.assetName.join(", ") : 'Assign Asset',
        };
    },
    watch: {
        // Watch the assetName prop for changes and update the local copy
        assetName(newVal) {
            this.localAssetName = newVal.join(", ");
            this.form.asset = [...newVal];        }
    },
    methods: {
        openModal() {
            this.form.asset = [...this.assetName];
            this.showModal = true;
            this.fetchAssets();
        },
        handleClose() {
            this.showModal = false;
        },
        fetchAssets() {
            axios.get('/admin/developers/filter-options')
                .then(response => {
                    responseParse(response, false);
                    if (response.status === 200) {
                        let data = response.data.data;

                        if (data.assets) {
                            this.assets = data.assets;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching assets:', error);
                });
        },
        async changeAsset() {
            await getData({
                method: 'POST',
                config: {
                    headers: { 'content-type': 'multipart/form-data' }
                },
                url: '/admin/developers/update-asset',
                data: {
                    assets: this.form.asset,
                    developer_id: this.developerId
                }
            }).then(response => {
                responseParse(response, true);

                if (response.status === 200) {
                    let data = response.data.data;
                    data.assets.length ? this.localAssetName = data.assets.join(", ") : 'Assign Asset';
                    this.handleClose();
                }
            });
        }
    }
};
</script>
