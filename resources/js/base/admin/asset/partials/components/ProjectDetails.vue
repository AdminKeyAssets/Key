<template>
    <div>

        <!-- Copy From Button -->
        <div class="form-group">
            <div @click="openModal" class="btn btn-primary">Copy From</div>
        </div>

        <!-- Modal for Selecting Asset -->
        <el-dialog title="Select Asset" :visible.sync="isModalVisible" width="30%">
            <el-select v-model="selectedAsset" placeholder="Select an asset">
                <el-option v-for="asset in assets" :key="asset.id" :label="asset.project_name" :value="asset.project_name"></el-option>
            </el-select>
            <span slot="footer" class="dialog-footer">
                <el-button @click="isModalVisible = false">Cancel</el-button>
                <el-button type="primary" @click="copyAsset">Confirm</el-button>
            </span>
        </el-dialog>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Upload Icon:</label>
            <div class="col-md-10 uppercase-medium">
                <div class="upload-container">
                    <!-- Drag and Drop Area -->
                    <div class="drop-area"
                         @dragover.prevent="dragOver"
                         @dragleave.prevent="dragLeave"
                         @drop.prevent="handleDrop"
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

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Project Name:</label>
            <div class="col-md-10 uppercase-medium">
                <input class="form-control" :disabled="loading" v-model="form.project_name"></input>
            </div>
        </div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Project Description:</label>
            <div class="col-md-10 uppercase-medium">
                <el-input
                    type="textarea"
                    autosize
                    placeholder="Project Description"
                    :disabled="loading"
                    v-model="form.project_description">
                </el-input>
            </div>
        </div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Project Link:</label>
            <div class="col-md-10 uppercase-medium">
                <input class="form-control" :disabled="loading" v-model="form.project_link"></input>
            </div>
        </div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">City:</label>
            <div class="col-md-10 uppercase-medium">
                <input class="form-control" :disabled="loading" v-model="form.city"></input>
            </div>
        </div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Address:</label>
            <div class="col-md-10 uppercase-medium">
                <input class="form-control" :disabled="loading" v-model="form.address"></input>
            </div>
        </div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Total Floors:</label>
            <div class="col-md-10 uppercase-medium">
                <input type="number" class="form-control" :disabled="loading" v-model="form.total_floors"></input>
            </div>
        </div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Location:</label>
            <div class="col-md-10 uppercase-medium">
                <el-input
                    type="textarea"
                    autosize
                    placeholder="Embeded link for location"
                    :disabled="loading"
                    v-model="form.location">
                </el-input>
            </div>
        </div>

        <div class="form-group dashed">
            <label class="col-md-1 control-label">Delivery Date:</label>
            <div class="col-md-10 uppercase-medium">
                <el-date-picker
                    v-model="form.delivery_date"
                    format="yyyy/MM/dd"
                    type="date"
                    value-format="yyyy/MM/dd"
                    placeholder="Pick a delivery date">
                </el-date-picker>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'; // Assuming you're using axios for HTTP requests
import MapMarker from "../../../../components/admin/MapMarker.vue";
import ImageModal from "../../../../components/admin/ImageModal.vue";

export default {
    components: {ImageModal, MapMarker},
    props: ['form', 'loading', 'updateData', 'item'],
    data() {
        return {
            files: [],
            isModalVisible: false,
            assets: [],
            selectedAsset: null
        };
    },
    watch: {
        'form'() {
            if (this.form.gallery) {
                this.files = this.form.gallery;
            }
        }
    },
    methods: {
        openModal() {
            this.isModalVisible = true;
            this.fetchAssets();
        },
        fetchAssets() {
            axios.get('/assets/names')
                .then(response => {
                    this.assets = response.data.data.assets; // Assuming the API returns an array of asset names
                })
                .catch(error => {
                    console.error('Error fetching assets:', error);
                });
        },
        copyAsset() {
            if (this.selectedAsset) {
                axios.post(`/assets/clone/${this.selectedAsset}`)
                    .then(response => {
                        const data = response.data.data;
                        const asset = data.asset;
                        const gallery = data.gallery;

                        // Assign values to the existing form fields
                        if (asset.project_name) this.form.project_name = asset.project_name;
                        if (asset.project_description) this.form.project_description = asset.project_description;
                        if (asset.project_link) this.form.project_link = asset.project_link;
                        if (asset.city) this.form.city = asset.city;
                        if (asset.address) this.form.address = asset.address;
                        if (asset.total_floors) this.form.total_floors = asset.total_floors;
                        if (asset.location) this.form.location = asset.location;
                        if (asset.delivery_date) this.form.delivery_date = asset.delivery_date;
                        // if (gallery) this.files = gallery;
                        if (gallery) {
                            this.form.gallery = gallery;
                            this.files = gallery.map(file => ({
                                file: file,
                                preview: file.preview || file.image || '',
                                name: file.name || file.fileName || ''
                            }));
                        }

                        this.$emit('update-form', {...this.form});

                        this.$emit('update-form', this.form);
                        this.isModalVisible = false;
                    })
                    .catch(error => {
                        console.error('Error cloning asset:', error);
                    });
            }
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
                        gallery: [...(Array.isArray(this.form.gallery) ? this.form.gallery : []), {
                            file: file,
                            preview: file.type.startsWith('image/') ? e.target.result : null,
                            name: file.name,
                        }]
                    });
                };
                reader.readAsDataURL(file);
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
        dragOver() {
            // Add any visual cues for dragging over the area
        },
        dragLeave() {
            // Handle cleanup of visual cues
        },
        addFile(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.files.push({
                    name: file.name,
                    url: e.target.result
                });
            };
            reader.readAsDataURL(file);
        },
        removeFile(index) {
            this.files.splice(index, 1);
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
