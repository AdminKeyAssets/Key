<template>
    <div>
        <img
            v-if="rounded"
            :style="getImageStyle()"
            :width="width"
            :height="height"
            :src="thumbnail"
            alt="Image thumbnail"
            @click="showModal = true"
            style="border-radius: 50%; cursor: pointer;">
        <img
            v-else
            :style="getImageStyle()"
            :src="thumbnail"
            :width="width"
            :height="height"
            alt="Image thumbnail"
            @click="showModal = true"
            style="cursor: pointer;">
        <el-dialog
            top="5vh"
            :visible.sync="showModal"
            :append-to-body="true"
            width="fit-content"
            :before-close="handleClose"
            custom-class="transparent-dialog">
            <div class="image-container">
                <img :src="imagePath" alt="Image full view">
            </div>
        </el-dialog>
    </div>
</template>

<script>
export default {
    name: 'ImageModal',
    props: {
        imagePath: {
            type: String,
            required: false,
            default: ''
        },
        thumbnail: {
            type: String,
            required: false,
            default: ''
        },
        rounded: {
            type: Boolean,
            required: false,
            default: false
        },
        width: {
            type: Number,
            required: false
        },
        height: {
            type: Number,
            required: false
        },
        fullscreen: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            showModal: false
        };
    },
    methods: {
        handleClose(done) {
            this.showModal = false;
            done();
        },
        getImageStyle() {
            let style = {};
            if (this.width) {
                style.width = `${this.width}px`; // Set width if provided
            }
            if (this.height) {
                style.height = `${this.height}px`; // Set height if provided
            }
            return style;
        }
    }
};
</script>

<style scoped>
.image-container {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 80vw;
    background-color: white; /* White background for empty spaces */
}

.image-container img {
    width: 100%;
    max-width: 100%;
    max-height: 100%;
    object-fit: contain; /* Ensures the image maintains its aspect ratio and fits within the container */
}

.transparent-dialog .el-dialog__body {
    background-color: transparent;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 80vw;
    margin: auto; /* Center the dialog */
}

@media (min-width: 769px) {
    .transparent-dialog .el-dialog__body, .image-container{
        height: 70vh;
    }
}

@media (max-width: 768px) {
    .transparent-dialog .el-dialog__body, .image-container{
        height: 30vh;
    }
    .el-dialog.transparent-dialog {
        margin-top: 15vh !important;
    }
}

.transparent-dialog .el-dialog__wrapper {
    width: fit-content; /* Dialog width is fit-content */
}

.transparent-dialog .el-dialog {
    margin: auto; /* Center the dialog */
}

.transparent-dialog .el-dialog__header,
.transparent-dialog .el-dialog__footer {
    display: none; /* Hide the header and footer if not needed */
}
</style>
