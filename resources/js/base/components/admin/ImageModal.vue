<template>
    <div>
        <img v-if="this.rounded" :style="getImageStyle()" :width="this.width" :height="this.height" :src="this.thumbnail" alt="Image thumbnail" @click="showModal = true" style="border-radius: 50%; cursor: pointer; ">
        <img v-else :style="getImageStyle()" :src="this.thumbnail" :width="this.width" :height="this.height" alt="Image thumbnail" @click="showModal = true" style="cursor: pointer; ">
        <el-dialog :visible.sync="showModal" :append-to-body="true" width="100%" :before-close="handleClose">
            <img :src="imagePath" alt="Image full view" style="width: 100%;">
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
        fullscreen:{
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
/* Add any additional styles here */
</style>
