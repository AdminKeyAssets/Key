<template>
    <div>
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Upload Icon:</label>
            <div class="col-md-10 uppercase-medium">
                <div class="upload-container">
                    <!-- Drag and Drop Upload Area -->
                    <div
                        class="drop-area"
                        @click="triggerInput"
                        @dragover.prevent
                        @drop.prevent="handleDrop"
                    >
                        <p>Drag your images here or click to upload</p>
                        <input
                            type="file"
                            multiple
                            @change="handleFiles"
                            ref="fileInput"
                            style="display: none;"
                        />
                    </div>

                    <!-- Thumbnails Preview with Drag-and-Drop Sorting -->
                    <div class="preview">
                        <div
                            class="thumbnail"
                            v-for="(file, index) in files"
                            :key="index"
                            draggable="true"
                            @dragstart="onDragStart(index)"
                            @dragover.prevent
                            @drop="onDrop(index)"
                        >
                            <img
                                v-if="file.preview"
                                :src="file.preview"
                                :alt="file.name"
                                class="img-thumbnail"
                            />
                            <img
                                v-else
                                :src="file.image"
                                :alt="file.name"
                                class="img-thumbnail"
                            />
                            <div class="remove" @click="removeFile(index)">×</div>
                            <span class="move-to-front" @click="moveToFront(index)">
                <i class="fa fa-arrow-up"></i>
              </span>
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
        loading: {
            type: Boolean,
            default: false
        },
        updateData: {
            type: Function,
            required: false
        }
    },
    data() {
        return {
            files: [],
            draggedIndex: null
        };
    },
    watch: {
        form: {
            handler(newVal) {
                if (Array.isArray(newVal.attachments)) {
                    this.files = [...newVal.attachments];
                }
            },
            immediate: true,
            deep: true
        }
    },
    methods: {
        triggerInput() {
            this.$refs.fileInput.click();
        },
        handleFiles(e) {
            const selectedFiles = e.target.files;
            for (let i = 0; i < selectedFiles.length; i++) {
                const file = selectedFiles[i];
                const reader = new FileReader();

                reader.onload = (event) => {
                    const newAttachment = {
                        file: file,
                        preview: file.type.startsWith('image/') ? event.target.result : null,
                        name: file.name
                    };
                    this.files.push(newAttachment);
                    this.emitUpdate();
                };

                reader.readAsDataURL(file);
            }
            // reset input
            e.target.value = null;
        },
        handleDrop(e) {
            const droppedFiles = e.dataTransfer.files;
            for (let i = 0; i < droppedFiles.length; i++) {
                const file = droppedFiles[i];
                const reader = new FileReader();

                reader.onload = (event) => {
                    const newAttachment = {
                        file: file,
                        preview: file.type.startsWith('image/') ? event.target.result : null,
                        name: file.name
                    };
                    this.files.push(newAttachment);
                    this.emitUpdate();
                };

                reader.readAsDataURL(file);
            }
        },
        removeFile(index) {
            this.files.splice(index, 1);
            this.emitUpdate();
        },
        moveToFront(index) {
            if (index > 0) {
                const [file] = this.files.splice(index, 1);
                this.files.unshift(file);
                this.emitUpdate();
            }
        },
        emitUpdate() {
            this.$emit('update-form', {
                ...this.form,
                attachments: [...this.files]
            });
        },
        // Drag and Drop Reordering
        onDragStart(index) {
            this.draggedIndex = index;
        },
        onDrop(targetIndex) {
            if (this.draggedIndex === null) return;

            const movedItem = this.files.splice(this.draggedIndex, 1)[0];
            this.files.splice(targetIndex, 0, movedItem);
            this.draggedIndex = null;
            this.emitUpdate();
        }
    }
};
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
    margin-bottom: 10px;
    position: relative;
    display: inline-block;
    cursor: move;
}

.img-thumbnail {
    width: 100px;
    height: 100px;
    object-fit: cover;
    user-select: none;
}

.remove {
    position: absolute;
    top: 0;
    right: 0;
    background: red;
    color: white;
    cursor: pointer;
    padding: 2px 5px;
}

/* Move-to-front icon styling */
.move-to-front {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: rgba(0,0,0,0.5);
    color: white;
    padding: 2px 4px;
    border-radius: 3px;
    cursor: pointer;
}
</style>
