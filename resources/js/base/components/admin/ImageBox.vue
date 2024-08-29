<template>
    <div class="image-box">
        <CustomImagePreview :mainImage="mainImage" :images="srcList" @update-main-image="setMainImage" />
        <div class="thumbnail-carousel-container">
            <!-- Previous Button -->
            <span @click="slidePrev" class="carousel-button prev-button">❮</span>

            <!-- Swiper Element for Thumbnails -->
            <swiper-container
                ref="swiperRef"
                class="thumbnail-carousel"
                slides-per-view="2"
                space-between="10"
                free-mode
                loop
            >
                <swiper-slide
                    v-for="(imageItem, index) in srcList"
                    :key="imageItem.id"
                    :class="['thumbnail-item', { 'active': imageItem.image === mainImage }]"
                >
                    <img
                        :src="imageItem.image"
                        alt="Thumbnail"
                        class="carousel-thumbnail-item"
                        @click="setMainImage(imageItem.image)"
                    />
                </swiper-slide>
            </swiper-container>

            <!-- Next Button -->
            <span @click="slideNext" class="carousel-button next-button">❯</span>
        </div>
    </div>
</template>

<script>
// Importing the Swiper Web Component
import 'swiper/swiper-bundle.css';
import 'swiper/swiper-element-bundle.min.js';

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
            srcList: []
        };
    },
    created() {
        if (this.images) {
            this.srcList = [...this.images];
        }
    },
    methods: {
        setMainImage(image) {
            this.mainImage = image;
        },
        slidePrev() {
            this.$refs.swiperRef.swiper.slidePrev();
        },
        slideNext() {
            this.$refs.swiperRef.swiper.slideNext();
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
    z-index: 10;
}

.carousel-button:hover {
    background-color: rgba(0, 0, 0, 0.8);
}

.prev-button {
    position: absolute;
    left: -20px;
    width: 40px;
    height: 40px;
    line-height: 18px;
    border-radius: 50%;
    text-align: center;
}

.next-button {
    position: absolute;
    right: -20px;
    width: 40px;
    height: 40px;
    line-height: 18px;
    border-radius: 50%;
    text-align: center;
}

.thumbnail-carousel {
    width: 100%;
    overflow: hidden;
}

.thumbnail-item {
    height: 104px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.5s;
}

.carousel-thumbnail-item {
    width: 100%;
    height: auto;
    max-height: 100px;
    object-fit: cover;
    border-radius: 5px;
    cursor: pointer;
    transition: border-color 0.3s;
}

.thumbnail-item.active {
    border-color: #000;
}

.thumbnail-item {
    border: 2px solid transparent;
    border-radius: 10px;
    transition: border-color 0.3s;
}
</style>
