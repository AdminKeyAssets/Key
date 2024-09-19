<template>
    <div class="reminders-container">

        <el-popover
            placement="bottom"
            title="Upcoming Reminders"
            width="400"
            trigger="click">
            <div class="items-wrapper">

                <span class="add-reminder" @click="openModal" type="primary">Add Reminder</span>

                <div class="upcoming-reminder-wrapper" v-for="(reminder, index) in reminders" :key="index"
                     :class="getItemClass(index)">
                    <el-card class="box-card">
                        <div class="reminder-item-wrapper">
                            <div class="reminder-item-content">
                                <div class="date item">
                                    <i class="el-icon-time"><span> {{ formatDate(reminder.reminder_date) }}</span></i>
                                </div>
                                <div class="text item">
                                    {{ reminder.comment }}
                                </div>
                            </div>
                            <div class="reminder-item-done">
                            <span @click="markAsDone(reminder.id)" type="success">
                                 Done
                            </span>
                            </div>
                        </div>
                    </el-card>
                </div>
            </div>
            <el-badge slot="reference" v-if="UpcomingRemindersCount" class="item" style="cursor: pointer"
                      type="warning">
                <el-tooltip content="Reminders" placement="top" effect="light">
                    <i class="el-icon-message-solid" @click="toggleUpcomingRemindersList" style="color: #E6A23C"></i>
                </el-tooltip>
            </el-badge>

            <el-badge slot="reference" v-else class="item" style="cursor: pointer" type="warning">
                <el-tooltip content="Reminders" placement="top" effect="light">
                    <i class="el-icon-bell" @click="toggleUpcomingRemindersList" style="color:white"></i>
                </el-tooltip>
            </el-badge>
        </el-popover>


        <el-dialog :visible.sync="modalVisible" title="Add Reminder">
            <el-form :model="newReminder" label-width="100px">
                <el-form-item label="Date & Time">
                    <el-date-picker v-model="newReminder.reminder_date" type="datetime"
                                    placeholder="Select date and time"></el-date-picker>
                </el-form-item>
                <el-form-item label="Comment">
                    <el-input type="textarea" v-model="newReminder.comment"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="modalVisible = false">Cancel</el-button>
                <el-button type="primary" @click="saveReminder">Save</el-button>
            </div>
        </el-dialog>

    </div>
</template>

<script>
export default {
    data() {
        return {
            reminders: [],
            modalVisible: false,
            newReminder: {
                reminder_date: '',
                comment: ''
            },
            showUpcomingReminders: false,
            UpcomingRemindersCount: 0,
        };
    },
    mounted() {
        this.fetchReminders();
    },
    methods: {
        async fetchReminders() {
            const response = await axios.get('/admin/users/reminders');
            this.reminders = response.data;

            this.reminders.forEach((reminder, index) => {
                if (!reminder.done) {
                    this.UpcomingRemindersCount++;
                }
            });
        },
        async saveReminder() {
            await axios.post('/admin/users/reminders', this.newReminder);
            this.modalVisible = false;
            this.newReminder = {reminder_date: '', comment: ''};
            this.fetchReminders(); // Refresh the reminders list
        },
        async markAsDone(reminderId) {
            await axios.patch(`/admin/users/reminders/${reminderId}/done`);
            this.fetchReminders(); // Refresh the reminders list
        },
        formatDate(isoString) {
            const date = new Date(isoString);
            return date.toLocaleString('en-GB', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
            });
        },
        openModal() {
            this.modalVisible = true;
        },

        toggleUpcomingRemindersList() {
            this.showUpcomingReminders = !this.showUpcomingReminders;
        },

        getItemClass(index) {
            return index % 2 === 0 ? 'even-item' : 'odd-item';
        },
    }
};
</script>

<style scoped>
.rental-body p {
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
}

.even-item .box-card {
    border-left: 5px solid #807f7f;
}

.odd-item .box-card {
    border-left: 5px solid #646262;
}

.text.item {
    margin-bottom: 7px;
}

.date.item {
    margin-bottom: 0 !important;
    font-size: 13px;
}

.el-card__header {
    padding: 10px 20px;
}

.items-wrapper::-webkit-scrollbar {
    display: none;
}

.items-wrapper {
    max-height: 70vh;
    overflow-y: scroll;
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none;
}

.add-reminder {
    cursor: pointer;
    font-style: italic;
    text-decoration: underline;
    padding: 5px 0;
    margin: 5px 0;
    display: inline-block;
}

.reminder-item-wrapper {
    display: flex;
    flex-direction: row;
}

.reminder-item-content {
    white-space: pre-line;
    width: 90%;
}

.reminder-item-done {
    width: 10%;
    color: green;
    cursor: pointer;
    font-style: italic;
    text-decoration: underline;
}
</style>
