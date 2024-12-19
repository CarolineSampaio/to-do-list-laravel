<template>
    <div class="page px-10 md:px-52 w-full md:mt-14 mt-6 md:mb-14 mb-6">
        <div class="flex items-center justify-center md:justify-start py-4">
            <h1 class="text-gray-700 text-2xl md:text-4xl font-medium">
                Categorias
            </h1>
            <i class="fa-solid fa-layer-group text-amber-400 text-4xl mx-4"></i>
        </div>

        <div class="bg-white shadow-lg rounded-lg p-4 mx-auto">
            <p class="text-gray-700 text-lg font-medium mb-2">
                {{ categoryId ? 'Edição de Categorias' : 'Cadastro de Categorias' }} </p>
            <form @submit.prevent="addCategory" class="flex flex-col sm:flex-row gap-4">
                <input v-model="title" type="text" placeholder="Digite o nome da categoria" :class="{
                    'border-red-500': errors.title,
                    'border-gray-300': !errors.title,
                }" class="border px-4 py-2 rounded-lg w-full" />

                <button type="submit"
                    class="bg-gray-800 text-amber-400 font-bold py-2 px-8 rounded-lg w-full sm:w-1/3 mt-4 sm:mt-0">
                    {{ categoryId ? 'Editar' : 'Cadastrar' }}

                </button>
            </form>
            <p v-if="errors.title" class="text-sm text-red-600 mt-1">
                {{ errors.title }}
            </p>

            <Loader :show="isLoading" />
            <div class="mt-8 relative">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr>
                            <th class="text-center font-bold py-2 px-4" style="color: #292929" v-if="!noCategories">
                                Lista de Categorias
                            </th>
                            <th class="text-center font-bold py-2 px-4" style="color: #292929" v-if="!noCategories">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="noCategories">
                            <td colspan="2" class="text-center py-10 border-t">
                                Não há categorias cadastradas.
                            </td>
                        </tr>
                        <tr v-for="category in categories" :key="category.id">
                            <td class="py-2 px-4  custom-md w-2/5" style="color: #292929">
                                {{ category.title }}
                            </td>
                            <td class="table-cell text-center space-x-4 py-2 px-4">
                                <button @click="editCategory(category)" :disabled="isLoading"
                                    class="bg-amber-400 text-gray-800 font-bold py-2 px-4 rounded-lg">
                                    <i class="fa-solid fa-file-pen"></i>
                                </button>
                                <button @click="deleteCategory(category.id)" :disabled="isLoading"
                                    class="bg-gray-800 text-amber-400 font-bold py-2 px-4 rounded-lg">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div>
                <div v-if="snackbarSuccess"
                    class="bg-green-500 text-white py-2 px-4 rounded mt-4 fixed right-60 top-32">
                    {{ snackbarMessage }}
                </div>
                <div v-if="snackbarError" class="mt-4 bg-red-600 text-white p-4 rounded-lg top-20 right-4 fixed">
                    {{ snackbarMessage }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import * as yup from "yup";
import { captureErrorYup } from "../utils/captureErrorYup";
import axios from "axios";
import { API_URL } from "../utils/constants";
import Loader from "@/components/Loader.vue";

export default {
    components: {
        Loader,
    },
    data() {
        return {
            title: "",
            categories: [],
            errors: {},
            snackbarSuccess: false,
            snackbarError: false,
            loadError: false,
            isLoading: false,
            categoryId: this.$route?.params?.id,
            noCategories: false
        };
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

        getCategories() {
            axios
                .get(`${API_URL}/categories`, {
                    headers: {
                        Authorization: `Bearer ${this.cleanToken()}`,
                    },
                })
                .then((response) => {
                    this.categories = response.data.data.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at));
                    if(this.categories.length === 0) this.noCategories = true;
                })

                .catch((error) => {
                    if (error.response.status === 401) {
                        this.snackbarError = true;
                        this.snackbarMessage = "Sua sessão expirou. Faça login novamente.";

                        setTimeout(() => {
                            localStorage.removeItem("logged_user");
                            this.$router.push("/");
                        }, 3000);
                    } else {
                        this.snackbarError = true;
                        this.snackbarMessage = "Erro ao carregar informações do dashboard!";
                    }
                });
        },

        addCategory() {
            const schema = yup.object().shape({
                title: yup
                    .string()
                    .required("Por favor, digite o nome da categoria.")
                    .max(150),
            });

            try {
                schema.validateSync(
                    { title: this.title },
                    { abortEarly: false }
                );
                this.errors = {};
                this.isLoading = true;
                document.body.style.overflow = 'hidden';

                if (this.categoryId) {
                    axios
                        .put(
                            `${API_URL}/categories/${this.categoryId}`,
                            { title: this.title },
                            {
                                headers: {
                                    Authorization: `Bearer ${this.cleanToken()}`,
                                },
                            }
                        )
                        .then((response) => {
                            const categoryIndex = this.categories.findIndex(
                                (category) => category.id === this.categoryId
                            );
                            this.categories[categoryIndex] = response.data.data;

                            this.title = "";
                            this.categoryId = "";

                            this.snackbarSuccess = true;
                            this.snackbarMessage = "Categoria editada com sucesso!";

                            setTimeout(() => {
                                this.snackbarSuccess = false;
                            }, 3000);

                            this.categoryId = null;
                            this.$router.push('/categories');
                        })
                        .catch((error) => {
                            this.snackbarError = true;
                            this.snackbarMessage = "Erro ao editar categoria";
                        })
                        .finally(() => {
                            this.isLoading = false;
                            document.body.style.overflow = '';
                        });
                } else {
                    axios
                        .post(
                            `${API_URL}/categories`,
                            { title: this.title },
                            {
                                headers: {
                                    Authorization: `Bearer ${this.cleanToken()}`,
                                },
                            }
                        )
                        .then((response) => {
                            this.categories.unshift(response.data.data);

                            this.title = "";
                            this.snackbarSuccess = true;
                            this.snackbarMessage = "Categoria cadastrada com sucesso!";

                            setTimeout(() => {
                                this.snackbarSuccess = false;
                            }, 3000);
                        })
                        .catch((error) => {
                            this.snackbarError = true;
                            this.snackbarMessage = "Erro ao cadastrar categoria";
                        })
                        .finally(() => {
                            this.isLoading = false;
                            document.body.style.overflow = '';
                        });
                }
            } catch (error) {
                if (error instanceof yup.ValidationError) {
                    this.errors = captureErrorYup(error);
                } else {
                    this.snackbarError = true;
                    this.snackbarMessage = "Erro na manipulação da categoria";
                }
            }
        },

        editCategory(category) {
            this.$router.push(`/category/${category.id}/edit`);
            this.title = category.title;
            this.categoryId = category.id;
        },

        deleteCategory(categoryId) {
            this.isLoading = true;
            document.body.style.overflow = 'hidden';

            axios
                .delete(`${API_URL}/categories/${categoryId}`, {
                    headers: {
                        Authorization: `Bearer ${this.cleanToken()}`,
                    },
                })
                .then(() => {
                    this.snackbarSuccess = true;
                    this.snackbarMessage = "Categoria removida com sucesso!";
                    this.categories = this.categories.filter(
                        (category) => category.id !== categoryId
                    );
                })
                .catch((error) => {
                    this.snackbarError = true;
                    this.snackbarMessage = "Erro ao excluir categoria";
                })
                .finally(() => {
                    this.isLoading = false;
                    document.body.style.overflow = '';
                });
        },
    },
    created() {
        this.authenticateUser();
        this.getCategories();
    },
};
</script>

<style scoped>
.page {
    width: 100%;
    min-height: 100%;
}

.bg-white {
    margin: 0 auto;
    width: 100%;
}

table th,
table td {
    border-bottom: 1px solid #e2e8f0;
    font-weight: 500;
    color: #292929;
}

@media (min-width: 800px) {
    .custom-md {
        width: 50%;
    }
}

@media (min-width: 900px) {
    .custom-md {
        width: 60%;
    }
}

@media (min-width: 1200px) {
    .custom-md {
        width: 80%;
    }
}
</style>
