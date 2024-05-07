<template>
    <span>
        <el-switch
            v-loading="loading"
            element-loading-text="Loading..."
            element-loading-spinner="el-icon-loading"
            element-loading-background="rgba(0, 0, 0, 0)"
            :change="changeStatus"
            v-model="status"
            active-color="#13ce66"
            inactive-color="#ff4949">
</el-switch>
    </span>
</template>

<script>
    import {getData} from '../../mixins/getData'
    import {responseParse} from '../../mixins/responseParse'

    export default  {

        props: [
            'route',
            'id',
            'active'
        ],

        data(){

            return {
                loading: false,
                status: this.active ? true : false
            }

        },
        watch:{
            status(){
                this.changeStatus();
            }
        },
        methods: {

            /**
             * Change status.
             */
            async changeStatus(){

                this.loading = true;

                await getData({
                    method: 'POST',
                    url: this.route,
                    data: {id: this.id, status: this.status}
                }).then(response => {

                    // Parse response notification.
                    responseParse(response);

                    this.loading = false;

                })

            }

        }

    }

</script>
