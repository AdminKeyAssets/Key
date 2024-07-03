<template>
    <div>
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Attachments:</label>
            <div class="col-md-10 uppercase-medium">
                <input type="file" @change="onFileChange" multiple>
                <div v-if="form.attachments">
                    <ul>
                        <li v-for="(file, index) in form.attachments" :key="index"
                            style="display: inline-block; margin-right: 10px">
                            <img v-if="file.preview" :src="file.preview" alt="preview"
                                 style="max-width: 100px;"/>
                            <img v-else-if="file.type === 'image'" :src="file.path" alt="preview"
                                 style="max-width: 100px;"/>
                            <a v-else :href="file.path" target="_blank">{{ file.name }}</a>
                            <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                       @click="removeAttachment(index)"></el-button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Extra Details:</label>
            <div class="col-md-10 uppercase-medium">
                <el-form-item
                    v-for="(extraDetail) in form.extraDetails"
                    :key="extraDetail.id">
                    <div class="col-md-5 uppercase-medium">
                        <el-input class="col-md-5" v-model="extraDetail.key"
                                  placeholder="Name for extra detail"></el-input>
                    </div>
                    <div class="col-md-5 uppercase-medium">
                        <el-input class="col-md-5" v-model="extraDetail.value"
                                  placeholder="Value for extra detail"></el-input>
                    </div>
                    <el-button icon="el-icon-delete-solid" size="small" type="danger"
                               @click.prevent="removeDetail(extraDetail)"></el-button>
                </el-form-item>
                <el-button type="primary" size="medium" icon="el-icon-plus" @click="addDetail">Add Extra Details</el-button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        form: {
            type: Object,
            required: true,
            default: () => ({
                attachments: [],
                extraDetails: []
            })
        },
        loading: Boolean,
        updateData: Function
    },
    methods: {
        onFileChange(e) {
            const files = e.target.files;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.$emit('update-form', {
                        ...this.form,
                        attachments: [...(Array.isArray(this.form.attachments) ? this.form.attachments : []), {
                            file: file,
                            preview: file.type.startsWith('image/') ? e.target.result : null,
                            name: file.name,
                        }]
                    });
                };
                reader.readAsDataURL(file);
            }
        },
        removeAttachment(index) {
            const attachments = Array.isArray(this.form.attachments) ? [...this.form.attachments] : [];
            attachments.splice(index, 1);
            this.$emit('update-form', { ...this.form, attachments });
        },
        addDetail() {
            this.$emit('update-form', {
                ...this.form,
                extraDetails: [...(Array.isArray(this.form.extraDetails) ? this.form.extraDetails : []), {
                    id: Date.now(),
                    key: '',
                    value: ''
                }]
            });
        },
        removeDetail(item) {
            const extraDetails = Array.isArray(this.form.extraDetails) ? this.form.extraDetails.filter(detail => detail.id !== item.id) : [];
            this.$emit('update-form', { ...this.form, extraDetails });
        }
    }
}
</script>
