<template>
    <el-checkbox v-model="checked" :label="this.id"></el-checkbox>
</template>
<script>
    import * as types from '../../store/action-types'

    export default {
        store: window.store,
        props: [
            'id'
        ],
        computed: {
            checked: {
                get(){
                    // console.log(this.$store.getters.checkedId.includes(this.id), 'qdadsa', this.id, this.$store.getters.checkedId);
                    return this.$store.getters.checkedId.includes(this.id.toString());
                },
                set(){
                    let checkedId = this.$store.getters.checkedId;

                    if (!this.$store.getters.checkedId.includes(this.id.toString())) {
                        checkedId.push(this.id.toString());
                    } else {
                        if (checkedId) {
                            checkedId = checkedId.filter((item) => {
                                return item != this.id;
                            })
                        }
                    }
                    this.$store.dispatch(types.CHECKED_ID, checkedId);
                }
            }
        },
    }
</script>
