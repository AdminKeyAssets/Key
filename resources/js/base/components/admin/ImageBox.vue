<template>
    <div class="image-box">
        <CustomImagePreview :mainImage="mainImage" :images="srcList" @update-main-image="setMainImage" />
        <div class="thumbnail-carousel-container">
            <span @click="prevImage" class="carousel-button prev-button">❮</span>
            <div
                class="thumbnail-carousel"
                @mousedown="onMouseDown"
                @mousemove="onMouseMove"
                @mouseup="onMouseUp"
                @mouseleave="onMouseUp"
                @touchstart="onTouchStart"
                @touchmove="onTouchMove"
                @touchend="onTouchEnd"
            >
                <div
                    v-for="(image, index) in loopedImages"
                    :key="index"
                    class="thumbnail-item"
                    :style="{ transform: `translateX(${currentTranslate}px)` }"
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
            isDragging: false,
            mouseStartX: 0,
            mouseEndX: 0,
            isMouseDragging: false,
            currentTranslate: 0,
            prevTranslate: 0,
            startPos: 0,
            animationFrameId: null
        };
    },
    computed: {
        loopedImages() {
            return [...this.srcList, ...this.srcList, ...this.srcList]; // Triplicate the list to simulate infinite loop
        }
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
            this.currentIndex--;
            if (this.currentIndex < 0) {
                this.currentIndex = this.srcList.length - 1;
                this.currentTranslate = -this.srcList.length * 100; // Jump to the middle copy
            }
            this.currentTranslate += 100;
        },
        nextImage() {
            this.currentIndex++;
            if (this.currentIndex >= this.srcList.length) {
                this.currentIndex = 0;
                this.currentTranslate = -this.srcList.length * 100; // Jump to the middle copy
            }
            this.currentTranslate -= 100;
        },
        onTouchStart(event) {
            this.touchStartX = event.changedTouches[0].screenX;
            this.startPos = this.touchStartX;
            this.isDragging = true;
            this.prevTranslate = this.currentTranslate;
        },
        onTouchMove(event) {
            if (this.isDragging) {
                const currentPosition = event.changedTouches[0].screenX;
                const delta = currentPosition - this.startPos;
                this.currentTranslate = this.prevTranslate + delta;
            }
        },
        onTouchEnd(event) {
            if (this.isDragging) {
                this.touchEndX = event.changedTouches[0].screenX;
                const movedBy = this.touchStartX - this.touchEndX;
                if (movedBy > 50) {
                    this.nextImage();
                } else if (movedBy < -50) {
                    this.prevImage();
                }
                this.isDragging = false;
                this.prevTranslate = this.currentTranslate;
            }
        },
        onMouseDown(event) {
            this.mouseStartX = event.clientX;
            this.startPos = this.mouseStartX;
            this.isMouseDragging = true;
            this.prevTranslate = this.currentTranslate;
            event.preventDefault(); // Prevent text selection while dragging
        },
        onMouseMove(event) {
            if (this.isMouseDragging) {
                const currentPosition = event.clientX;
                const delta = currentPosition - this.startPos;
                this.currentTranslate = this.prevTranslate + delta;
            }
        },
        onMouseUp(event) {
            if (this.isMouseDragging) {
                this.mouseEndX = event.clientX;
                const movedBy = this.mouseStartX - this.mouseEndX;
                if (movedBy > 50) {
                    this.nextImage();
                } else if (movedBy < -50) {
                    this.prevImage();
                }
                this.isMouseDragging = false;
                this.prevTranslate = this.currentTranslate;
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
    cursor: grab; /* Indicate draggable area */
}

.thumbnail-carousel:active {
    cursor: grabbing; /* Indicate active dragging */
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
    transition: border-color 0.3s;
}

.thumbnail.active {
    border-color: #000;
}
</style>
