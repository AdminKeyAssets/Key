<template>
    <div class="image-box">
        <div class="main-image">
            <el-image
                :close-on-click-modal="true"
                fit="contain"
                :src="mainImage"
                :preview-src-list="srcList">
            </el-image>
        </div>
        <div class="thumbnail-carousel-container">
            <span @click="prevImage" class="carousel-button prev-button">❮</span>
            <div class="thumbnail-carousel">
                <div
                    v-for="(image, index) in srcList"
                    :key="index"
                    class="thumbnail-item"
                    :style="{ transform: `translateX(${-currentIndex * 100}%)` }"
                >
                    <img
                        :src="image"
                        alt="Thumbnail"
                        class="thumbnail"
                        @click="setMainImage(image)"
                        :class="{ 'active': image === mainImage }"
                    >
                </div>
            </div>
            <span @click="nextImage" class="carousel-button next-button">❯</span>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ImageBox',
    props: {
        images: {
            type: Array,
            required: true
        },
        initialMainImage: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            mainImage: this.initialMainImage,
            currentIndex: 0,
            srcList: []
        };
    },
    created() {
        if (this.images){
            this.images.forEach((image, index) => {
                this.srcList.push(image.image);
            });
        }
    },
    methods: {
        setMainImage(image) {
            this.mainImage = image;
        },
        prevImage() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
            }
        },
        nextImage() {
            if (this.currentIndex < this.images.length - 3) { // Assuming we show 3 thumbnails at a time
                this.currentIndex++;
            }
        }
    }
};
</script>

<style scoped>
.image-box {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.main-image {
    width: 100%;
    max-width: 600px;
    height: 400px; /* Fixed height */
    margin-bottom: 20px;
}

.main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Ensure the image covers the fixed height */
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease;
}

.main-image img:hover {
    transform: scale(1.05);
}

.thumbnail-carousel-container {
    display: flex;
    align-items: center;
    position: relative;
    width: 100%;
    max-width: 600px;
}

.carousel-button {
    background-color: rgba(0, 0, 0, 0.5);
    border: none;
    color: white;
    font-size: 1.5em;
    cursor: pointer;
    padding: 10px;
    transition: background-color 0.3s;
}

.carousel-button:hover {
    background-color: rgba(0, 0, 0, 0.8);
}

.prev-button {
    position: absolute;
    left: -40px;
}

.next-button {
    position: absolute;
    right: -40px;
}

.thumbnail-carousel {
    display: flex;
    overflow: hidden;
    width: 100%;
}

.thumbnail-item {
    min-width: 100px;
    transition: transform 0.5s;
}

.thumbnail {
    width: 100px;
    height: 80px; /* Fixed height for thumbnails */
    object-fit: cover; /* Ensure thumbnails cover the fixed height */
    cursor: pointer;
    border: 2px solid transparent;
    border-radius: 5px;
    transition: border-color 0.3s, transform 0.3s;
}

.thumbnail.active {
    border-color: #000;
}

.thumbnail:hover {
    transform: scale(1.1);
}
</style>
