<template>
    <div class="container mx-auto p-4 sm:p-6 md:mt-20 mt-6">
        <div
            class="bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-3xl p-6 mb-6 shadow-lg"
        >
            <div
                class="flex flex-col sm:flex-row justify-between items-center sm:items-start"
            >
                <div class="flex flex-col items-center sm:items-start">
                    <h2 class="text-2xl font-bold text-white mb-2">
                        Bem-vindo!
                    </h2>
                    <p class="text-lg text-white">
                        Vamos organizar suas tarefas e transformar suas metas em
                        conquistas. Comece agora!
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between gap-6">
            <div
                class="bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-2xl p-6 flex-1 shadow-lg"
            >
                <div class="flex flex-col items-center sm:items-start mb-4">
                    <img
                        src="../../assets/tasks.svg"
                        alt="Ícone de tarefas"
                        class="h-32 mb-4 mx-auto mt-6"
                    />
                    <h3 class="text-4xl font-bold text-white mx-auto mt-6">
                        {{ amountTasks }}
                    </h3>
                    <p class="text-2xl text-white mx-auto mt-4">Tarefas Cadastradas</p>
                </div>
                <router-link to="/tasks">
                    <button
                        class="w-full mt-6 py-2 bg-gray-800 text-white rounded-lg font-semibold hover:bg-gray-900 transition"
                    >
                    Gerenciar Tarefas
                    </button>
                </router-link>
            </div>

            <div
                class="bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-2xl p-6 flex-1 shadow-lg"
            >
                <div class="flex flex-col items-center sm:items-start mb-4">
                    <img
                        src="../../assets/categories.svg"
                        alt="Ícone de categorias"
                        class="h-32 mb-4 mx-auto mt-6"
                    />
                    <h3 class="text-4xl font-bold text-white mx-auto mt-6">
                        {{ amountCategories }}
                    </h3>
                    <p class="text-2xl text-white mx-auto mt-4">Categorias Cadastradas</p>
                </div>
                <router-link to="/categories">
                    <button
                        class="w-full mt-6 py-2 bg-gray-800 text-white rounded-lg font-semibold hover:bg-gray-900 transition"
                    >
                    Gerenciar Categorias
                    </button>
                </router-link>
            </div>
        </div>

        <div
            v-if="snackbarError"
            class="mt-4 bg-red-600 text-white p-4 rounded-lg"
        >
            Erro ao carregar informações do dashboard!
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { API_URL } from "../utils/constants";

export default {
    data() {
        return {
            amountTasks: 0,
            amountCategories: 0,
            snackbarError: false,
            duration: 3000,
            token: this.cleanToken(),
        };
    },
    mounted() {
        this.getInfo();
    },
    methods: {
        cleanToken() {
            return localStorage.getItem("logged_user")
                ? localStorage
                      .getItem("logged_user")
                      .replace(/^.*?\|/, "")
                      .replace(/"/g, "")
                : "";
        },
        authenticateUser() {
            const storedUser = localStorage.getItem("logged_user");
            if (storedUser) {
                this.user = JSON.parse(storedUser);
            } else {
                this.$router.push("/");
            }
        },

        getInfo() {
            axios
                .get(`${API_URL}/tasks`, {
                    headers: {
                        Authorization: `Bearer ${this.token}`,
                    },
                })
                .then((response) => {
                    this.amountTasks = response.data.data.length;
                })
                .catch((error) => {
                    console.log(error);
                    this.snackbarError = true;
                });

            axios
                .get(`${API_URL}/categories`, {
                    headers: {
                        Authorization: `Bearer ${this.token}`,
                    },
                })
                .then((response) => {
                    this.amountCategories = response.data.data.length;
                })
                .catch((error) => {
                    console.log(error);
                    this.snackbarError = true;
                });
        },
    },
};
</script>

<style scoped>
.container {
    max-width: 1200px;
}
</style>
