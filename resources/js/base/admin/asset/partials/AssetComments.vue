<template>
    <div class="comments-section">
        <h3>Comments</h3>
        <ul class="comments-list">
            <li v-for="comment in comments" :key="comment.id" class="comment-item">
                <div class="comment-header">
                    <span class="comment-user">{{ comment.admin.name }}</span>
                    <span class="comment-date">{{ formatDate(comment.created_at) }}</span>
                </div>
                <p class="comment-text">{{ comment.comment }}</p>
                <a v-if="comment.attachment" :href="`${comment.attachment}`" target="_blank" class="comment-attachment">View
                    Attachment</a>
                <div class="comment-actions">
                <span class="comment-delete" @click="deleteComment(comment.id)">
                    Delete
                </span>
                </div>
            </li>
        </ul>
        <el-form @submit.prevent="submitComment" class="comment-form">
            <el-input v-model="newComment" placeholder="Add a comment" class="comment-input"></el-input>
            <input type="file" @change="onFileChange" class="comment-file-input">
            <el-button type="primary" @click="submitComment" class="comment-submit-button">Submit</el-button>
        </el-form>
    </div>
</template>


<script>
import {responseParse} from '../../../mixins/responseParse'
import {getData} from '../../../mixins/getData'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {
    props: [
        'id'
    ],
    data() {
        return {
            comments: [],
            newComment: '',
            attachment: null,
        }
    },


    mounted() {
        this.fetchComments();
    },

    methods: {
        onFileChange(e) {
            this.attachment = e.target.files[0];
        },

        removeFile() {
            this.attachment = null;
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

            await axios.post(`/assets/${this.id}/comments`, formData)
                .then(response => {
                    this.comments = response.data.data;
                    this.newComment = '';
                    this.attachment = null;
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
        }
    }
}

</script>

<style scoped>
.comments-section {
    padding: 20px;
    background: #f9f9f9;
    border-radius: 10px;
    max-width: 600px;
    margin: 0 auto;
}

.comments-list {
    list-style-type: none;
    padding: 0;
}

.comment-item {
    padding: 10px;
    margin-bottom: 10px;
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 5px;
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
}

.comment-form {
    margin-top: 20px;
}

.comment-input {
    width: calc(100% - 120px);
    display: inline-block;
    margin-right: 10px;
}

.comment-file-input {
    margin-top: 10px;
}

.comment-submit-button {
    display: inline-block;
    margin-top: 10px;
}
.comment-actions{
    display: flex;
    justify-content: right;
}
.comment-delete{
    text-decoration: underline;
    cursor: pointer;
}
</style>
