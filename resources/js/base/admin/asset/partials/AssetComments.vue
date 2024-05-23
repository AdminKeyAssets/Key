<template>
    <div>
        <div class="block">

            <div class="comments-section">
                <h3>Comments</h3>
                <ul>
                    <li v-for="comment in comments" :key="comment.id">
                        <p>{{ comment.comment }} - {{ comment.admin.name }} ({{ comment.created_at }})</p>
                        <a v-if="comment.attachment" :href="`/storage/${comment.attachment}`" target="_blank">View Attachment</a>
                        <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                   @click="deleteComment(comment.id)"></el-button>
                    </li>
                </ul>
            </div>

            <el-form ref="form" class="form-horizontal form-bordered"
                     @submit.prevent="submitComment">

                <el-row>
                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Comment:</label>
                        <div class="col-md-10 uppercase-medium">
                            <textarea class="form-control"  v-model="newComment"></textarea>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Attach file:</label>
                        <div class="col-md-10 uppercase-medium">
                            <input type="file" @change="onFileChange">
                            <div v-if="attachment">
                                <el-button icon="el-icon-delete-solid" size="small" type="danger"
                                           @click="removeFile"></el-button>
                            </div>
                        </div>
                    </div>
                    <el-button type="primary" @click="submitComment">Add Comment</el-button>
                </el-row>
            </el-form>
        </div>
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

        removeFile(){
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
        }
    }
}

</script>
