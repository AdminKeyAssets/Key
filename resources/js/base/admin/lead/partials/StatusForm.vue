<template>
    <div>
        <a @click="openModal" style="cursor: pointer">{{ localStatus }}</a>

        <el-dialog
            title="Change Status"
            :visible.sync="showModal"
            width="30%"
            :before-close="handleClose"
        >
            <el-form>
                <el-form-item label="Select new status:">
                    <el-select v-model="form.status" placeholder="Select Status" v-remove-readonly>
                        <el-option
                            v-for="status in statuses"
                            :key="status"
                            :label="status"
                            :value="status"
                        ></el-option>
                    </el-select>
                </el-form-item>
            </el-form>

            <span slot="footer" class="dialog-footer">
                <el-button @click="showModal = false">Cancel</el-button>
                <el-button type="primary" @click="changeStatus">Change</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
import { responseParse } from "../../../mixins/responseParse";
import { getData } from "../../../mixins/getData";

export default {
    props: {
        status: {
            type: String,
            required: true
        },
        leadId: {
            type: [String, Number],
            required: true
        }
    },
    data() {
        return {
            form: {
                status: this.status
            },
            showModal: false,
            statuses: {
                'New': 'New',
                'Not Responding': 'Not Responding',
                'Communication': 'Communication',
                'Proposal Sent': 'Proposal Sent',
                'Refused': 'Refused',
                'Signed': 'Signed',
                'Archieve': 'Archieve',
            },
            localStatus: this.status
        };
    },
    watch: {
        status(newVal) {
            this.localStatus = newVal;
        }
    },
    methods: {
        openModal() {
            this.showModal = true;
        },
        handleClose() {
            this.showModal = false;
        },

        async changeStatus() {
            let url = `/lead/status-update/${this.leadId}`;
            await getData({
                method: 'POST',
                config: {
                    headers: { 'content-type': 'multipart/form-data' }
                },
                url: url,
                data: {
                    status: this.form.status,
                }
            }).then(response => {
                responseParse(response, true);

                if (response.status === 200) {
                    let data = response.data.data;
                    if (data.status) {
                        this.localStatus = data.status;
                        this.handleClose();
                    }
                }
            });
        }
    }
};
</script>
