<template>
    <div>
        <div class="block">
            <div class="form-horizontal form-bordered">

                <div class="form-group dashed">
                    <label class="col-md-2 control-label">Title:</label>
                    <div class="col-md-10">
                        <input
                            class="form-control"
                            :disabled="loading || isSubmitting"
                            v-model="form.title"
                            placeholder="Enter news title" />
                    </div>
                </div>

                <div class="form-group dashed">
                    <label class="col-md-2 control-label">Content:</label>
                    <div class="col-md-10">
                        <div ref="editorContainer" v-if="!loading && domReady">
                            <ckeditor
                                :key="'ckeditor-' + (form.id || 'new')"
                                :editor="editor"
                                v-model="form.content"
                                :disabled="isSubmitting"
                                :config="editorConfig"
                                @ready="onEditorReady"
                                @destroy="onEditorDestroy">
                            </ckeditor>
                        </div>
                        <div v-else class="text-center" style="padding: 50px;">
                            <i class="fa fa-spinner fa-spin"></i> Loading editor...
                        </div>
                    </div>
                </div>

                <div class="form-group dashed">
                    <label class="col-md-2 control-label">Status:</label>
                    <div class="col-md-10">
                        <el-select v-model="form.status" :disabled="loading || isSubmitting">
                            <el-option label="Draft" value="draft"></el-option>
                            <el-option label="Published" value="published"></el-option>
                        </el-select>
                    </div>
                </div>

                <!-- Manager Selection (only for administrators) -->
                <div v-if="isAdmin && managers.length > 0" class="form-group dashed">
                    <label class="col-md-2 control-label">Assign Manager:</label>
                    <div class="col-md-10">
                        <el-select
                            v-model="form.manager_id"
                            :disabled="loading || isSubmitting"
                            placeholder="Select a manager (optional)"
                            clearable>
                            <el-option
                                v-for="manager in managers"
                                :key="manager.id"
                                :label="manager.full_name || (manager.name + ' ' + manager.surname)"
                                :value="manager.id">
                            </el-option>
                        </el-select>
                    </div>
                </div>

                <!-- Investor Selection -->
                <div class="form-group dashed">
                    <label class="col-md-2 control-label">Attach to Investors:</label>
                    <div class="col-md-10">
                        <el-select
                            v-model="form.investor_ids"
                            :disabled="loading || isSubmitting"
                            multiple
                            filterable
                            placeholder="Select investors">
                            <el-option
                                v-for="investor in investors"
                                :key="investor.id"
                                :label="investor.full_name || (investor.name + ' ' + investor.surname)"
                                :value="investor.id">
                            </el-option>
                        </el-select>
                        <small class="text-muted">Select which investors should receive this news</small>
                    </div>
                </div>

                <!-- Image Gallery Section -->
                <div class="form-group dashed">
                    <label class="col-md-2 control-label">Images:</label>
                    <div class="col-md-10">
                        <div class="upload-container">
                            <div class="drop-area"
                                 @dragover.prevent
                                 @dragleave.prevent
                                 @drop.prevent="handleDrop"
                                 @click="triggerInput">
                                <p>Drag your images here or click to upload</p>
                                <p class="text-muted">The first image will be used as thumbnail</p>
                                <input type="file" multiple @change="handleFiles" ref="fileInput" style="display: none;" accept="image/*">
                            </div>

                            <div class="preview" v-if="files.length > 0">
                                <div
                                    class="thumbnail"
                                    v-for="(file, index) in files"
                                    :key="index"
                                    draggable="true"
                                    @dragstart="onDragStart(index)"
                                    @dragover.prevent
                                    @drop="onDrop(index)"
                                >
                                    <img v-if="file.preview" :src="file.preview" :alt="file.name" class="img-thumbnail">
                                    <img v-else :src="file.image" :alt="file.name" class="img-thumbnail">
                                    <div class="remove" @click="removeFile(index)">×</div>
                                    <span v-if="index === 0" class="thumbnail-badge">Thumbnail</span>
                                    <span v-else class="move-to-front" @click="moveToFront(index)">
                                        <i class="fa fa-arrow-up"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-group">
                    <div class="col-md-12 text-right">
                        <button @click="goBack" class="btn btn-secondary" :disabled="isSubmitting">
                            Cancel
                        </button>
                        <button @click="saveNews" class="btn btn-primary" :disabled="loading || isSubmitting" style="margin-left: 10px;">
                            <i v-if="isSubmitting" class="fa fa-spinner fa-spin"></i>
                            {{ isSubmitting ? 'Saving...' : (form.id ? 'Update News' : 'Create News') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {
    props: ['newsId', 'getSaveDataRoute', 'saveRoute', 'backRoute'],
    data() {
        return {
            loading: false,
            isSubmitting: false,
            isDestroying: false,
            domReady: false,
            isAdmin: false,
            editor: ClassicEditor,
            editorInstance: null,
            editorConfig: {
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'link', '|',
                    'bulletedList', 'numberedList', '|',
                    'indent', 'outdent', '|',
                    'blockQuote', 'insertTable', '|',
                    'undo', 'redo'
                ]
            },
            form: {
                id: null,
                title: '',
                content: '',
                status: 'draft',
                manager_id: null,
                investor_ids: [],
                gallery: []
            },
            files: [],
            investors: [],
            managers: [],
            dragIndex: null
        };
    },
    mounted() {
        // Ensure DOM is fully ready before initializing editor
        this.$nextTick(() => {
            setTimeout(() => {
                this.domReady = true;
                this.loadData();
            }, 100); // Small delay to ensure DOM stability
        });
    },
    watch: {
        'form.content'(newVal, oldVal) {
            // Safely handle content changes
            if (this.editorInstance && newVal !== oldVal && !this.isDestroying) {
                try {
                    // Check if editor is still valid and attached to DOM
                    if (this.editorInstance.sourceElement && this.editorInstance.sourceElement.parentNode) {
                        // Only update if the editor content is different
                        const currentData = this.editorInstance.getData();
                        if (currentData !== newVal) {
                            this.editorInstance.setData(newVal || '');
                        }
                    }
                } catch (error) {
                    console.warn('Editor content update failed:', error);
                }
            }
        }
    },
    beforeDestroy() {
        // Set flags to prevent operations during destruction
        this.isDestroying = true;
        this.domReady = false;
        
        // Clean up editor instance
        if (this.editorInstance) {
            try {
                // Check if editor is still valid before destroying
                if (this.editorInstance.sourceElement && this.editorInstance.sourceElement.parentNode) {
                    this.editorInstance.destroy()
                        .catch(error => {
                            console.error('Error destroying editor:', error);
                        });
                }
            } catch (error) {
                console.error('Error during editor cleanup:', error);
            } finally {
                this.editorInstance = null;
            }
        }
    },
    methods: {
        onEditorReady(editor) {
            if (!this.isDestroying && this.$refs.editorContainer) {
                try {
                    // Verify the editor container is still in the DOM
                    if (this.$refs.editorContainer.parentNode) {
                        this.editorInstance = editor;
                        console.log('Editor is ready to use!', editor);
                    } else {
                        console.warn('Editor container not properly attached to DOM');
                    }
                } catch (error) {
                    console.error('Error in onEditorReady:', error);
                }
            }
        },

        onEditorDestroy() {
            this.editorInstance = null;
            console.log('Editor was destroyed');
        },

        async loadData() {
            if (this.isDestroying) {
                return; // Don't load data if component is being destroyed
            }
            
            this.loading = true;
            try {
                const response = await axios.post(this.getSaveDataRoute, {
                    id: this.newsId
                });
                
                if (response.data.data && !this.isDestroying) {
                    const data = response.data.data;
                    
                    // Always load investors and managers data
                    this.investors = data.investors || [];
                    this.managers = data.managers || [];
                    this.isAdmin = data.managers && data.managers.length > 0;

                    // Load existing item data if editing
                    if (data.item) {
                        this.form = {
                            id: data.item.id,
                            title: data.item.title || '',
                            content: data.item.content || '',
                            status: data.item.status || 'draft',
                            manager_id: data.item.manager_id,
                            investor_ids: data.item.investor_ids || [],
                            gallery: data.item.gallery || []
                        };

                        if (data.item.gallery) {
                            this.files = data.item.gallery.map(file => ({
                                id: file.id,
                                image: file.image || file.preview,
                                preview: file.preview || file.image,
                                name: file.name || file.fileName || '',
                                fileName: file.fileName || file.name || '',
                                is_thumbnail: file.is_thumbnail
                            }));
                        }
                    }
                }
            } catch (error) {
                console.error('Error loading data:', error);
                this.$notify.error({
                    title: 'Error',
                    message: 'Failed to load news data'
                });
            } finally {
                this.loading = false;
            }
        },

        async saveNews() {
            if (!this.form.title.trim()) {
                this.$notify.error({
                    title: 'Validation Error',
                    message: 'Title is required'
                });
                return;
            }

            if (!this.form.content.trim()) {
                this.$notify.error({
                    title: 'Validation Error',
                    message: 'Content is required'
                });
                return;
            }

            this.isSubmitting = true;

            try {
                const formData = new FormData();

                if (this.form.id) {
                    formData.append('id', this.form.id);
                }

                formData.append('title', this.form.title);
                formData.append('content', this.form.content);
                formData.append('status', this.form.status);

                if (this.form.manager_id) {
                    formData.append('manager_id', this.form.manager_id);
                }

                if (this.form.investor_ids.length > 0) {
                    formData.append('investor_ids', this.form.investor_ids.join(','));
                }

                // Handle gallery files
                if (this.files.length > 0) {
                    this.files.forEach((file, index) => {
                        if (file.file) {
                            // New file upload
                            formData.append(`gallery[${index}]`, file.file);
                        } else if (file.image) {
                            // Existing image URL
                            formData.append(`gallery[${index}]`, file.image);
                        }
                    });
                }

                const response = await axios.post(this.saveRoute, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                if (response.data.status) {
                    this.$notify({
                        title: 'Success',
                        message: response.data.message || 'News saved successfully',
                        type: 'success'
                    });

                    // Redirect after successful save
                    window.location.href = this.backRoute;
                } else {
                    throw new Error(response.data.message || 'Failed to save news');
                }

            } catch (error) {
                console.error('Error saving news:', error);
                this.$notify.error({
                    title: 'Error',
                    message: error.response?.data?.message || 'Failed to save news'
                });
            } finally {
                this.isSubmitting = false;
            }
        },

        // Image handling methods
        triggerInput() {
            this.$refs.fileInput.click();
        },

        handleFiles(e) {
            const files = e.target.files;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const fileData = {
                            file: file,
                            preview: event.target.result,
                            name: file.name
                        };
                        this.files.push(fileData);
                        if (!this.form.gallery || !Array.isArray(this.form.gallery)) {
                            this.form.gallery = [];
                        }
                        this.form.gallery.push(fileData);
                    };
                    reader.readAsDataURL(file);
                }
            }
        },

        handleDrop(event) {
            const dataTransfer = event.dataTransfer;
            if (dataTransfer.items) {
                const items = Array.from(dataTransfer.items);
                items.forEach(item => {
                    if (item.kind === 'file' && item.type.startsWith('image/')) {
                        const file = item.getAsFile();
                        this.addFile(file);
                    }
                });
            }
        },

        addFile(file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                const fileData = {
                    file: file,
                    preview: event.target.result,
                    name: file.name
                };
                this.files.push(fileData);
                if (!this.form.gallery || !Array.isArray(this.form.gallery)) {
                    this.form.gallery = [];
                }
                this.form.gallery.push(fileData);
            };
            reader.readAsDataURL(file);
        },

        removeFile(index) {
            this.files.splice(index, 1);
            if (this.form.gallery && Array.isArray(this.form.gallery)) {
                this.form.gallery.splice(index, 1);
            }
        },

        moveToFront(index) {
            if (index > 0) {
                const file = this.files.splice(index, 1)[0];
                this.files.unshift(file);
                if (this.form.gallery) {
                    this.form.gallery = [...this.files];
                }
            }
        },

        // Drag and drop reordering
        onDragStart(index) {
            this.dragIndex = index;
        },

        onDrop(dropIndex) {
            if (this.dragIndex === null) return;

            const draggedItem = this.files[this.dragIndex];
            this.files.splice(this.dragIndex, 1);
            this.files.splice(dropIndex, 0, draggedItem);

            if (this.form.gallery) {
                this.form.gallery = [...this.files];
            }

            this.dragIndex = null;
        },

        goBack() {
            window.location.href = this.backRoute;
        }
    }
}
</script>

<style scoped>
.upload-container {
    margin: 20px 0;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.drop-area {
    padding: 40px;
    border: 2px dashed #ccc;
    text-align: center;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.drop-area:hover {
    background-color: #f9f9f9;
}

.preview {
    display: flex;
    flex-wrap: wrap;
    margin-top: 20px;
    gap: 10px;
}

.thumbnail {
    position: relative;
    display: inline-block;
    cursor: move;
    border-radius: 5px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.img-thumbnail {
    width: 120px;
    height: 120px;
    object-fit: cover;
    display: block;
}

.remove {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #e74c3c;
    color: white;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 50%;
    font-size: 12px;
    font-weight: bold;
    line-height: 1;
}

.move-to-front {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 4px 6px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
}

.thumbnail-badge {
    position: absolute;
    top: 5px;
    left: 5px;
    background: #27ae60;
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: bold;
}

.form-group.dashed {
    border-bottom: 1px dashed #ddd;
    padding-bottom: 20px;
    margin-bottom: 20px;
}

.block {
    background: white;
    padding: 30px;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.control-label {
    font-weight: 600;
    color: #2c3e50;
}
</style>
