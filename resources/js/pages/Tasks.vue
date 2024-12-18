<template>
    <div class="page px-10 md:px-52 w-full md:mt-14 mt-6 md:mb-14 mb-6">
        <div class="flex items-center justify-center md:justify-start py-4">
            <h1 class="text-gray-700 text-2xl md:text-4xl font-medium">
                Tarefas
            </h1>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-amber-400 ml-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </div>

        <div class="bg-white shadow-lg rounded-lg p-4 mx-auto">
            <p class="text-gray-700 text-lg font-medium mb-2">{{ taskId ? 'Edição de Tarefas' : 'Cadastro de Tarefas' }}</p>
            <form @submit.prevent="addTask" class="flex flex-col sm:flex-row gap-4">
                <input v-model="newTask.title" type="text" placeholder="Digite o título da tarefa"
                    class="border px-4 py-2 rounded-lg w-full" />
                <input v-model="newTask.description" type="text" placeholder="Descrição da tarefa"
                    class="border px-4 py-2 rounded-lg w-full" />
                <select v-model="newTask.category_id" class="border px-4 py-2 rounded-lg w-full">
                    <option value="" selected>Selecione a categoria</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.title }}</option>
                </select>
                <button type="submit"
                    class="bg-gray-800 text-amber-400 font-bold py-2 px-8 rounded-lg w-full sm:w-1/3 mt-4 sm:mt-0">
                    {{ taskId ? 'Editar' : 'Cadastrar' }}
                </button>
            </form>

            <Loader :show="isLoading" />

            <div class="mt-8 flex gap-4">
                <input v-model="searchQuery" type="text" placeholder="Buscar tarefa..."
                    class="border px-4 py-2 rounded-lg w-full sm:w-1/3" />
                <select v-model="filterCategory" class="border px-4 py-2 rounded-lg w-full sm:w-1/3">
                    <option value="">Filtrar por Categoria</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.title }}</option>
                </select>
                <select v-model="filterCompleted" class="border px-4 py-2 rounded-lg w-full sm:w-1/3">
                    <option value="">Filtrar por Concluída</option>
                    <option value="true">Concluídas</option>
                    <option value="false">Não Concluídas</option>
                </select>
            </div>

            <div class="mt-8 relative">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr>
                            <th class="text-center font-bold py-2 px-4" style="color: #292929">Lista de Tarefas</th>
                            <th class="text-center font-bold py-2 px-4" style="color: #292929">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="task in filteredTasks" :key="task.id" class="min-h-20">
                            <td class="py-2 px-4  md:w-4/5" style="color: #292929">
                                <input type="checkbox" :checked="task.completed" @change="toggleComplete(task)" />
                                <span class="pl-3 flex-1">{{ task.title }}</span>
                                <p class="text-xs text-gray-500 px-6" style="text-align: justify;">{{ task.description
                                    }}</p>
                            </td>
                            <td class="py-2 px-4 flex justify-center space-x-4">
                                <button @click="editTask(task)" :disabled="isLoading"
                                    class="bg-amber-400 text-gray-800 font-bold py-2 px-8 rounded-lg">
                                    Editar
                                </button>
                                <button @click="deleteTask(task.id)" :disabled="isLoading"
                                    class="bg-gray-800 text-amber-400 font-bold py-2 px-8 rounded-lg">
                                    Excluir
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <div v-if="snackbarSuccess" class="bg-green-500 text-white py-2 px-4 rounded mt-4 fixed right-60 top-32">
                {{ taskId ? 'Tarefa editada com sucesso!' : 'Tarefa cadastrada com sucesso!' }}
            </div>
            <div v-if="snackbarError" class="mt-4 bg-red-600 text-white p-4 rounded-lg top-20 right-4 fixed">
                {{ snackbarMessage }}
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
            newTask: {
                title: "",
                description: "",
                category_id: "",
            },
            tasks: [],
            categories: [],
            searchQuery: "",
            filterCategory: "",
            filterCompleted: "",
            errors: {},
            snackbarSuccess: false,
            snackbarError: false,
            loadError: false,
            isLoading: false,
            taskId: this.$route?.params?.id,
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
                    this.categories = response.data.data;
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
        getTasks() {
            axios
                .get(`${API_URL}/tasks`, {
                    headers: {
                        Authorization: `Bearer ${this.cleanToken()}`,
                    },
                })
                .then((response) => {
                    this.tasks = response.data.data;
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

        addTask() {
            const schema = yup.object().shape({
                title: yup
                    .string()
                    .required("Por favor, digite o título da tarefa.")
                    .max(150),
                description: yup.string(),
                category_id: yup.string(),
            });

            try {
                schema.validateSync(this.newTask, { abortEarly: false });
                this.errors = {};
                this.isLoading = true;
                document.body.style.overflow = "hidden";

                if (this.taskId) {
                    axios
                        .put(
                            `${API_URL}/tasks/${this.taskId}`,
                            this.newTask,
                            {
                                headers: {
                                    Authorization: `Bearer ${this.cleanToken()}`,
                                },
                            }
                        )
                        .then((response) => {
                            const taskIndex = this.tasks.findIndex(
                                (task) => task.id === this.taskId
                            );
                            this.tasks[taskIndex] = response.data.data;
                            this.clearForm();
                            this.snackbarSuccess = true;
                            setTimeout(() => {
                                this.snackbarSuccess = false;
                            }, 3000);
                        })
                        .catch((error) => {
                            this.snackbarError = true;
                            this.snackbarMessage = "Erro ao editar tarefa!";
                        })
                        .finally(() => {
                            this.isLoading = false;
                            document.body.style.overflow = "";
                        });
                } else {
                    axios
                        .post(
                            `${API_URL}/tasks`,
                            this.newTask,
                            {
                                headers: {
                                    Authorization: `Bearer ${this.cleanToken()}`,
                                },
                            }
                        )
                        .then((response) => {
                            this.tasks.push(response.data.data);
                            this.clearForm();
                            this.snackbarSuccess = true;
                            setTimeout(() => {
                                this.snackbarSuccess = false;
                            }, 3000);
                        })
                        .catch((error) => {
                            this.snackbarError = true;
                            this.snackbarMessage = "Erro ao cadastrar tarefa!";
                        })
                        .finally(() => {
                            this.isLoading = false;
                            document.body.style.overflow = "";
                        });
                }
            } catch (error) {
                this.errors = captureErrorYup(error);
            }
        },

        editTask(task) {
            this.$router.push(`/task/${task.id}/edit`);
            this.newTask = { ...task };
            this.taskId = task.id;
        },

        deleteTask(taskId) {
            this.isLoading = true;
            document.body.style.overflow = "hidden";

            axios
                .delete(`${API_URL}/tasks/${taskId}`, {
                    headers: {
                        Authorization: `Bearer ${this.cleanToken()}`,
                    },
                })
                .then(() => {
                    this.tasks = this.tasks.filter((task) => task.id !== taskId);
                    this.snackbarSuccess = true;
                    setTimeout(() => {
                        this.snackbarSuccess = false;
                    }, 3000);
                })
                .catch((error) => {
                    this.snackbarError = true;
                    this.snackbarMessage = "Erro ao excluir tarefa!";
                })
                .finally(() => {
                    this.isLoading = false;
                    document.body.style.overflow = "";
                });
        },

        toggleComplete(task) {
            axios
                .patch(
                    `${API_URL}/tasks/${task.id}/complete`,
                    { completed: !task.completed },
                    {
                        headers: {
                            Authorization: `Bearer ${this.cleanToken()}`,
                        },
                    }
                )
                .then((response) => {
                    task.completed = response.data.data.completed;
                })
                .catch((error) => {
                    this.snackbarError = true;
                    this.snackbarMessage = "Erro ao alterar status da tarefa!";
                });
        },

        clearForm() {
            this.newTask = {
                title: "",
                description: "",
                category_id: "",
            };

            this.taskId = null;
        },
    },

    computed: {
        filteredTasks() {
            let filteredTasks = this.tasks.filter((task) => {
                let matchesCategory =
                    !this.filterCategory || task.category_id === this.filterCategory;
                let matchesCompletion =
                    this.filterCompleted === "" ||
                    (this.filterCompleted === "true" ? task.completed : !task.completed);
                let matchesSearch =
                    task.title.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    task.description.toLowerCase().includes(this.searchQuery.toLowerCase());
                return matchesCategory && matchesCompletion && matchesSearch;
            });

            filteredTasks.sort((a, b) => {
                if (a.completed === b.completed) {
                    return b.updated_at.localeCompare(a.updated_at);
                }
                return a.completed ? 1 : -1;
            });

            return filteredTasks;
        },
    },

    created() {
        this.authenticateUser();
        this.getTasks();
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
</style>
