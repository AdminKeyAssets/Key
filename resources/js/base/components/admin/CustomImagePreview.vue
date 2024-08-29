<template>
    <div>
        <div class="main-image" @click="showPreview = true">
            <img :src="mainImage" alt="Main Image" />
        </div>
        <div v-if="showPreview" class="overlay" @click.self="closePreview" @touchstart="onTouchStart" @touchmove="onTouchMove" @touchend="onTouchEnd">
            <div class="image-preview">
                <span class="nav-button prev-button" @click.stop="prevImage">❮</span>
                <img :src="mainImage" alt="Full Image" />
                <span class="nav-button next-button" @click.stop="nextImage">❯</span>
                <button class="close-button" @click="closePreview">✖</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CustomImagePreview',
    props: {
        images: {
            type: Array,
            required: true
        },
        mainImage: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            showPreview: false,
            touchStartX: 0,
            touchEndX: 0
        };
    },
    watch: {
        showPreview(value) {
            if (value) {
                document.addEventListener('keydown', this.handleEscPress);
            } else {
                document.removeEventListener('keydown', this.handleEscPress);
            }
        }
    },
    methods: {
        closePreview() {
            this.showPreview = false;
        },
        handleEscPress(event) {
            if (event.key === 'Escape') {
                this.closePreview();
            }
        },
        prevImage() {
            const currentIndex = this.images.findIndex(image => image.image === this.mainImage);
            const prevIndex = (currentIndex - 1 + this.images.length) % this.images.length;
            this.$emit('update-main-image', this.images[prevIndex].image);
        },
        nextImage() {
            const currentIndex = this.images.findIndex(image => image.image === this.mainImage);
            const nextIndex = (currentIndex + 1) % this.images.length;
            this.$emit('update-main-image', this.images[nextIndex].image);
        },
        onTouchStart(event) {
            this.touchStartX = event.changedTouches[0].screenX;
        },
        onTouchMove(event) {
            this.touchEndX = event.changedTouches[0].screenX;
        },
        onTouchEnd() {
            if (this.touchStartX - this.touchEndX > 50) {
                this.nextImage();
            } else if (this.touchStartX - this.touchEndX < -50) {
                this.prevImage();
            }
        }
    },
    beforeDestroy() {
        document.removeEventListener('keydown', this.handleEscPress);
    }
};
</script>

<style scoped>
.main-image {
    width: 100%;
    max-width: 600px;
    height: 280px; /* Fixed height */
    margin-bottom: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Ensure the image covers the container without stretching */
    cursor: pointer;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.image-preview {
    position: relative;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    display: flex;
    justify-content: center;
    align-items: center;
    width: 90vw;
    max-width: 800px;
    height: auto;
}

.image-preview img {
    width: 100%; /* Make the image take up the full width */
    height: auto;
    max-height: 70vh; /* Adjust this value to control the height */
    object-fit: contain; /* Ensure the image is fully visible without distortion */
    border-radius: 10px;
}

.close-button, .nav-button {
    position: absolute;
    background: rgba(0, 0, 0, 0.5); /* Matching the thumbnail slider button style */
    border: none;
    font-size: 1.5em;
    color: white;
    cursor: pointer;
    padding: 10px;
    border-radius: 50%;
    transition: background-color 0.3s;
}

.nav-button:hover, .close-button:hover {
    background: rgba(0, 0, 0, 0.8); /* Darker background on hover */
}

.close-button {
    top: 10px;
    right: 10px;
}

.nav-button {
    top: 50%;
    transform: translateY(-50%);
}

.prev-button {
    left: -20px;
}

.next-button {
    right: -20px;
}
</style>
