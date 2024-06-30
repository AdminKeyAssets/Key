<template>
    <div>
        <div class="col-xs-12">
            <div class="registration-btn project-title-buttons">

                <div class="project-title">

                </div>

                <div class="block">
                    <el-form ref="form" :model="form" class="form-horizontal form-bordered">

                        <el-row>

                            <div class="form-group dashed">
                                <label class="col-md-1 control-label">Select Asset Manager:</label>
                                <div class="col-md-10 uppercase-medium">
                                    <el-select v-model="form.admin_id" :value="form.admin_id" filterable
                                               placeholder="Select">
                                        <el-option
                                            v-for="(item, index) in managers"
                                            :key="index"
                                            :label="item.name + item.surname"
                                            :value="item.id">
                                        </el-option>
                                    </el-select>
                                </div>
                            </div>


                        </el-row>
                    </el-form>
                </div>

                <div class="project-buttons">
                    <el-button type="primary" size="medium" icon="el-icon-check"
                               @click="save"
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
        'managers',
        'id',
        'managerId',
        'changeManagerUrl'
    ],
    data() {
        return {
            /** Form data*/
            form: {
                admin_id: this.managerId
            },
        }
    },

    methods: {
        async save() {
            let formData = new FormData();
            formData.append('admin_id', this.form.admin_id);
            formData.append('id', this.id);

            await axios.post(this.changeManagerUrl, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                responseParse(response);
                setTimeout(() => {
                    window.location.href = '/assets/list';
                }, 1000);
            })
                .catch(error => {
                    responseParse(error.response);
                });
        }
    }
}

</script>
