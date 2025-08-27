<template>
    <span v-if="unreadCount > 0" class="notification-dot">
        {{ unreadCount > 99 ? '99+' : unreadCount }}
    </span>
</template>

<script>
import axios from 'axios';

export default {
    name: 'InvestorNewsNotification',
    data() {
        return {
            unreadCount: 0
        };
    },
    mounted() {
        // Fetch unread count immediately on page load
        this.fetchUnreadCount();
        
        // Refresh count every 2 minutes for updates
        this.interval = setInterval(this.fetchUnreadCount, 120000);
        
        // Listen for refresh events (when news is viewed)
        this.$root.$on('refresh-news-count', this.fetchUnreadCount);
    },
    beforeDestroy() {
        if (this.interval) {
            clearInterval(this.interval);
        }
        this.$root.$off('refresh-news-count', this.fetchUnreadCount);
    },
    methods: {
        async fetchUnreadCount() {
            try {
                const response = await axios.get('/investor/news/unread-count');
                if (response.data.success) {
                    this.unreadCount = response.data.unread_count;
                }
            } catch (error) {
                // Silently fail if investor is not authenticated or route not accessible
                if (error.response && error.response.status !== 401 && error.response.status !== 403) {
                    console.error('Error fetching unread count:', error);
                }
                this.unreadCount = 0;
            }
        }
    }
};
</script>

<style>
/* Styles are defined in admin.scss for better integration */
</style>
