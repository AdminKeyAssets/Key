<template>
    <el-checkbox v-model="checked"></el-checkbox>
</template>

<script>
    import * as types from '../../store/action-types'

    export default {
        store: window.store,
        props: [
            'allId'
        ],
        watch: {
            checked(){
                if (this.checked) {
                    this.$store.dispatch(types.CHECKED_ID, this.allId.split(','));
                } else {
                    this.$store.dispatch(types.CHECKED_ID, []);
                }
            }
        },
        data(){
            return {
                checked: false
            }
        }
    }

</script>
