<template>
    <div>
        <div style="margin-bottom: 20px">
            <el-button @click="openModal" style="cursor: pointer">Send Email</el-button>
        </div>
        <el-dialog
            title="Send Email"
            :visible.sync="showModal"
            width="30%"
            :before-close="handleClose"
        >
            <el-form>
                <el-form-item label="Select email template:">
                    <el-select v-model="form.template" @change="onTemplateSelect" placeholder="Select Template">
                        <el-option
                            v-for="template in emailTemplates"
                            :key="template.id"
                            :label="template.name"
                            :value="template.id"
                        ></el-option>
                    </el-select>
                </el-form-item>

                <!-- Textarea for the email body -->
                <el-form-item label="Email Body" v-if="form.templateBody">
                    <el-input
                        type="textarea"
                        v-model="form.body"
                        :rows="6"
                    ></el-input>
                </el-form-item>
            </el-form>

            <span slot="footer" class="dialog-footer">
                <el-button @click="showModal = false">Cancel</el-button>
                <el-button type="primary" @click="sendEmail">Send</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
import {responseParse} from "../../../mixins/responseParse";
import {getData} from "../../../mixins/getData";

export default {
    props: {
        investorId: {
            type: [String, Number],
            required: true
        }
    },
    data() {
        return {
            form: {
                template: '',
                body: '',
                templateBody: '',
            },
            showModal: false,
            emailTemplates: [],
        };
    },

    methods: {
        openModal() {
            this.showModal = true;
            this.fetchEmailTemplates();
        },
        handleClose() {
            this.showModal = false;
        },
        fetchEmailTemplates() {
            axios.get('/templates/filter')
                .then(response => {
                    responseParse(response, false);
                    if (response.status === 200) {
                        let data = response.data.data;

                        if (data.templates) {
                            this.emailTemplates = data.templates; // Corrected property
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching templates:', error);
                });
        },
        onTemplateSelect(templateId) {
            const selectedTemplate = this.emailTemplates.find(template => template.id === templateId);
            if (selectedTemplate) {
                this.form.body = selectedTemplate.body; // Set the email body based on selected template
                this.form.templateBody = selectedTemplate.body;
            }
        },
        sendEmail() {
            const payload = {
                investor_id: this.investorId,
                body: this.form.body
            };

            axios.post('/admin/investors/notify', payload)
                .then(response => {
                    if (response.status === 200) {
                        this.$message.success('Email sent successfully');
                        this.showModal = false;
                    }
                })
                .catch(error => {
                    this.$message.error('Error sending email');
                    console.error('Error sending email:', error);
                });
        }
    }
};
</script>
