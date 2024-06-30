<template>
    <div>
        <img v-if="this.rounded" :width="this.width" :height="this.height" :src="this.thumbnail" alt="Image thumbnail" @click="showModal = true" style="border-radius: 50%; cursor: pointer; ">
        <img v-else :src="this.thumbnail" :width="this.width" :height="this.height" alt="Image thumbnail" @click="showModal = true" style="cursor: pointer; ">
        <el-dialog :visible.sync="showModal" width="50%" :before-close="handleClose">
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
            required: false,
            default: 50
        },
        height: {
            type: Number,
            required: false,
            default: 50
        },
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
        }
    }
};
</script>

<style scoped>
/* Add any additional styles here */
</style>
