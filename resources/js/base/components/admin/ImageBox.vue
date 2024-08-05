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
                    v-for="(imageItem, index) in srcList"
                    :key="imageItem.id"
                    :class="['thumbnail-item',{ 'active': imageItem.image === mainImage }]"
                >
                    <img
                        :src="imageItem.image"
                        alt="Thumbnail"
                        class="carouser-thumbnail-item"
                        @click="setMainImage(imageItem.image)"
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
    created() {
        if (this.images) {
            this.srcList = [...this.images];
        }
    },
    methods: {
        setMainImage(image) {
            this.mainImage = image;
        },
        prevImage() {
            const lastItem = this.srcList.pop();
            this.srcList.unshift(lastItem);
            this.currentTranslate = -100;
            this.animateSlide(100);
        },
        nextImage() {
            const firstItem = this.srcList.shift();
            this.srcList.push(firstItem);
            this.currentTranslate = 0;
            this.animateSlide(-100);
        },
        animateSlide(offset) {
            this.currentTranslate += offset;
            this.prevTranslate = this.currentTranslate;
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
    display: flex;
    overflow: hidden;
    width: 100%;
    cursor: grab; /* Indicate draggable area */
}

.thumbnail-carousel:active {
    cursor: grabbing; /* Indicate active dragging */
}

.thumbnail-item {
    //min-width: 100px;
    height: 104px;
    transition: transform 0.5s;
}

.carouser-thumbnail-item {
    width: 165px;
    height: auto; /* Fixed height for thumbnails */
    max-height: 100px;
    object-fit: cover; /* Ensure thumbnails cover the fixed height */
    cursor: pointer;
    border-radius: 5px;
    transition: border-color 0.3s;
}

.thumbnail-item.active {
    border-color: #000;
}

.thumbnail-item{
    border: 2px solid transparent;
    border-radius: 10px;
    transition: border-color 0.3s;
}
</style>
