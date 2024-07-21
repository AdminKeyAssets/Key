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
            const currentIndex = this.images.indexOf(this.mainImage);
            const prevIndex = (currentIndex - 1 + this.images.length) % this.images.length;
            this.$emit('update-main-image', this.images[prevIndex]);
        },
        nextImage() {
            const currentIndex = this.images.indexOf(this.mainImage);
            const nextIndex = (currentIndex + 1) % this.images.length;
            this.$emit('update-main-image', this.images[nextIndex]);
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
    height: 400px; /* Fixed height */
    margin-bottom: 20px;
}

.main-image img {
    width: 100%;
    height: auto;
    cursor: pointer;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease;
}

.main-image img:hover {
    transform: scale(1.05);
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
}

.image-preview img {
    max-width: 90vw;
    max-height: 90vh;
    border-radius: 10px;
}

.close-button, .nav-button {
    position: absolute;
    background: none;
    border: none;
    font-size: 2em;
    cursor: pointer;
    color: black;
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
    left: 10px;
}

.next-button {
    right: 10px;
}
</style>
