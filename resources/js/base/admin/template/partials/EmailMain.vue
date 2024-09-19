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
                        <label class="col-md-2 control-label">Body: </label>
                        <div class="col-md-6">
                            <el-input class="el-input--is-round"
                                      type="textarea" v-model="form.body"
                                      autosize
                                      :disabled="loading"></el-input>
                        </div>
                    </div>

                </el-row>

            </el-form>
        </div>
    </div>
</template>


<script>
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
