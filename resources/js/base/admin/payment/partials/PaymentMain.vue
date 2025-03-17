<template>
    <div>
        <div class="block">
            <el-form v-loading="loading"
                     element-loading-text="Loading..."
                     element-loading-spinner="el-icon-loading"
                     element-loading-background="rgba(0, 0, 0, 0.0)"
                     ref="form" :model="form" class="form-horizontal form-bordered">

                <el-row>
                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Date:</label>
                        <div class="col-md-10 uppercase-medium">
                            <el-date-picker
                                v-model="form.date"
                                format="yyyy/MM/dd"
                                value-format="yyyy/MM/dd"
                                type="date"
                                placeholder="Pick a payment date">
                            </el-date-picker>
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Amount:</label>
                        <div class="col-md-7 uppercase-medium">
                            <input class="form-control" :disabled="loading" v-model="form.amount">
                        </div>
                    </div>

                    <div class="form-group dashed">
                        <label class="col-md-1 control-label">Attachment:</label>
                        <div class="col-md-10 uppercase-medium">
                            <p v-if="form.attachment">
                                File: <a :href="form.attachment" target="_blank">View Attachment</a>
                                <el-button type="danger" icon="el-icon-delete" size="small"
                                           @click="removeFile">
                                </el-button>
                            </p>
                            <input v-else type="file" class="form-control" @change="onFileChange">
                        </div>
                    </div>

                </el-row>
            </el-form>
        </div>
    </div>
</template>

<script>
import { responseParse } from '../../../mixins/responseParse'
import { getData } from '../../../mixins/getData'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {
    props: [
        'routes',
        'updateData',
        'item',
        'nextPayment'
    ],
    data() {
        // Compute today's date in "yyyy/MM/dd" format.
        const today = new Date();
        const day = String(today.getDate()).padStart(2, '0');
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const year = today.getFullYear();
        const formattedDate = `${year}/${month}/${day}`;

        return {
            form: {
                currency: 'USD',
                date: formattedDate, // Set default date to today's date.
                amount: this.nextPayment || ''
            },
            loading: false,
            editor: ClassicEditor,
            addDetailIsBtnDisabled: true,
            currencies: {
                "GEL": "GEL",
                "USD": "USD",
            },
        }
    },
    updated() {
        this.updateData(this.form);
    },
    watch: {
        item() {
            if (this.item) {
                this.form = this.item;
            }
        },
        nextPayment(newVal) {
            // Only update if the current amount is empty or 0.
            if ((!this.form.amount || this.form.amount === 0) && newVal) {
                this.form.amount = newVal;
            }
        }
    },
    methods: {
        onFileChange(e) {
            this.form.attachment = e.target.files[0];
        },
        removeFile() {
            this.form.attachment = null;
        },
    }
}
</script>
