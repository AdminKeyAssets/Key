<template>
    <div>
        <div class="block" >
            <el-form v-loading="loading"
                     element-loading-text="Loading..."
                     element-loading-spinner="el-icon-loading"
                     element-loading-background="rgba(0, 0, 0, 0.0)"
                     ref="form" :model="form" class="form-horizontal form-bordered">

                <el-tabs v-model="activeTabName">
                    <template v-for="locale in locales">
                        <el-tab-pane class="" :label="locale" :name="locale">

                            <el-row>

                                <div class="form-group dashed">
                                    <label class="col-md-1 control-label">{{ lang.tag_name }} <span class="grey">{{ locale }}</span>:</label>
                                    <div class="col-md-10 uppercase-medium">
                                        <el-select
                                            v-model="form[locale].tags"
                                            multiple
                                            filterable
                                            allow-create
                                            default-first-option
                                            :placeholder="lang.tag_name_placeholder">
                                            <el-option
                                                v-for="item in options.tags[locale]"
                                                :key="item.value"
                                                :label="item.label"
                                                :value="item.value">
                                            </el-option>
                                        </el-select>
                                    </div>
                                </div>

                            </el-row>
                        </el-tab-pane>
                    </template>
                </el-tabs>

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
        'locales',
        'defaultLocale',
        'routes',
        'updateData',
        'item',
        'options'
    ],
    data(){
        return {
            form: {},
            activeTabName: '',
            loading: false
        }
    },
    updated() {
        this.updateData('tag', this.form);
    },
    watch: {
        'defaultLocale'(){
            this.activeTabName = this.defaultLocale;
        },
        'locales'(){
            if (!this.item || Object.keys(this.item).length == 0) {
                this.locales.forEach((value,index) => {
                    if(!this.form[value]) {
                        this.form[value] = {};
                    }
                });
            }
        },
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
