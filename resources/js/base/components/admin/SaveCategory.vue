<template>
    <div>
        <div class="block" >
            <el-form v-loading="loading"
                     element-loading-text="Loading..."
                     element-loading-spinner="el-icon-loading"
                     element-loading-background="rgba(0, 0, 0, 0.0)"
                     ref="form" :model="form" class="form-horizontal form-bordered">

                    <el-row>

                        <div class="form-group dashed">
                            <label class="col-md-1 control-label">{{ lang.category_name }}</label>
                            <div class="col-md-10 uppercase-medium">
                                <el-select
                                    v-model="form.categories"
                                    multiple
                                    filterable
                                    allow-create
                                    default-first-option
                                    :placeholder="lang.category_name">
                                    <el-option
                                        v-for="item in options.categories"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value">
                                    </el-option>
                                </el-select>
                            </div>
                        </div>

                    </el-row>

            </el-form>
        </div>
    </div>
</template>


<script>
import {responseParse} from '../../mixins/responseParse'
import {getData} from '../../mixins/getData'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {
    props: [
        'lang',
        'routes',
        'updateData',
        'item',
        'options'
    ],
    data(){
        return {
            form: {},
            loading: false
        }
    },
    updated() {
        this.updateData('category', this.form);
    },
    watch: {
        'item'(){
            if (this.item) {
                this.form = this.item;
            }
        }
    },
    methods: {
    }
}

</script>
