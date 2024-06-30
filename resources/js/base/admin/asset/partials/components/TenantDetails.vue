<template>
    <div>
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Tenant Name:</label>
            <div class="col-md-5 uppercase-medium">
                <el-input v-model="tenant.name" placeholder="Name" @input="capitalizeFirstLetter('name')"></el-input>
            </div>
            <div class="col-md-5 uppercase-medium">
                <el-input v-model="tenant.surname" placeholder="Surname" @input="capitalizeFirstLetter('surname')"></el-input>
            </div>
        </div>
        <div class="form-group dashed">
            <label class="col-md-1 control-label">ID Number:</label>
            <div class="col-md-10 uppercase-medium">
                <el-input v-model="tenant.id_number" placeholder="ID Number"></el-input>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-1 control-label">Citizenship:</label>
            <div class="col-md-10 uppercase-medium">
                <el-select v-model="tenant.citizenship" filterable
                           placeholder="Select Country">
                    <el-option
                        v-for="country in this.countries"
                        :key="country.country"
                        :label="country.country"
                        :value="country.country"
                    ></el-option>
                </el-select>
            </div>
        </div>
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Email:</label>
            <div class="col-md-10 uppercase-medium">
                <el-input type="email" v-model="tenant.email" placeholder="Email"></el-input>
            </div>
        </div>
        <div class="form-group phone">
            <label class="col-md-1 control-label">Phone:</label>
            <div class="col-md-10 uppercase-medium">
                <el-input placeholder="Phone" v-model="tenant.phone" class="input-with-select">
                    <el-select v-model="tenant.prefix" slot="prepend" filterable
                               placeholder="Prefix">
                        <el-option
                            v-for="prefix in this.prefixes"
                            :key="prefix.prefix"
                            :label="prefix.prefix"
                            :value="prefix.prefix"
                        ></el-option>
                    </el-select>
                </el-input>
            </div>
        </div>
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Agreement Date:</label>
            <div class="col-md-10 uppercase-medium">
                <el-date-picker v-model="tenant.agreement_date" format="yyyy/MM/dd" type="date" placeholder="Pick a date"></el-date-picker>
            </div>
        </div>
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Agreement Term:</label>
            <div class="col-md-10 uppercase-medium">
                <el-select v-model="tenant.agreement_term" placeholder="Agreement Term" filterable>
                    <el-option
                        v-for="term in agreementTerms"
                        :key="term"
                        :label="term"
                        :value="term"
                    ></el-option>
                </el-select>
            </div>
        </div>
        <div class="form-group dashed">
            <label class="col-md-1 control-label">Monthly Rent:</label>
            <div class="col-md-7 uppercase-medium">
                <el-input type="number" v-model="tenant.monthly_rent" placeholder="Monthly Rent"></el-input>
            </div>
            <div class="col-md-3 uppercase-medium">
                <el-select v-model="tenant.currency" placeholder="Currency">
                    <el-option label="USD" value="USD"></el-option>
                    <el-option label="GEL" value="GEL"></el-option>
                </el-select>
            </div>
        </div>
        <div class="form-group dashed">
            <el-button type="primary" size="medium" @click="generateRentalList">Generate Rental List</el-button>
        </div>
    </div>
</template>

<script>
export default {
    props: ['tenant', 'loading', 'countries', 'prefixes'],
    data() {
        return {
            agreementTerms: this.getAgreementTerms()
        }
    },
    watch: {
        tenant: {
            handler(newTenant) {
                this.$emit('update-tenant', newTenant);
            },
            deep: true
        }
    },
    methods: {
        capitalizeFirstLetter(field) {
            if (this.tenant[field]) {
                this.tenant[field] = this.tenant[field].charAt(0).toUpperCase() + this.tenant[field].slice(1);
                this.$emit('update-tenant', this.tenant);
            }
        },
        generateRentalList() {
            this.$emit('generate-rental-list');
        },
        getAgreementTerms() {
            let terms = [];
            for (let i = 2; i <= 120; i++) {
                terms.push(i);
            }
            return terms;
        }
    }
}
</script>
