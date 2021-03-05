<template>

  <v-app>

    <v-app-bar app dark>
        <v-spacer></v-spacer>
        <a href="%GITHUB_URL%" title="See project on github">
            <v-btn color="red accent-2" class="mx-4">
                See on github
                <v-icon dark right>mdi-github</v-icon>
            </v-btn>
        </a>
    </v-app-bar>

    <v-main>

        <v-container class="d-flex">

            <div class="d-flex flex-column mr-5">
                <v-card outlined class="api-card mb-5 pa-3 d-flex flex-column align-center">
                    <v-card-title>Ruby</v-card-title>

                    <v-form class="d-flex flex-column align-center">
                        <v-text-field
                            label="Description"
                            :counter="50"
                            :rules="[rules.required, rules.counter]"
                            required
                            outlined
                            v-model="description"
                            class="charge-field"
                        ></v-text-field>

                        <v-text-field
                            label="Amount"
                            :rules="[rules.required, rules.money]"
                            required
                            outlined
                            prefix="£"
                            v-model="amount"
                            class="charge-field"
                        ></v-text-field>

                        <v-btn color="primary" elevation="2" @click="createCharge" small
                            :disabled="!canCreateCharge"
                        >
                            Create a charge
                        </v-btn>

                    </v-form>
                </v-card>

                <v-card outlined class="api-card mb-5 d-flex flex-column align-center">
                    <v-card-title>Go</v-card-title>

                    <v-btn class="mb-5" color="primary" elevation="2" @click="getTransactions" small>
                        Get transactions
                    </v-btn>
                </v-card>

                <v-card outlined class="api-card d-flex flex-column align-center">
                    <v-card-title>Scala</v-card-title>

                    <v-btn class="mb-5"  color="primary" elevation="2" @click="getBalance" small>
                        Get balance
                    </v-btn>
                </v-card>
            </div>

            <v-card outlined class="d-flex flex-column align-center ml-5 flex-grow-1">

                <v-progress-circular
                    v-if="loader"
                    :size="70"
                    indeterminate
                    color="primary"
                    class="mt-3"
                ></v-progress-circular>

                <v-card-title v-if="response.code && !loader">
                    HTTP code {{ response.code }}
                </v-card-title>
                <v-card-text v-if="response.payload && !loader">
                    <pre>{{ response.payload }}</pre>
                </v-card-text>
            </v-card>


        </v-container>


    </v-main>

  </v-app>

</template>

<script>
import Config from "@/config";
import axios from 'axios';
export default {
    name: 'App',
    data: () => ({
        description: '',
        amount: '',
        loader: false,
        response: {
            code: null,
            payload: null
        },
        rules: {
            required: value => !!value || 'Required',
            counter: value => value.length <= 50 || 'Max 50 characters',
            money: value => {
                const pattern = /^[0-9]+(\.[0-9]{1,2})?$/
                return pattern.test(value) || 'Invalid amount of money.'
            },
        },
    }),
    methods: {
        createCharge() {
            this.setupApiCall();
            axios
                .post(Config.DOMAIN+Config.CREATE_CHARGE, {
                    description: this.description,
                    amount: this.amount,
                })
                .then((response) => {
                    this.processResponse(response);
                })
                .catch((error) => {
                    this.processError(error);
                });
        },
        getTransactions() {
            this.setupApiCall();
            axios
                .get(Config.DOMAIN+Config.GET_TRANSACTIONS)
                .then((response) => {
                    this.processResponse(response);
                })
                .catch((error) => {
                    this.processError(error);
                });
        },
        getBalance() {
            this.setupApiCall();
            axios
                .get(Config.DOMAIN+Config.API_PATH+Config.GET_BALANCE)
                .then((response) => {
                    this.processResponse(response);
                })
                .catch((error) => {
                    this.processError(error);
                });
        },
        setupApiCall() {
            this.loader = true;
            this.response.code = null;
            this.response.payload = null;
            axios.defaults.headers = {'Access-Control-Allow-Origin': Config.DOMAIN};
        },
        processResponse(response) {
            this.response.code = response.status;
            if (response.data) {
                this.response.payload = JSON.stringify(response.data, null, 4);
            }
            this.loader = false;
        },
        processError(error) {
            if (typeof error.response !=='undefined' && typeof error.response.status !== 'undefined') {
                this.response.code = error.response.status;
            } else {
                this.response.code = 500;
            }

            if (typeof error.response !=='undefined' && typeof error.response.data !== 'undefined') {
                this.response.payload = error.response.data;
            }

            this.loader = false;
        }
    },
    computed: {
        canCreateCharge() {
            if (this.rules.required(this.description) !== true) {
                return false;
            }

            if (this.rules.counter(this.description) !== true) {
                return false;
            }

            if (this.rules.required(this.amount) !== true) {
                return false;
            }

            if (this.rules.money(this.amount) !== true) {
                return false;
            }

            return true;
        },
    }
};
</script>

<style scoped>
    .api-card {
        width: 280px;
    }

    .charge-field {
        width: 225px;
    }

    a {
        text-decoration: none;
    }
</style>