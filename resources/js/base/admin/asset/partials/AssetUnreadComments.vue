<template>
    <div class="header">
        <div class="comments-container">
            <div class="comments-button" @click="toggleUnreadCommentsList">
                <i class="el-icon-bell" style="color:white"></i>
                <span class="badge">{{ unreadCommentsCount }}</span>
            </div>
            <el-drawer
                title="Notifications"
                :visible.sync="showUnreadComments">

                <el-card class="box-card" v-for="comment in unreadComments" :key="comment.id">
                    <div slot="header" class="clearfix">
                        <span>{{ comment.admin.name }}</span>
                    </div>
                    <div class="text item">
                        <div class="comment-body">
                            <p>
                                {{ comment.comment }}
                            </p>
                            <a v-if="comment.attachment" :href="`${comment.attachment}`" target="_blank"
                               class="comment-attachment">View Attachment</a>
                        </div>

                        <div style="display: flex; justify-content: space-between; ">
                            <span>{{ formatDate(comment.created_at) }}</span>
                            <a :href="`/assets/comments/${comment.id}`" class="view-asset-link">
                                read
                            </a>
                        </div>
                    </div>
                </el-card>
            </el-drawer>

        </div>
    </div>
</template>

<script>

export default {
    props: [
        'id'
    ],
    data() {
        return {
            unreadComments: [],
            unreadCommentsCount: 0,
            showUnreadComments: false,
        }
    },
    created() {
    },
    mounted() {
        this.fetchUnreadComments();
    },
    methods: {
        async fetchUnreadComments() {
            await axios.get('/assets/comments/unread').then(response => {
                this.unreadComments = response.data.data;
                this.unreadCommentsCount = this.unreadComments.length;
            });
        },

        // async fetchUnreadComments() {
        //     await axios.get(`/assets/4/comments`).then(response => {
        //         this.comments = response.data.data;
        //         this.unreadComments = response.data.data;
        //     });
        // },
        toggleUnreadCommentsList() {
            this.showUnreadComments = !this.showUnreadComments;
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
.comment-body p{
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
}
</style>
