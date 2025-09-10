<template>
    <div>
        <div class="block">
            <div class="form-horizontal form-bordered">

                <div v-if="loading" class="text-center">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p>Loading news...</p>
                </div>

                <div v-else-if="news" class="news-content">
                    <!-- Status -->
                    <div class="form-group dashed">
                        <div class="col-md-6">
                            <div class="status-section">
                                <span v-if="news.status === 'published'" class="badge badge-success">Published</span>
                                <span v-else class="badge badge-warning">Draft</span>
                            </div>
                        </div>
                    </div>

                    <!-- Attached Investors (hidden for investor users) -->
                    <div v-if="!isInvestor && news.investors && news.investors.length > 0" class="form-group dashed">
                        <div class="col-md-12">
                            <div class="investor-list">
                                <span v-for="investor in news.investors" :key="investor.id" class="investor-badge">
                                    {{ investor.full_name || (investor.name + ' ' + investor.surname) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Images Gallery -->
                    <div v-if="news.images && news.images.length > 0" class="form-group dashed">
                        <div class="col-md-12">
                            <!-- Single image display -->
                            <div v-if="news.images.length === 1" class="single-image-container">
                                <img :src="news.images[0].image"
                                     :alt="news.title"
                                     class="single-news-image"
                                     @click="openImageModal(news.images[0].image)">
                            </div>
                            <!-- Multiple images - use ImageBox -->
                            <ImageBox v-else
                                :slides-count="3"
                                :initial-main-image="news.images[0].image"
                                :images="news.images"
                                :align-left="false">
                            </ImageBox>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="form-group dashed">
                        <div class="col-md-12">
                            <div class="content-area" v-html="news.content"></div>
                        </div>
                    </div>

                </div>

                <div v-else class="text-center">
                    <h4>News not found</h4>
                </div>
            </div>
        </div>

        <!-- Image Modal -->
        <!--<el-dialog :visible.sync="imageModalVisible" class="image-modal">
            <img :src="selectedImage" alt="News Image" style="width: 100%; height: auto;">
        </el-dialog>-->
    </div>
</template>

<script>
import axios from 'axios';
import ImageBox from '../../components/admin/ImageBox.vue';

export default {
    components: {
        ImageBox
    },
    props: ['newsId', 'getDataRoute', 'backRoute', 'isInvestor'],
    data() {
        return {
            loading: false,
            news: null
        };
    },
    mounted() {
        this.loadNews();
    },
    methods: {
    async loadNews() {
        this.loading = true;
        try {
            const response = await axios.post(this.getDataRoute, {
                id: this.newsId
            });
            if (response.data.data.item) {
                this.news = response.data.data.item;

                // Emit event to refresh notification count if this is an investor viewing news
                if (this.isInvestor) {
                    // Add a small delay to ensure database update completes
                    setTimeout(() => {
                        this.$root.$emit('refresh-news-count');
                    }, 500);
                }
            } else {
                this.$notify.error({
                    title: 'Error',
                    message: 'News not found'
                });
            }
        } catch (error) {
            console.error('Error loading news:', error);
            this.$notify.error({
                title: 'Error',
                message: 'Failed to load news'
            });
        } finally {
            this.loading = false;
        }
    },

        openImageModal(imageSrc) {
            // Simple image modal - you can enhance this with a proper modal component
            window.open(imageSrc, '_blank');
        },

        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleString();
        },

        goBack() {
            window.location.href = this.backRoute;
        },

        goToEdit() {
            window.location.href = this.backRoute.replace('/news', `/news/edit/${this.newsId}`);
        }
    }
}
</script>

<style scoped>
.block {
    background: white;
    padding: 30px;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-group.dashed {
    border-bottom: 1px dashed #ddd;
    padding-bottom: 20px;
    margin-bottom: 20px;
}

.control-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
    display: block;
}

.news-content h3 {
    color: #2c3e50;
    margin: 0;
    font-weight: 600;
}

.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-success {
    background-color: #27ae60;
    color: white;
}

.badge-warning {
    background-color: #f39c12;
    color: white;
}

.investor-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.investor-badge {
    background-color: #3498db;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.content-area {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 5px;
    border: 1px solid #e9ecef;
    min-height: 200px;
    line-height: 1.6;
}

.content-area >>> h1,
.content-area >>> h2,
.content-area >>> h3,
.content-area >>> h4,
.content-area >>> h5,
.content-area >>> h6 {
    color: #2c3e50;
    margin-top: 0;
}

.content-area >>> p {
    margin-bottom: 15px;
}

.content-area >>> img {
    max-width: 100%;
    height: auto;
    border-radius: 5px;
}

.content-area >>> blockquote {
    border-left: 4px solid #3498db;
    margin: 20px 0;
    padding: 10px 20px;
    background-color: #ecf0f1;
}

.content-area >>> table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

.content-area >>> table th,
.content-area >>> table td {
    border: 1px solid #bdc3c7;
    padding: 8px 12px;
    text-align: left;
}

.content-area >>> table th {
    background-color: #34495e;
    color: white;
}

.status-section {
    margin-bottom: 10px;
}

.single-image-container {
    display: flex;
    justify-content: center;
    margin: 20px 0;
}

.single-news-image {
    max-width: 100%;
    max-height: 400px;
    width: auto;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.single-news-image:hover {
    transform: scale(1.02);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
}
</style>
