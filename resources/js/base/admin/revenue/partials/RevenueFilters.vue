<template>
    <div class="block">
        <!-- Button to toggle the visibility of the filters -->
        <el-button type="primary" @click="showFilters = !showFilters" style="margin-bottom: 20px">
            {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
        </el-button>

        <!-- Filters form, shown only if showFilters is true -->
        <el-form
            v-if="showFilters"
            ref="form" :model="form" class="form-inline form-bordered"
            @submit.prevent="applyFilters">
            <el-row>
                <div class="form-group">
                    <el-date-picker
                        v-model="form.agreement_date"
                        type="daterange"
                        format="yyyy/MM/dd"
                        value-format="yyyy/MM/dd"
                        start-placeholder="Start date"
                        end-placeholder="End date">
                    </el-date-picker>
                </div>

                <el-button type="secondary" icon="el-icon-search" @click="applyFilters">Apply Filters</el-button>
            </el-row>
        </el-form>
    </div>
</template>

<script>
import { responseParse } from "../../../mixins/responseParse";

export default {
    data() {
        return {
            form: {
                agreement_date: '',
            },
            showFilters: false, // Controls the visibility of the filters, initially hidden
        };
    },
    mounted() {
        this.loadFiltersFromQueryParams();
    },
    methods: {
        loadFiltersFromQueryParams() {
            const urlParams = new URLSearchParams(window.location.search);
            this.form.agreement_date = urlParams.get('agreement_date') ? urlParams.get('agreement_date').split(',') : '';
        },
        applyFilters() {
            const queryParams = new URLSearchParams(this.form).toString();
            window.location.search = queryParams;
        },
    }
};
</script>

<style scoped>
.block {
    margin-bottom: 20px;
}
</style>
