<template>
    <div class="image-box">
        <CustomImagePreview :mainImage="mainImage" :images="srcList" @update-main-image="setMainImage" />
        <div class="thumbnail-carousel-container">
            <span @click="prevImage" class="carousel-button prev-button">❮</span>
            <div
                class="thumbnail-carousel"
                @touchstart="onTouchStart"
                @touchmove="onTouchMove"
                @touchend="onTouchEnd"
            >
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
                    />
                </div>
            </div>
            <span @click="nextImage" class="carousel-button next-button">❯</span>
        </div>
    </div>
</template>

<script>
import CustomImagePreview from './CustomImagePreview.vue';

export default {
    name: 'ImageBox',
    components: {
        CustomImagePreview
    },
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
            srcList: [],
            touchStartX: 0,
            touchEndX: 0,
            isDragging: false
        };
    },
    created() {
        if (this.images) {
            this.images.forEach((image) => {
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
        },
        onTouchStart(event) {
            this.touchStartX = event.changedTouches[0].screenX;
            this.isDragging = true;
        },
        onTouchMove(event) {
            if (this.isDragging) {
                this.touchEndX = event.changedTouches[0].screenX;
            }
        },
        onTouchEnd() {
            if (this.isDragging) {
                if (this.touchStartX - this.touchEndX > 50) {
                    this.nextImage();
                } else if (this.touchStartX - this.touchEndX < -50) {
                    this.prevImage();
                }
                this.isDragging = false;
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
