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

    }
}
</script>
