<template>
    <div class="block">
        <el-form v-loading="loading"
                 element-loading-text="Loading..."
                 element-loading-spinner="el-icon-loading"
                 element-loading-background="rgba(0, 0, 0, 0.8)"
                 ref="form" :model="form" class="form-horizontal form-bordered">

            <el-row>

                <div class="form-group">
                    <label class="col-md-2 control-label">Title: <span class="text-danger">*</span></label>
                    <div class="col-md-10">
                        <el-input class="el-input--is-round"
                            maxlength="255"
                            show-word-limit
                            :disabled="loading || isSubmitting"
                            v-model="form.title"
                            placeholder="Enter news title" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label">Content: <span class="text-danger">*</span></label>
                    <div class="col-md-10">
                        <div class="editor-container">
                            <ckeditor
                                ref="ckeditor"
                                :editor="editor"
                                v-model="form.content"
                                :config="editorConfig">
                            </ckeditor>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label">Status:</label>
                    <div class="col-md-6">
                        <el-select v-model="form.status" :disabled="loading || isSubmitting" v-remove-readonly>
                            <el-option label="Draft" value="draft"></el-option>
                            <el-option label="Published" value="published"></el-option>
                        </el-select>
                    </div>
                </div>

                <!-- Manager Selection (only for administrators) -->
                <div v-if="canAssignManager" class="form-group">
                    <label class="col-md-2 control-label">Assign Manager:</label>
                    <div class="col-md-6">
                        <el-select
                            v-model="form.manager_id"
                            :disabled="loading || isSubmitting"
                            placeholder="Select a manager (optional)"
                            v-remove-readonly
                            clearable>
                            <el-option
                                v-for="manager in managers"
                                :key="manager.id"
                                :label="manager.full_name || (manager.name + ' ' + manager.surname)"
                                :value="manager.id">
                            </el-option>
                        </el-select>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Assign a manager to this news article</small>
                    </div>
                </div>

                <!-- Investor Selection -->
                <div v-if="showInvestorSelection" class="form-group">
                    <label class="col-md-2 control-label">Attach to Investors:</label>
                    <div class="col-md-6">
                        <el-select
                            v-model="form.investor_ids"
                            :disabled="loading || isSubmitting"
                            multiple
                            filterable
                            v-remove-readonly
                            placeholder="Select investors">
                            <el-option
                                v-for="investor in investors"
                                :key="investor.id"
                                :label="investor.full_name || (investor.name + ' ' + investor.surname)"
                                :value="investor.id">
                            </el-option>
                        </el-select>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">
                            {{ isDeveloper ? 'Select investors from your assets' : 'Select which investors should receive this news' }}
                        </small>
                    </div>
                </div>

                <!-- Image Gallery Section -->
                <div class="form-group">
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
                        <button @click="saveNews" class="btn btn-primary" :disabled="loading || isSubmitting">
                            <i v-if="isSubmitting" class="fa fa-spinner fa-spin"></i>
                            {{ isSubmitting ? 'Saving...' : (form.id ? 'Update News' : 'Create News') }}
                        </button>
                    </div>
                </div>

            </el-row>
        </el-form>
    </div>
</template>

<script>
import axios from 'axios';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {
    props: ['newsId', 'getSaveDataRoute', 'saveRoute', 'backRoute', 'isAdmin', 'isDeveloper', 'isUpdate'],
    data() {
        return {
            loading: false,
            isSubmitting: false,
            editor: ClassicEditor,
            editorConfig: {
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'link', '|',
                    'bulletedList', 'numberedList', '|',
                    'indent', 'outdent', '|',
                    'blockQuote', 'insertTable', '|',
                    'undo', 'redo'
                ],
                placeholder: 'Enter news content...'
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
        this.$nextTick(() => {
            this.loadData();
        });
    },
    computed: {
        editorDisabled() {
            return this.loading || this.isSubmitting;
        },
        canAssignManager() {
            return this.isAdmin && this.managers && this.managers.length > 0;
        },
        showInvestorSelection() {
            return this.isAdmin || this.isDeveloper;
        }
    },
    methods: {
        async loadData() {
            this.loading = true;
            try {
                const response = await axios.post(this.getSaveDataRoute, {
                    id: this.newsId
                });

                if (response.status == 200) {
                    const data = response.data.data;

                    this.investors = data.investors || [];
                    this.managers = data.managers || [];

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
                                ...file,
                                preview: file.preview || file.image || '',
                                name: file.name || file.fileName || ''
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

                // Handle gallery files - only send new file uploads
                const newFiles = this.files.filter(file => file.file);
                if (newFiles.length > 0) {
                    newFiles.forEach((file, index) => {
                        formData.append(`gallery[${index}]`, file.file);
                    });
                }

                // Send existing image IDs/URLs separately if needed
                const existingImages = this.files.filter(file => !file.file && (file.id || file.image));
                if (existingImages.length > 0) {
                    formData.append('existing_images', JSON.stringify(existingImages.map(img => ({
                        id: img.id,
                        image: img.image,
                        name: img.name
                    }))));
                }

                const response = await axios.post(this.saveRoute, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                if (response.status == 200) {
                    // Handle both admin (ServiceResponse) and developer response structures
                    const isSuccess = response.data.status === true || response.data.success === true;
                    const message = response.data.message || 
                                  (response.data.data && response.data.data.message) || 
                                  'News saved successfully';
                    
                    if (isSuccess) {
                        this.$notify({
                            title: 'Success',
                            message: message,
                            type: 'success'
                        });

                        // Redirect to news index page after successful save
                        setTimeout(() => {
                            if (response.data.redirect) {
                                window.location.href = response.data.redirect;
                            } else if (this.backRoute) {
                                window.location.href = this.backRoute;
                            } else {
                                // Fallback redirect logic based on user type
                                if (this.isDeveloper) {
                                    window.location.href = '/developer/news';
                                } else {
                                    window.location.href = '/admin/news';
                                }
                            }
                        }, 1500); // Wait 1.5 seconds to show success message
                    } else {
                        throw new Error(message);
                    }
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

.editor-loading {
    padding: 40px;
    text-align: center;
    color: #666;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.editor-container {
    min-height: 200px;
}
</style>
