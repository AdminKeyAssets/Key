<template>
    <div>
        <div class="block">
            <el-form v-loading="loading"
                     element-loading-text="Loading..."
                     element-loading-spinner="el-icon-loading"
                     element-loading-background="rgba(0, 0, 0, 0.0)"
                     ref="form" :model="form" class="form-horizontal form-bordered">

                <el-row>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Price:</label>
                        <div class="col-md-7 uppercase-medium">
                            <input class="form-control" :disabled="loading" v-model="form.price"></input>
                        </div>
                        <div class="col-md-3 uppercase-medium">
                            <el-select v-model="form.currency" :value="form.currency" filterable placeholder="Select">
                                <el-option
                                    v-for="(currency, index) in currencies"
                                    :key="index"
                                    :label="currency"
                                    :value="index">
                                </el-option>
                            </el-select>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Date From:</label>
                        <div class="col-md-10 uppercase-medium">
                            <el-date-picker
                                v-model="form.date_from"
                                format="yyyy/MM/dd"
                                value-format="yyyy/MM/dd"
                                type="date"
                                placeholder="Pick a rental date from">
                            </el-date-picker>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Date To:</label>
                        <div class="col-md-10 uppercase-medium">
                            <el-date-picker
                                v-model="form.date_to"
                                format="yyyy/MM/dd"
                                value-format="yyyy/MM/dd"
                                type="date"
                                placeholder="Pick a rental date to">
                            </el-date-picker>
                        </div>
                    </div>
                </el-row>
                <div class="form-group dashed">
                    <label class="col-md-1 control-label">Select Asset:</label>
                    <div class="col-md-10 uppercase-medium">
                        <el-select v-model="form.asset_id" :value="form.asset_id" filterable placeholder="Select">
                            <el-option
                                v-for="item in assets"
                                :key="item.id"
                                :label="item.name"
                                :value="item.id">
                            </el-option>
                        </el-select>
                    </div>
                </div>
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
        'assets'
    ],
    data() {
        return {
            form: {},
            loading: false,
            editor: ClassicEditor,
            addDetailIsBtnDisabled: true,
            currencies: {
                "GEL": "GEL",
                "USD": "USD",
            },
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
}

</script>
