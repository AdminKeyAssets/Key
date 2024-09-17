<template>
    <div>
<!--        <div class="form-group dashed">-->
<!--            <label class="col-md-1 control-label">Photos:</label>-->
<!--            <div class="col-md-10 uppercase-medium">-->
<!--                <input type="file" @change="onFileChange" multiple>-->
<!--                <div v-if="form.attachments">-->
<!--                    <ul>-->
<!--                        <li v-for="(file, index) in form.attachments" :key="index"-->
<!--                            style="display: inline-block; margin-right: 10px">-->
<!--                            <img v-if="file.preview" :src="file.preview" alt="preview"-->
<!--                                 style="max-width: 100px;"/>-->
<!--                            <img v-else-if="file.type === 'image'" :src="file.path" alt="preview"-->
<!--                                 style="max-width: 100px;"/>-->
<!--                            <a v-else :href="file.path" target="_blank">{{ file.name }}</a>-->
<!--                            <el-button icon="el-icon-delete-solid" size="small" type="danger"-->
<!--                                       @click="removeAttachment(index)"></el-button>-->
<!--                        </li>-->
<!--                    </ul>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Upload Icon:</label>
            <div class="col-md-10 uppercase-medium">
                <div class="upload-container">
                    <!-- Drag and Drop Area -->
                    <div class="drop-area"
                         @click="triggerInput">
                        <p>Drag your images here or click to upload</p>
                        <input type="file" multiple @change="handleFiles" ref="fileInput" style="display: none;">
                    </div>

                    <!-- Thumbnails Preview -->
                    <div class="preview">
                        <div class="thumbnail" v-for="(file, index) in files" :key="index">
                            <img v-if="file.preview" :src="file.preview" :alt="file.name" class="img-thumbnail">
                            <img v-else :src="file.image" :alt="file.name" class="img-thumbnail">
                            <div class="remove" @click="removeFile(index)">×</div>
                        </div>
                    </div>
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
    data() {
        return {
            files: [],
        };
    },
    watch: {
        'form'() {
            if (this.form.attachments) {
                this.files = this.form.attachments;
            }
        }
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



        triggerInput() {
            this.$refs.fileInput.click();
        },
        handleFiles(e) {
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
        removeFile(index) {
            this.files.splice(index, 1);
            console.log(this.files);
            this.form.attachments = this.files;
            this.$emit('update-form', {...this.form});
        }
    }
}
</script>

<style scoped>
.upload-container {
    margin: 20px;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.drop-area {
    padding: 20px;
    border: 2px dashed #ccc;
    text-align: center;
    cursor: pointer;
}

.drop-area:hover {
    background-color: #f9f9f9;
}

.preview {
    display: flex;
    flex-wrap: wrap;
    margin-top: 20px;
}

.thumbnail {
    margin-right: 10px;
    position: relative;
    display: inline-block;
}

.img-thumbnail {
    width: 100px;
    height: 100px;
    object-fit: cover;
}

.remove {
    position: absolute;
    top: 0;
    right: 0;
    background: red;
    color: white;
    cursor: pointer;
}
</style>
