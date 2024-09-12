<template>
    <div class="not-registered-wrapper">
        <div class="col-xs-12 title-wrapper">
      <span>
        Not registered yet?
        <span class="try-now" style="cursor: pointer" @click="isModalVisible = !isModalVisible">
          Try Demo User
        </span>
      </span>
        </div>

        <!-- Registration Modal -->
        <el-dialog
            title="For getting demo user and password, please fill the data below"
            :visible.sync="isModalVisible"
            width="50%"
            @close="handleModalClose"
        >
            <div>
                <!-- Conditionally render form or credentials based on `credentialsVisible` -->
                <form v-if="!credentialsVisible" @submit.prevent="submitRegistrationForm">
                    <div id="form-content">
                        <el-row :gutter="20">
                            <el-col :span="12">
                                <el-input placeholder="First name" v-model="form.name"></el-input>
                            </el-col>
                            <el-col :span="12">
                                <el-input placeholder="Last name" v-model="form.surname"></el-input>
                            </el-col>
                            <el-col :span="12">
                                <el-input placeholder="Email" v-model="form.email" type="email"></el-input>
                            </el-col>
                            <el-col :span="12">
                                <el-input placeholder="Phone" v-model="form.phone" class="input-with-select">
                                    <el-select v-model="form.prefix" slot="prepend" filterable placeholder="+995">
                                        <el-option label="+1" value="+1"></el-option>
                                        <el-option label="+995" value="+995"></el-option>
                                    </el-select>
                                </el-input>
                            </el-col>
                        </el-row>
                    </div>

                    <span slot="footer" class="dialog-footer">
            <el-button @click="isModalVisible = false">Cancel</el-button>
            <el-button type="primary" @click="submitRegistrationForm">Submit</el-button>
          </span>
                </form>

                <!-- Demo Credentials and Login Button -->
                <div v-else>
                    <el-row :gutter="20" class="credentials-boxes">
                        <el-col :span="12">
                            <label>User</label>
                            <p>{{ demoCredentials.name }}</p>
                        </el-col>
                        <el-col :span="12">
                            <label>Password</label>
                            <p>{{ demoCredentials.password }}</p>
                        </el-col>
                    </el-row>
                    <span slot="footer" class="dialog-footer">
            <el-button @click="isModalVisible = false">Close</el-button>
            <el-button type="primary" @click="autoLogin">Login</el-button>
          </span>
                </div>
            </div>
        </el-dialog>

        <!-- Hidden login form for demo user login -->
        <form id="login-form" @submit.prevent="submitLoginForm" style="display: none;">
            <input type="email" v-model="loginEmail" placeholder="Email" />
            <input type="password" v-model="loginPassword" placeholder="Password" />
        </form>
    </div>
</template>

<script>
import {responseParse} from "../../../mixins/responseParse";

export default {
    data() {
        return {
            form: {
                name: '',
                surname: '',
                email: '',
                phone: '',
                prefix: '+995',
            },
            demoCredentials: {
                name: '',
                password: '',
            },
            credentialsVisible: false,
            loginEmail: '',
            loginPassword: '',
            isModalVisible: false,
        };
    },
    methods: {
        handleModalClose() {
            this.resetForm();
            this.$emit('close');
        },
        resetForm() {
            this.form = {
                name: '',
                surname: '',
                email: '',
                phone: '',
                prefix: '+995',
            };
            this.credentialsVisible = false;
        },
        async submitRegistrationForm() {
            const formData = { ...this.form };
            await axios
                .post('/lead/add', formData)
                .then((response) => {
                    // Hide the form content and show demo credentials
                    this.demoCredentials.name = response.data.data.name;
                    this.demoCredentials.password = response.data.data.password;
                    this.credentialsVisible = true;

                    // Set login details to be used in login form
                    this.loginEmail = response.data.data.name;
                    this.loginPassword = response.data.data.password;
                })
                .catch((error) => {
                    let errorMessage = 'Registration failed. Please check the form for errors.';
                    if (error.response && error.response.data.errors) {
                        errorMessage = Object.values(error.response.data.errors).flat().join(' ');
                    }
                    alert(errorMessage);
                });
        },
        async submitLoginForm() {
            const formData = {
                email: this.loginEmail,
                password: this.loginPassword,
            };
            await axios
                .post('/login', formData)
                .then((response) => {
                    window.location.href = '/'; // Redirect to home or desired page after login
                })
                .catch((error) => {
                    alert('Login failed. Please try again.');
                });
        },
        autoLogin() {
            this.submitLoginForm();
        },
    },
};

</script>


<style>
/* Modal Styles */
.modal-content {
    border-radius: 10px;
    padding: 20px;
}

.modal-header {
    border-bottom: none;
    position: relative;
}

.modal-header h5 {
    font-size: 24px;
    font-weight: bold;
}

.modal-header p {
    margin-bottom: 20px;
    font-size: 16px;
    color: #555;
}

/* Custom Close Button */
.custom-close {
    opacity: 1;
    position: absolute;
    top: -30px;
    right: -30px;
    background-color: #007bff !important;
    border: none !important;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    color: #fff;
    font-size: 18px;
    line-height: 18px;
    text-align: center;
    cursor: pointer;
}

.form-control {
    border-radius: 5px;
    border: 1px solid #ccc;
    height: 45px;
    font-size: 14px;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    padding: 10px 20px;
    font-size: 14px;
}

.btn-secondary {
    padding: 10px 20px;
    font-size: 14px;
}

.modal-footer {
    border-top: none;
    text-align: center;
    padding-top: 20px;
}

.modal-footer .btn {
    width: 120px;
    font-weight: 600;
}

#form-content div {
    padding-bottom: 10px;
}

.credential-box p {
    background-color: #ffffff;
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 5px;
    text-align: center;
    margin-bottom: 20px;
    font-size: 14px;
    word-break: break-word;
}

.credential-box label {
    display: block;
    font-weight: bold !important;
    margin-bottom: 5px;
    font-size: 14px;
    width: 100%;
    text-align: center;
}

.credentials-boxes{
    display: flex;
    justify-content: center;
}
@media (max-width: 767px) {
    .modal-dialog {
        margin-top: 30%;
    }

    .credentials-boxes {
        flex-direction: column;
    }

    .credential-box {
        width: 100%;
        margin-bottom: 10px;
    }
}

#registrationModalLabel {
    font-size: 20px;
    font-weight: 500;
}

.input-group-addon {
    padding: 0;
}

.phone.input-group {
    display: flex;
    padding: 0 !important;
    border: none;
    border-radius: 5px !important;
}
.phone.input-group .input-group-addon{
    width: 50%;
    border-color: #ccc;
}


</style>
