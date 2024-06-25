<template>
    <div class="form-group dashed">
        <label class="col-md-1 control-label">Current Value:</label>
        <div class="col-md-10 uppercase-medium">
            <input class="form-control" :disabled="loading" v-model="form.current_value"></input>
        </div>
        <div v-if="form.currentValues">
            <el-table :data="form.currentValues" style="width: 100%">
                <el-table-column prop="value" label="Value" width="150"/>
                <el-table-column prop="date" label="Date" width="180">
                    <template slot-scope="scope">
                        <el-date-picker
                            v-model="scope.row.date"
                            type="date"
                            format="yyyy/MM/dd"
                            disabled
                            value-format="yyyy/MM/dd"
                        ></el-date-picker>
                    </template>
                </el-table-column>
            </el-table>
        </div>
    </div>
</template>

<script>
export default {
    props: ['form', 'loading', 'updateData']
}
</script>
