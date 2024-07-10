<template>
        <div class="comments-container">
            <el-badge :value="unreadCommentsCount" class="item" style="cursor: pointer">
                <i class="el-icon-chat-round" @click="toggleUnreadCommentsList" style="color:white"></i>
            </el-badge>
            <el-drawer
                title="Notifications"
                class="notifications-sidebar"
                :modal="false"
                :size="'50%'"
                :visible.sync="showUnreadComments">
                <div class="comment-wrapper" v-for="comment in unreadComments" :key="comment.id">
                    <el-card class="box-card" v-bind:class = "(comment.read)?'read-comment':'unread-comment'">
                        <div slot="header" class="clearfix">
                            <span>{{ comment.admin.name }} {{ comment.admin.surname }}</span>
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
                </div>
            </el-drawer>

        </div>
</template>

<script>

export default {
    data() {
        return {
            unreadComments: [],
            unreadCommentsCount: 0,
            showUnreadComments: false,
        }
    },
    mounted() {
        this.fetchUnreadComments();
    },
    methods: {
        async fetchUnreadComments() {
            await axios.get('/assets/comments/unread').then(response => {
                this.unreadComments = response.data.data;

                this.unreadComments.forEach((comment, index) => {
                    if (!comment.read) {
                        this.unreadCommentsCount += 1;
                    }
                });

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
.comment-body p {
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
}
.box-card.read-comment{
    border-left: 5px solid gray;
}
.box-card.unread-comment{
    border-left: 5px solid red;
}
</style>
