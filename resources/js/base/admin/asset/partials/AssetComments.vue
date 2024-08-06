<template>
    <div class="comments-section">
        <ul class="comments-list">
            <li v-for="comment in visibleComments" :key="comment.id"
                :class="['comment-item', { mine: investorView && comment.investor_id === userId }, { mine: !investorView && comment.admin_id === userId }]">
                <div class="comment-header">
                    <span class="comment-user" v-if="comment.admin">{{ comment.admin.name }} {{
                            comment.admin.surname
                        }}</span>
                    <span class="comment-user" v-else-if="comment.investor">{{
                            comment.investor.name
                        }} {{ comment.investor.surname }}</span>
                    <span class="comment-date">{{ formatDate(comment.created_at) }}</span>
                </div>
                <p class="comment-text">{{ comment.comment }}</p>
                <a v-if="comment.attachment" :href="`${comment.attachment}`" target="_blank"
                   class="comment-attachment">View
                    {{ getFilename(comment.attachment) }}</a>
                <div class="comment-actions" v-if="isAdmin">
                    <span class="comment-delete" @click="deleteComment(comment.id)">
                        Delete
                    </span>
                </div>
            </li>
        </ul>
        <el-button v-if="!showAllComments && hasOlderComments" @click="showAllComments = true"
                   class="show-all-comments-button">Show All Comments
        </el-button>
        <el-form @submit.prevent="submitComment" class="comment-form">
            <el-input v-model="newComment" placeholder="Add a comment" class="comment-input"></el-input>
            <div class="end-comments-form">
                <div>
                    <label for="comment-file-input" class="custom-file-label">
                        <i class="el-icon-upload"></i> Upload File
                    </label>
                    <input type="file" id="comment-file-input" @change="onFileChange" class="comment-file-input">
                    <span class="file-name">{{ attachmentName || 'No file chosen' }}</span>
                </div>
                <el-button type="primary" @click="submitComment" class="comment-submit-button">Submit</el-button>
            </div>
        </el-form>
    </div>
</template>

<script>
import {responseParse} from '../../../mixins/responseParse';
import {getData} from '../../../mixins/getData';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {
    props: {
        id: {
            type: [String, Number],
            required: true
        },
        userId: {
            type: [String, Number],
            required: true
        },
        isAdmin: {
            type: [Boolean, Number],
            default: false
        },
        investorView: {
            type: [Boolean, Number],
            default: false
        }
    },
    data() {
        return {
            comments: [],
            newComment: '',
            attachment: null,
            attachmentName: null,
            showAllComments: false,
        };
    },
    mounted() {
        this.fetchComments();
    },
    computed: {
        visibleComments() {
            if (this.showAllComments) {
                return this.comments;
            }
            const oneMonthAgo = new Date();
            oneMonthAgo.setMonth(oneMonthAgo.getMonth() - 1);
            return this.comments.filter(comment => new Date(comment.created_at) >= oneMonthAgo);
        },
        hasOlderComments() {
            const oneMonthAgo = new Date();
            oneMonthAgo.setMonth(oneMonthAgo.getMonth() - 1);
            return this.comments.some(comment => new Date(comment.created_at) < oneMonthAgo);
        }
    },
    methods: {
        onFileChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.attachment = file;
                this.attachmentName = file.name;
            } else {
                this.attachment = null;
                this.attachmentName = null;
            }
        },
        async fetchComments() {
            await axios.get(`/assets/${this.id}/comments`).then(response => {
                this.comments = response.data.data;
            });
        },
        async deleteComment(commentId) {
            this.$confirm('Are you sure?', 'You are deleting a comment', {
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                type: 'warning'
            }).then(async () => {
                await axios.post(`/assets/${this.id}/comments/delete/${commentId}`).then(response => {
                    responseParse(response);
                    this.comments = response.data.data;
                });
            });
        },
        async submitComment() {
            const formData = new FormData();
            formData.append('comment', this.newComment);
            if (this.attachment) {
                formData.append('attachment', this.attachment);
            }

            let url = this.investorView ? `/assets/${this.id}/investor/comments` : `/assets/${this.id}/comments`;
            await axios.post(url, formData)
                .then(response => {
                    responseParse(response);
                    this.comments = response.data.data;
                    this.newComment = '';
                    this.attachment = null;
                    this.attachmentName = null;
                });
        },
        formatDate(isoString) {
            const date = new Date(isoString);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        },
        getFilename(path) {
            return path.split('/').pop();
        }
    }
};
</script>

<style scoped>
.comments-section {
    padding: 20px;
    background: #f9f9f9;
    border-radius: 10px;
}

.comments-list {
    list-style-type: none;
    padding: 0;
    display: flex;
    flex-direction: column;
}

.comment-item {
    padding: 15px 25px;
    margin-bottom: 10px;
    background: #EFF0F1;
    border-radius: 20px;
    width: 60%;
    align-self: flex-start;
}

@media screen and (max-width: 992px) {
    .comment-item {
        width: 85%;
    }
    .comment-header{
        flex-direction: column;
    }

}

.comment-item.mine {
    align-self: flex-end;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    font-weight: bold;
    margin-bottom: 5px;
}

.comment-user {
    color: #333;
}

.comment-item.mine .comment-user {
    color: #FF9100;
}

.comment-date {
    color: #888;
    font-size: 0.9em;
}

.comment-text {
    margin: 5px 0;
}

.comment-attachment {
    display: block;
    margin-top: 5px;
    color: #1e90ff;
    text-decoration: underline;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.comment-form {
    margin-top: 20px;
}

.comment-input {
    display: inline-block;
    margin-right: 10px;
}

.comment-file-input {
    display: none; /* Hide the default file input */
}

.custom-file-label {
    display: inline-block;
    padding: 10px 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: white;
    cursor: pointer;
    color: #007bff;
    font-size: 16px;
    margin-right: 10px;
    text-align: center;
}

.custom-file-label:hover {
    background-color: #f0f0f0;
}

.file-name {
    font-style: italic;
    color: #555;
    margin-left: 10px;
}

.end-comments-form {
    display: flex;
    align-items: center;
    margin-top: 10px;
}

.comment-submit-button {
    display: inline-block;
    margin-left: 10px;
}

.comment-actions {
    display: flex;
    justify-content: right;
}

.comment-delete {
    text-decoration: underline;
    cursor: pointer;
}

.show-all-comments-button {
    display: block;
    margin: 20px auto;
}
</style>
