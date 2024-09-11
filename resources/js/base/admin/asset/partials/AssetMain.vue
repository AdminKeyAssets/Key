<template>
    <div>
        <div class="block">
            <el-form v-loading="loading"
                     element-loading-text="Loading..."
                     element-loading-spinner="el-icon-loading"
                     element-loading-background="rgba(0, 0, 0, 0.0)"
                     ref="form" :model="form" class="form-horizontal form-bordered">
                <el-row style="margin-left: 15px">
                    <el-tabs v-model="activeNames">
                        <el-tab-pane label="Project Details" name="1">
                            <ProjectDetails :form="form" :loading="loading" @update-form="updateForm" :item="item" :update-data="updateData"/>
                        </el-tab-pane>
                        <el-tab-pane label="Asset Details" name="2">
                            <AssetDetails :form="form" :item="item" :loading="loading" :types="types" :currencies="currencies" :conditions="conditions" :investors="investors" @update-form="updateForm"/>
                        </el-tab-pane>
                        <el-tab-pane label="Asset Photos" name="3">
                            <ExtraDetails :form="form" :loading="loading" @update-form="updateForm"/>
                        </el-tab-pane>
                        <el-tab-pane label="Agreement Details" name="4">
                            <AgreementDetails
                                :form="form" :loading="loading" :currencies="currencies" :agreement-statuses="agreementStatuses" :numbers="numbers" @update-form="updateForm"/>
                        </el-tab-pane>
                        <el-tab-pane label="Current Value" name="5">
                            <CurrentValue :form="form"  :currencies="currencies" :loading="loading" @update-form="updateForm"/>
                        </el-tab-pane>
                    </el-tabs>
                </el-row>
            </el-form>
        </div>
    </div>
</template>

<script>
import ProjectDetails from './components/ProjectDetails.vue'
import AssetDetails from './components/AssetDetails.vue'
import ExtraDetails from './components/ExtraDetails.vue'
import AgreementDetails from './components/AgreementDetails.vue'
import CurrentValue from './components/CurrentValue.vue'

export default {
    components: { ProjectDetails, AssetDetails, ExtraDetails, AgreementDetails, CurrentValue },
    props: [
        'routes',
        'updateData',
        'item',
        'investors'
    ],
    data() {
        return {
            form: {
                extraDetails: [],
                attachments: [],
                existingAttachments: [],
                icon: null,
                iconPreview: null,
                payments: [],
                currency: 'USD',
                current_value: '',
            },
            loading: false,
            addDetailIsBtnDisabled: true,
            fileList: [],
            currencies: {
                "USD": "USD",
                "GEL": "GEL",
            },
            types: {
                "Flat": "Flat",
                "Land": "Land",
                "Office": "Office",
                "Commercial Space": "Commercial Space",
                "Villa": "Villa"
            },
            conditions: {
                "Black Frame": "Black Frame",
                "White Frame": "White Frame",
                "Green Frame": "Green Frame",
                "Renovated": "Renovated",
                "None": "None"
            },
            agreementStatuses: {
                "Complete": "Complete",
                "Installments": "Installments"
            },
            activeNames: '1',
            numbers: this.getNumbersInRange(2, 50),
        }
    },
    updated() {
        this.updateData(this.form);
    },
    watch: {
        'item'() {
            if (this.item) {
                this.form = this.item;
                this.form.existingAttachments = this.item.existingAttachments || [];
                this.form.tenant = this.item.tenant || [];
            }
        }
    },
    methods: {
        getNumbersInRange(start, end) {
            let numbers = [];
            for (let i = start; i <= end; i++) {
                numbers.push(i);
            }
            return numbers;
        },

        updateForm(newForm) {
            this.form = newForm;
        },
    }
}
</script>
