<template>
    <div class="block">
        <div class="block-title">
            <h2>Developer Details</h2>
        </div>
        <div v-if="loading" class="text-center">
            <i class="fa fa-spinner fa-spin fa-3x"></i>
        </div>
        <div v-else class="block-content">
            <div class="row">
                <div class="col-md-6">
                    <h4>Basic Information</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th>Developer Name</th>
                            <td>{{ developer.name }}</td>
                        </tr>
                        <tr>
                            <th>ID Code</th>
                            <td>{{ developer.id_code }}</td>
                        </tr>
                        <tr>
                            <th>Representative</th>
                            <td>{{ developer.representative }}</td>
                        </tr>
                        <tr>
                            <th>Representative Position</th>
                            <td>{{ developer.representative_position }}</td>
                        </tr>
                        <tr>
                            <th>Tel</th>
                            <td>{{ developer.tel }}</td>
                        </tr>
                        <tr>
                            <th>Username</th>
                            <td>{{ developer.username }}</td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h4>Files &amp; Documents</h4>
                    <div class="row">
                        <div v-if="developer.logo" class="col-md-6 mb-3">
                            <h5>Logo</h5>
                            <img :src="developer.logo" class="img-thumbnail" style="max-height: 150px">
                        </div>
                        <div v-if="developer.stamp" class="col-md-6 mb-3">
                            <h5>Stamp</h5>
                            <img :src="developer.stamp" class="img-thumbnail" style="max-height: 150px">
                        </div>
                    </div>
                    <div class="row">
                        <div v-if="developer.signature" class="col-md-6 mb-3">
                            <h5>Signature</h5>
                            <img :src="developer.signature" class="img-thumbnail" style="max-height: 150px">
                        </div>
                        <div v-if="developer.service_agreement" class="col-md-6 mb-3">
                            <h5>Service Agreement</h5>
                            <a :href="developer.service_agreement" target="_blank" class="btn btn-sm btn-info">
                                <i class="fa fa-download"></i> Download Service Agreement
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group form-actions">
                <div>
                    <a :href="editLink" class="btn btn-primary">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                    <a :href="indexLink" class="btn btn-default">
                        <i class="fa fa-list"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        id: {
            required: true
        },
        getSaveDataRoute: {
            required: true,
            type: String
        }
    },
    data() {
        return {
            developer: {},
            loading: true,
            editLink: '',
            indexLink: ''
        };
    },
    mounted() {
        this.fetchDeveloperData();
    },
    methods: {
        fetchDeveloperData() {
            axios.post(this.getSaveDataRoute, {
                id: this.id
            })
                .then(response => {
                    if (response.data.status === 200 && response.data.data.item) {
                        this.developer = response.data.data.item;
                        this.editLink = response.data.data.routes.edit ? 
                            response.data.data.routes.edit.replace(':id?', this.id) : '';
                        this.indexLink = response.data.data.routes.create;
                    } else {
                        this.$message.error('Failed to load developer data');
                    }
                    this.loading = false;
                })
                .catch(error => {
                    console.error('Error loading developer data:', error);
                    this.$message.error('Error loading developer data');
                    this.loading = false;
                });
        }
    }
};
</script>
