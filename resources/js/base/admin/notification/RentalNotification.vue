<template>
        <div class="rentals-container">
            <el-popover
                placement="bottom"
                title="Pending Rentals"
                width="400"
                trigger="click">
                <div class="items-wrapper">
                    <div class="rental-notification-wrapper" v-for="(rental, index) in pendingRentals" :key="index" :class="getItemClass(index)">
                        <el-card class="box-card">
                            <div slot="header" class="clearfix">
                                <i class="el-icon-house"> {{rental.project_name}}</i>
                            </div>
                            <div class="text item">
                                <div style="padding-bottom: 5px">
                                    <i class="el-icon-money" style="color:green"> {{rental.amount}} {{rental.currency}}</i>
                                </div>
                                <div>
                                    <i class="el-icon-date" style="color:gray"> {{rental.payment_date}}</i>
                                </div>
                            </div>
                            <div class="text item">
                                <i class="el-icon-user-solid"> {{rental.tenant_name}} {{rental.tenant_surname}}</i>
                            </div>
                            <div class="text item" v-if="!investorView">
                                <i class="el-icon-user"> {{rental.investor_name}} {{rental.investor_surname}}</i>
                            </div>
                        </el-card>
                    </div>
                </div>
                <el-badge slot="reference" :value="pendingRentalsCount" class="item" style="cursor: pointer" type="warning">
                    <i class="el-icon-bell" @click="togglePendingRentalsList" style="color:white"></i>
                </el-badge>
            </el-popover>

        </div>
</template>

<script>

export default {
    props: [
        'investorView',
    ],
    data() {
        return {
            pendingRentals: [],
            pendingRentalsCount: 0,
            showPendingRentals: false,
        }
    },
    mounted() {
        this.fetchPendingRentals();
    },
    methods: {
        async fetchPendingRentals() {
            await axios.get('/assets/notifications/pending-rentals').then(response => {
                this.pendingRentals = response.data.data;
                this.pendingRentals.forEach((rental, index) => {
                    if (!rental.read) {
                        this.pendingRentalsCount += 1;
                    }
                });

            });
        },

        togglePendingRentalsList() {
            this.showPendingRentals = !this.showPendingRentals;
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

        getItemClass(index) {
            return index % 2 === 0 ? 'even-item' : 'odd-item';
        }
    }
}

</script>


<style scoped>
.rental-body p {
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
}
.even-item .box-card{
    border-left: 5px solid #807f7f;
}
.odd-item .box-card{
    border-left: 5px solid #646262;
}
.text.item{
    margin-bottom: 7px;
}
.el-card__header{
    padding: 10px 20px;
}

.items-wrapper::-webkit-scrollbar {
    display: none;
}
.items-wrapper{
    max-height: 70vh;
    overflow-y: scroll;
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;
}
</style>
