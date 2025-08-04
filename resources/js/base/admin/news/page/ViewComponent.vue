<template>
    <div class="block">
        <el-form v-loading="loading"
                 element-loading-text="Loading..."
                 element-loading-spinner="el-icon-loading"
                 element-loading-background="rgba(0, 0, 0, 0.8)"
                 ref="form" :model="form" class="form-horizontal form-bordered">

            <el-row>
                <!-- Title -->
                <div class="form-group">
                    <label class="col-md-2 control-label">{{ lang.title }}:</label>
                    <div class="col-md-8">
                        <h3 class="news-title">{{ form.title }}</h3>
                    </div>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label class="col-md-2 control-label">{{ lang.status }}:</label>
                    <div class="col-md-4">
                        <el-tag 
                            :type="getStatusType(form.status)"
                            size="medium">
                            {{ getStatusLabel(form.status) }}
                        </el-tag>
                    </div>
                </div>

                <!-- Manager -->
                <div class="form-group">
                    <label class="col-md-2 control-label">{{ lang.manager }}:</label>
                    <div class="col-md-6">
                        {{ form.admin ? form.admin.name + ' ' + form.admin.surname : 'N/A' }}
                    </div>
                </div>

                <!-- Image -->
                <div class="form-group" v-if="form.image">
                    <label class="col-md-2 control-label">{{ lang.image }}:</label>
                    <div class="col-md-8">
                        <ImageModal 
                            :thumbnail="getImageUrl(form.image)"
                            :image-path="getImageUrl(form.image)"
                            :title="form.title">
                        </ImageModal>
                    </div>
                </div>

                <!-- Content -->
                <div class="form-group">
                    <label class="col-md-2 control-label">{{ lang.content }}:</label>
                    <div class="col-md-8">
                        <div class="news-content" v-html="formatContent(form.content)"></div>
                    </div>
                </div>

                <!-- Investors -->
                <div class="form-group">
                    <label class="col-md-2 control-label">{{ lang.investors }}:</label>
                    <div class="col-md-8">
                        <div v-if="form.investors && form.investors.length > 0" class="investors-list">
                            <el-tag 
                                v-for="investor in form.investors" 
                                :key="investor.id"
                                class="investor-tag">
                                {{ investor.name }} {{ investor.surname }}
                            </el-tag>
                        </div>
                        <div v-else class="text-muted">
                            {{ lang.all_investors || 'Available to all investors' }}
                        </div>
                    </div>
                </div>

                <!-- Created At -->
                <div class="form-group">
                    <label class="col-md-2 control-label">{{ lang.created_at }}:</label>
                    <div class="col-md-6">
                        {{ formatDate(form.created_at) }}
                    </div>
                </div>

                <!-- Updated At -->
                <div class="form-group">
                    <label class="col-md-2 control-label">{{ lang.updated_at }}:</label>
                    <div class="col-md-6">
                        {{ formatDate(form.updated_at) }}
                    </div>
                </div>

                <!-- Actions -->
                <div class="form-group" v-if="canEdit">
                    <label class="col-md-2 control-label"></label>
                    <div class="col-md-8">
                        <el-button type="primary" 
                                   icon="el-icon-edit" 
                                   @click="editNews"
                                   :disabled="loading">
                            {{ lang.edit || 'Edit News' }}
                        </el-button>
                        <el-button type="default" 
                                   icon="el-icon-back" 
                                   @click="goBack"
                                   :disabled="loading">
                            {{ lang.back || 'Back to List' }}
                        </el-button>
                    </div>
                </div>

            </el-row>
        </el-form>
    </div>
</template>

<style scoped>
.news-title {
    margin: 0;
    color: #303133;
    font-weight: 500;
}

.news-content {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 4px;
    border-left: 4px solid #409eff;
    white-space: pre-line;
    line-height: 1.6;
}

.investors-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.investor-tag {
    margin: 2px 4px 2px 0;
}

.text-muted {
    color: #909399;
    font-style: italic;
}
</style>

<script>
import {responseParse} from '../../../mixins/responseParse'
import {getData} from '../../../mixins/getData'
import ImageModal from '../../../components/admin/ImageModal.vue'

export default {
    name: "NewsViewComponent",
    components: {
        ImageModal
    },
    props: [
        'id',
        'getSaveDataRoute'
    ],

    data() {
        return {
            loading: false,
            lang: {},
            routes: {},
            userRole: '',
            canEdit: false,

            /**
             * Form data
             */
            form: {
                id: '',
                title: '',
                content: '',
                status: '',
                image: '',
                admin: null,
                investors: [],
                created_at: '',
                updated_at: ''
            },
        }
    },

    created() {
        this.getViewData();
    },

    methods: {
        /**
         * Get view data
         */
        async getViewData() {
            this.loading = true;

            const formData = new FormData();
            formData.append('id', this.id);

            await getData({
                method: 'POST',
                url: this.getSaveDataRoute,
                data: formData
            }).then(response => {
                // Parse response notification.
                responseParse(response, false);
                
                if (response.status === 200) {
                    // Response data.
                    let data = response.data.data;

                    this.lang = data.trans_text;
                    this.routes = data.routes;
                    this.userRole = data.userRole || '';

                    if (data.item) {
                        this.form = data.item;
                        
                        // Check if user can edit
                        this.canEdit = this.userRole === 'administrator' || 
                                      (this.form.admin && this.form.admin.id === data.userId);
                    }
                }
                this.loading = false;
            }).catch(error => {
                this.loading = false;
                responseParse(error.response);
            });
        },

        /**
         * Get status type for el-tag
         */
        getStatusType(status) {
            switch (status) {
                case 'published':
                    return 'success';
                case 'draft':
                    return 'warning';
                case 'archived':
                    return 'info';
                default:
                    return '';
            }
        },

        /**
         * Get status label
         */
        getStatusLabel(status) {
            switch (status) {
                case 'published':
                    return this.lang.published || 'Published';
                case 'draft':
                    return this.lang.draft || 'Draft';
                case 'archived':
                    return this.lang.archived || 'Archived';
                default:
                    return status;
            }
        },

        /**
         * Format content with line breaks
         */
        formatContent(content) {
            if (!content) return '';
            return content.replace(/\n/g, '<br>');
        },

        /**
         * Format date
         */
        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleString();
        },

        /**
         * Get image URL
         */
        getImageUrl(image) {
            if (!image) return '';
            return image.startsWith('http') ? image : '/storage/' + image;
        },

        /**
         * Edit news
         */
        editNews() {
            if (this.routes.create) {
                window.location.href = this.routes.create.replace('create', 'create/' + this.id);
            }
        },

        /**
         * Go back to list
         */
        goBack() {
            if (this.routes.index) {
                window.location.href = this.routes.index;
            } else {
                window.history.back();
            }
        }
    }
}
</script>
