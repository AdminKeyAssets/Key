<template>
    <div>
        <!-- Display current manager's name -->
        <a @click="openModal">{{ localManagerName }}</a>

        <!-- Modal for selecting a new manager -->
        <el-dialog
            title="Change Manager"
            :visible.sync="showModal"
            width="30%"
            :before-close="handleClose"
        >
            <el-form>
                <el-form-item label="Select new manager:">
                    <el-select v-model="form.manager" placeholder="Select Manager">
                        <el-option
                            v-for="manager in managers"
                            :key="manager.id"
                            :label="`${manager.name} ${manager.surname}`"
                            :value="manager.id"
                        ></el-option>
                    </el-select>
                </el-form-item>
            </el-form>

            <span slot="footer" class="dialog-footer">
                <el-button @click="showModal = false">Cancel</el-button>
                <el-button type="primary" @click="changeManager">Change</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
import { responseParse } from "../../../mixins/responseParse";
import { getData } from "../../../mixins/getData";

export default {
    props: {
        managerName: {
            type: String,
            required: true
        },
        investorId: {
            type: [String, Number],
            required: true
        }
    },
    data() {
        return {
            form: {
                manager: ''
            },
            showModal: false,
            managers: [],
            localManagerName: this.managerName // Local copy of the prop
        };
    },
    watch: {
        // Watch the managerName prop for changes and update the local copy
        managerName(newVal) {
            this.localManagerName = newVal;
        }
    },
    methods: {
        openModal() {
            this.showModal = true;
            this.fetchManagers();
        },
        handleClose() {
            this.showModal = false;
        },
        fetchManagers() {
            axios.get('/admin/investors/filter-options')
                .then(response => {
                    responseParse(response, false);
                    if (response.status === 200) {
                        let data = response.data.data;

                        if (data.managers) {
                            this.managers = data.managers;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching managers:', error);
                });
        },
        async changeManager() {
            await getData({
                method: 'POST',
                config: {
                    headers: { 'content-type': 'multipart/form-data' }
                },
                url: '/admin/investors/update-manager',
                data: {
                    manager_id: this.form.manager,
                    investor_id: this.investorId
                }
            }).then(response => {
                responseParse(response, true);

                if (response.status === 200) {
                    let data = response.data.data;
                    if (data.manager) {
                        // Update local manager name instead of mutating the prop
                        this.localManagerName = data.manager.name + ' ' + data.manager.surname;
                        this.handleClose();
                    }
                }
            });
        }
    }
};
</script>
