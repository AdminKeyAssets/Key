<template>
    <div>
        <div class="block">
            <el-form v-loading="loading"
                     element-loading-text="Loading..."
                     element-loading-spinner="el-icon-loading"
                     element-loading-background="rgba(0, 0, 0, 0.0)"
                     ref="form" :model="form" class="form-horizontal form-bordered">

                <el-row>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Name: </label>
                        <div class="col-md-6">
                            <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                      v-model="form.name" @input="capitalizeFirstLetter('name')"></el-input>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Surname: </label>
                        <div class="col-md-6">
                            <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                      v-model="form.surname" @input="capitalizeFirstLetter('surname')"></el-input>
                        </div>
                    </div>

                    <div class="form-group">

                        <label class="col-md-2 control-label">Email: </label>
                        <div class="col-md-6">
                            <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                      v-model="form.email"></el-input>
                        </div>

                    </div>

                    <div class="form-group phone">
                        <label class="col-md-2 control-label">Phone: </label>
                        <div class="col-md-6">
                            <el-input placeholder="Phone" v-model="form.phone" class="input-with-select">
                                <el-select v-model="form.prefix" slot="prepend" filterable
                                           placeholder="Prefix">
                                    <el-option
                                        v-for="prefix in this.prefixes"
                                        :key="prefix.prefix"
                                        :label="prefix.prefix"
                                        :value="prefix.prefix"
                                    ></el-option>
                                </el-select>
                            </el-input>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-2 control-label">Status:</label>
                        <div class="col-md-6 uppercase-medium">
                            <el-select
                                v-model="form.status"
                                placeholder="Select Status">
                                <el-option label="Not Responding" value="Not Responding"></el-option>
                                <el-option label="Communication" value="Communication"></el-option>
                                <el-option label="Proposal Sent" value="Proposal Sent"></el-option>
                                <el-option label="Refused" value="Refused"></el-option>
                                <el-option label="Signed" value="Signed"></el-option>
                            </el-select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Select Manager: </label>
                        <div class="col-md-6">
                            <el-select v-model="form.admin_id" filterable
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

                    <div class="form-group">
                        <label class="col-md-2 control-label">Marketing Channel: </label>
                        <div class="col-md-6">
                            <el-input class="el-input--is-round" maxlength="150" show-word-limit :disabled="loading"
                                      v-model="form.marketing_channel"></el-input>
                        </div>
                    </div>
                </el-row>

            </el-form>
        </div>
    </div>
</template>


<script>
import {responseParse} from '../../../mixins/responseParse'
import {getData} from '../../../mixins/getData'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {
    props: [
        'routes',
        'updateData',
        'item',
        'prefixes',
        'managers'
    ],
    data() {
        return {
            form: {},
            loading: false,
            editor: ClassicEditor,
        }
    },
    updated() {
        this.updateData(this.form);
    },
    watch: {
        'item'() {
            if (this.item) {
                this.form = this.item;
            }
        }
    },
    methods: {
        capitalizeFirstLetter(field) {
            if (this.form[field]) {
                this.form[field] = this.form[field].charAt(0).toUpperCase() + this.form[field].slice(1);
            }
        },
    }
}

</script>
