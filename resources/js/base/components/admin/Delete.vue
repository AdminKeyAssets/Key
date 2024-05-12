<template>
    <el-button @click="deleteElement" icon="el-icon-delete-solid" size="small" type="danger"></el-button>
</template>

<script>
    import {getData} from '../../mixins/getData'
    import {responseParse} from '../../mixins/responseParse'

    export default  {

        props: [
            'url',
            'id'
        ],

        methods: {

            /**
             * Delete Element.
             */
            deleteElement(){

                this.$confirm('Are you sure?', 'You are deleting an item', {
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    type: 'warning'
                }).then(async () => {

                    this.loading = true;

                    await getData({
                        method: 'POST',
                        url: this.url,
                        data: {id: this.id}
                    }).then(response => {
                        // Parse response notification.
                        responseParse(response);

                        if (response.status == 200) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                        this.loading = false;
                    })

                });

            }

        }

    }

</script>
