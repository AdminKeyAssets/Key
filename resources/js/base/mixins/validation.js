export default {
    methods: {
        emptyValue(value, name) {
            if (value == '' || value == null) {

                this.$message.error(name + ' სავალდებულოა')

                return true
            }

            return false
        }
    }
}
