<template>
    <div>
        <img v-if="this.rounded" width="50" height="50" :src="this.thumbnail" alt="Image thumbnail" @click="showModal = true" style="border-radius: 50%; cursor: pointer; max-width: 100px; max-height: 100px;">
        <img v-else :src="this.thumbnail" alt="Image thumbnail" @click="showModal = true" style="cursor: pointer; max-width: 100px; max-height: 100px;">
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
        }
    }
};
</script>

<style scoped>
/* Add any additional styles here */
</style>
