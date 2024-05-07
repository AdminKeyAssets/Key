<template>
    <el-select v-model="perPage" allow-create filterable @change="changePerPage" placeholder="per page">
        <el-option
            v-for="item in options"
            :key="item.value"
            :label="item.label"
            :value="item.value">
        </el-option>
    </el-select>
</template>

<script>

    export default {
        props: [
            'active',
            'url'
        ],
        data(){
            return {
                perPage: this.active,
                options: [
                    { value: 25, label: "25"},
                    { value: 50, label: "50"},
                    { value: 200, label: "200"}
                ]
            }
        },
        methods: {

            changePerPage(){
                window.location.href = this.url + '?limit=' + this.perPage;
            }

        }
    }

</script>
