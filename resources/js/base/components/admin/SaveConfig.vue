<template>
    <div
        v-loading="loading"
        element-loading-text="Loading..."
        element-loading-spinner="el-icon-loading"
        element-loading-background="rgba(0, 0, 0, 0.0)">
        <el-button icon="el-icon-document-add" circle @click="showConfigData"></el-button>

        <el-drawer
            :title="lang.title"
            :visible.sync="dialogVisible"
            direction="rtl"
            custom-class="demo-drawer"
            :before-close="handleClose"
            size="60%"
            ref="drawer">
            <div class="demo-drawer__content">
                <div class="block">
                    <div class="row">
                        <template v-for="attribute in this.form.attributes">
                            <div class="form-group dashed">
                                <label class="col-md-3 control-label">{{ attribute.label }}</label>
                                <div class="col-md-9 uppercase-medium">

                                    <template v-if="attribute.type == 'integer'">
                                        <input type="number" class="form-control" maxlength="150"
                                               :disabled="loading"
                                               v-model="attribute.value"></input>
                                    </template>

                                    <template v-if="attribute.type == 'select'">
                                        <el-select
                                            v-model="attribute.value"
                                            clearable
                                            filterable
                                            :placeholder="attribute.label">
                                            <el-option
                                                v-for="(item,key) in attribute.options"
                                                :key="key"
                                                :label="item.label"
                                                :value="item.value.toString()">
                                            </el-option>
                                        </el-select>
                                    </template>

                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="demo-drawer__footer">
                        <el-button @click="toggleModal">{{ lang.cancel }}</el-button>
                        <el-button type="primary" @click="saveData">{{lang.save }}</el-button>
                    </div>
                </div>
            </div>
        </el-drawer>
    </div>
</template>

<script>
import {getData} from "../../mixins/getData";
import {responseParse} from "../../mixins/responseParse";

export default {
    props: [
        'configKey',
        'getConfigDataRoute',
        'attributesKey'
    ],
    data() {
        return {
            dialogVisible: false,
            loading: false,
            routes: {},
            lang: {},
            form: {
                key: this.configKey,
                value_meta: {},
                attributes: []
            }
        }
    },
    methods: {
        async showConfigData(){
            this.loading = true
            await getData({
                method: 'GET',
                url: this.getConfigDataRoute,
                data: {
                    key: this.configKey,
                    attributes_key: this.attributesKey
                }
            }).then(response => {
                // Parse response notification.
                responseParse(response);
                if (response.status == 200) {
                    let data = response.data.data;
                    this.routes = data.routes
                    this.lang = data.trans_text
                    if (data.item) {
                        this.form = data.item;
                    }
                    this.form.attributes = data.attributes;
                    this.toggleModal();
                }
                this.loading = false
            });
        },
        async saveData () {
            this.loading = true
            await getData({
                method: 'POST',
                url: this.routes.save,
                data: this.form
            }).then(response => {
                // Parse response notification.
                responseParse(response);
                if (response.status == 200) {
                    this.toggleModal();
                    setTimeout(() => {
                        window.location.reload();
                    },1000);
                }
                this.loading = false
            });
        },
        toggleModal(){
            this.dialogVisible = !this.dialogVisible;
        },
        handleClose (done) {
            this.toggleModal();
        }
    }
}
</script>
