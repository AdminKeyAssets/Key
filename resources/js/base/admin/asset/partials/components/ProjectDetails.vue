<template>
    <div>
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Upload Icon:</label>
            <div class="col-md-10 uppercase-medium">
                <input type="file" @change="onIconChange" accept="image/*">
                <div v-if="form.icon">
                    <ImageModal v-if="form.iconPreview"
                                :image-path="form.iconPreview"
                                :thumbnail="form.iconPreview"></ImageModal>
                    <ImageModal v-else
                                :image-path="form.icon"
                                :thumbnail="form.icon"></ImageModal>
                    <el-button icon="el-icon-delete-solid" size="small" type="danger"
                               @click="removeIcon"></el-button>
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
            <MapMarker
                v-if="item.location || !item.id"
                :update-data="updateData"
                :item="item"
            ></MapMarker>
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
import MapMarker from "../../../../components/admin/MapMarker.vue";
import ImageModal from "../../../../components/admin/ImageModal.vue";

export default {
    components: { ImageModal, MapMarker },
    props: ['form', 'loading', 'updateData', 'item'],
    methods: {
        onIconChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.$emit('update-form', { ...this.form, icon: file, iconPreview: e.target.result });
                };
                reader.onerror = (error) => {
                    console.error('Error loading file:', error);
                };
                reader.readAsDataURL(file);
            }
        },
        removeIcon() {
            this.$emit('update-form', { ...this.form, icon: null, iconPreview: null });
        },
    }
}
</script>
