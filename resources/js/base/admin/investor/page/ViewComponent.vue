<template>
    <div class="block">

        <NotifyInvestorByEmail
        :investor-id="id">
        </NotifyInvestorByEmail>

        <el-form v-loading="loading"
                 element-loading-text="Loading..."
                 element-loading-spinner="el-icon-loading"
                 element-loading-background="rgba(0, 0, 0, 0.8)"
                 ref="form" :model="form" class="form-horizontal form-bordered">

            <el-row>

                <div class="form-group">
                    <label class="col-md-2 control-label">Name: </label>
                    <div class="col-md-6">
                        {{ form.name }}
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label">Surname: </label>
                    <div class="col-md-6">
                        {{ form.surname }}
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label">ID/Passport Number: </label>
                    <div class="col-md-6">
                        {{ form.pid }}
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label">Citizenship: </label>
                    <div class="col-md-6">
                        {{ form.citizenship }}
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label">Address:</label>
                    <div class="col-md-6">
                        {{ form.address }}
                    </div>
                </div>

                <div class="form-group">

                    <label class="col-md-2 control-label">Email: </label>
                    <div class="col-md-6">
                        {{ form.email }}
                    </div>

                </div>

                <div class="form-group">

                    <label class="col-md-2 control-label">Is Demo: </label>
                    <div class="col-md-6">
                        <el-switch v-model="form.is_demo" disabled="">
                        </el-switch>
                    </div>

                </div>

                <div class="form-group phone">
                    <label class="col-md-2 control-label">Phone: </label>
                    <div class="col-md-6">
                        {{ form.prefix }}{{ form.phone }}
                    </div>
                </div>

                <div class="form-group">

                    <label class="col-md-2 control-label">Select Manager: <span class="text-danger">*</span>:</label>
                    <div class="col-md-6">
                        <el-select v-model="form.admin_id" filterable
                                   disabled
                                   placeholder="Manager">
                            <el-option
                                v-for="manager in this.managers"
                                :key="manager.id"
                                :label="manager.name + ' ' + manager.surname"
                                :value="manager.id"
                            ></el-option>
                        </el-select>
                    </div>

                </div>


                <div class="form-group dashed" v-if="form.profile_picture">
                    <label class="col-md-2 control-label">Profile Picture:</label>
                    <div class="col-md-6 uppercase-medium">
                        <div>
                            <ImageModal :thumbnail="form.profile_picture"
                                        :image-path="form.profile_picture"
                                        :rounded="false"
                                        :width="200"></ImageModal>
                        </div>
                    </div>
                </div>

                <div class="form-group dashed" v-if="form.passport">
                    <label class="col-md-2 control-label">Upload Passport:</label>
                    <div class="col-md-6 uppercase-medium">
                        <div>
                            <ImageModal :thumbnail="form.passport"
                                        :image-path="form.passport"
                                        :rounded="false"
                                        :width="200"></ImageModal>
                        </div>
                    </div>
                </div>

                <div class="form-group dashed" v-if="form.service_agreement">
                    <label class="col-md-2 control-label">Service Agreement:</label>
                    <div class="col-md-6 uppercase-medium">
                        <div>
                            <p><a :href="form.service_agreement" target="_blank">View Agreement</a></p>
                        </div>
                    </div>
                </div>
            </el-row>

        </el-form>
    </div>
</template>

<style>
.form-group.phone .el-select .el-input {
    width: 110px;
}

.form-group.phone .input-with-select .el-input-group__prepend {
    background-color: #fff;
}
</style>
<script>
import {responseParse} from '../../../mixins/responseParse'
import {getData} from '../../../mixins/getData'
import ImageModal from "../../../components/admin/ImageModal.vue";
import NotifyInvestorByEmail from "../partials/NotifyInvestorByEmail.vue";

export default {
    components: {
        ImageModal, NotifyInvestorByEmail
    },

    props: [
        'getSaveDataRoute',
        'id'
    ],

    data() {
        return {
            data: [],
            loading: false,
            lang: {},
            routes: {},
            options: {},
            countries: [],
            prefixes: [],
            managers: [],
            form: {
                guard_name: 'admin',
                password: '',
                name: '',
                surname: '',
                pid: '',
                citizenship: '',
                address: '',
                email: '',
                phone: '',
                prefix: '',
                admin_id: '',
                profile_picture: null,
                profilePicturePreview: null,
                passport: null,
                passportPreview: null,
                is_demo: false
            },
        }
    },
    created() {
        if (this.id) {
            this.form.id = this.id;
        }
        this.getSaveData();
    },
    methods: {
        async getSaveData() {
            this.loading = true;
            await getData({
                method: 'POST',
                url: this.getSaveDataRoute,
                data: this.form
            }).then(response => {
                responseParse(response, false);
                if (response.status == 200) {
                    let data = response.data.data;

                    this.routes = data.routes;
                    this.options = data.options;
                    if (data.item) {
                        this.form = {...this.form, ...data.item};
                        this.form.is_demo = Boolean(data.item.is_demo === true || data.item.is_demo === "true" || data.item.is_demo === 1);
                    }
                    if (data.countries) {
                        this.countries = data.countries;
                    }
                    if (data.prefixes) {
                        this.prefixes = data.prefixes;
                    }
                    if (data.managers) {
                        this.managers = data.managers;
                    }
                }
                this.loading = false
            });
        },
    }
}
</script>
