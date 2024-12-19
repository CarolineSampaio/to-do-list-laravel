<template>
    <div class="page px-10 md:px-52 w-full md:mt-14 mt-6 md:mb-14 mb-6">
        <div class="flex items-center justify-center md:justify-start py-4">
            <h1 class="text-gray-700 text-2xl md:text-4xl font-medium">
                Tarefas
            </h1>
            <i class="fa-solid fa-list-check text-amber-400 text-4xl mx-4"></i>
        </div>

        <div class="bg-white shadow-lg rounded-lg p-4 mx-auto">
            <p class="text-gray-700 text-lg font-medium mb-2">{{ taskId ? 'Edição de Tarefas' : 'Cadastro de Tarefas' }}
            </p>
            <form @submit.prevent="addTask" class="flex flex-col sm:flex-row gap-4">
                <input v-model="title" type="text" placeholder="Digite o título da tarefa"
                    class="border px-4 py-2 rounded-lg w-full" />
                <input v-model="description" type="text" placeholder="Descrição da tarefa"
                    class="border px-4 py-2 rounded-lg w-full" />
                <select v-model="category_id" class="border px-4 py-2 rounded-lg w-full">
                    <option value="" selected>Selecione a categoria</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.title }}</option>
                </select>
                <button type="submit"
                    class="bg-gray-800 text-amber-400 font-bold py-2 px-8 rounded-lg w-full sm:w-1/3 mt-4 sm:mt-0">
                    {{ taskId ? 'Editar' : 'Cadastrar' }}
                </button>
            </form>

            <Loader :show="isLoading" />

            <p class="mt-10 mx-32 flex justify-end text-sm font-bold">Filtros de Pesquisa</p>
            <div class="mt-2 flex justify-end gap-2">
                <select v-model="filterCategory" class="border px-2 py-1 rounded-lg text-sm w-48">
                    <option value="">Categoria</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.title }}</option>
                </select>
                <select v-model="filterCompleted" class="border px-2 py-1 rounded-lg text-sm w-48">
                    <option value="">Status</option>
                    <option value="true">Concluídas</option>
                    <option value="false">Não Concluídas</option>
                </select>
            </div>

            <div class="mt-4 relative">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr>
                            <th class="text-center font-bold py-2 px-4" style="color: #292929" v-if="!noTasks">Lista de
                                Tarefas</th>
                            <th class="text-center font-bold py-2 px-4" style="color: #292929" v-if="!noTasks">Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="noTasks">
                            <td colspan="2" class="text-center py-10 border-t">
                                Não há tarefas cadastradas.
                            </td>
                        </tr>
                        <tr v-for="task in filteredTasks" :key="task.id" class="min-h-20">
                            <td class="py-2 px-4  custom-md w-2/5" style="color: #292929">
                                <input class="h-4 w-4" type="checkbox" :checked="task.is_completed"
                                    @change="toggleComplete(task)">
                                <span class="pl-3 flex-1"
                                    :class="{ 'line-through text-gray-400': task.is_completed }">{{ task.title }}</span>
                                <p class="text-xs px-6" style="text-align: justify;"
                                    :class="task.is_completed ? 'text-gray-300' : 'text-gray-500'">{{ task.description
                                    }}</p>
                                <p v-if="task.is_completed && task.completed_by"
                                    class="text-xs px-6 text-gray-400 italic mt-2">
                                    Finalizado por: {{ task.completed_by.name }}
                                </p>
                            </td>
                            <td class="table-cell text-center space-x-4 py-2 px-4">
                                <button @click="editTask(task)" :disabled="isLoading"
                                    class="bg-amber-400 text-gray-800 font-bold py-2 px-4 rounded-lg">
                                    <i class="fa-solid fa-file-pen"></i>
                                </button>
                                <button @click="deleteTask(task.id)" :disabled="isLoading"
                                    class="bg-gray-800 text-amber-400 font-bold py-2 px-4 rounded-lg">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <div v-if="snackbarSuccess" class="bg-green-500 text-white py-2 px-4 rounded mt-4 fixed right-60 top-32">
                {{ snackbarMessage }}
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
            title: "",
            description: "",
            category_id: "",

            tasks: [],
            categories: [],
            filterCategory: "",
            filterCompleted: "",
            errors: {},
            snackbarSuccess: false,
            snackbarError: false,
            loadError: false,
            isLoading: false,
            taskId: this.$route?.params?.id,
            noTasks: false
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
                    this.tasks = response.data.data.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at));
                    if (this.tasks.length === 0) this.noTasks = true;
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
                description: yup.string().nullable(),
                category_id: yup.string().nullable(),
            });

            try {
                schema.validateSync(
                    {
                        title: this.title,
                        description: this.description,
                        category_id: this.category_id
                    },
                    { abortEarly: false }
                );
                this.errors = {};
                this.isLoading = true;
                document.body.style.overflow = "hidden";

                if (this.taskId) {
                    axios
                        .put(
                            `${API_URL}/tasks/${this.taskId}`,
                            {
                                title: this.title,
                                description: this.description || null,
                                category_id: this.category_id || null,
                            },
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

                            this.title = "";
                            this.description = "";
                            this.category_id = "";

                            this.snackbarSuccess = true;
                            this.snackbarMessage = "Tarefa editada com sucesso!";

                            setTimeout(() => {
                                this.snackbarSuccess = false;
                            }, 3000);

                            this.taskId = null;
                            this.$router.push("/tasks");
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
                            {
                                title: this.title,
                                description: this.description || null,
                                category_id: this.category_id || null,
                            },
                            {
                                headers: {
                                    Authorization: `Bearer ${this.cleanToken()}`,
                                },
                            }
                        )
                        .then((response) => {
                            this.tasks.push(response.data.data);

                            this.snackbarSuccess = true;
                            this.snackbarMessage = "Tarefa cadastrada com sucesso!";
                            this.title = "";
                            this.description = "";
                            this.category_id = "";
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
            this.title = task.title;
            this.description = task.description;
            this.category_id = task.category_id;
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
                    this.snackbarMessage = "Tarefa excluída com sucesso!";
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
                    { is_completed: !task.is_completed },
                    {
                        headers: {
                            Authorization: `Bearer ${this.cleanToken()}`,
                        },
                    }
                )
                .then((response) => {
                    task.is_completed = response.data.data.is_completed;
                    task.completed_at = response.data.data.completed_at;
                    task.completed_by = response.data.data.completed_by;
                    this.tasks.sort((a, b) => new Date(b.completed_at) - new Date(a.completed_at));
                })
                .catch((error) => {
                    this.snackbarError = true;
                    this.snackbarMessage = "Erro ao alterar status da tarefa!";
                });
        },
    },

    computed: {
        filteredTasks() {
            let filteredTasks = this.tasks.filter((task) => {
                let matchesCategory =
                    !this.filterCategory || task.category_id === this.filterCategory;
                let matchesCompletion =
                    this.filterCompleted === "" ||
                    (this.filterCompleted === "true" ? task.is_completed : !task.is_completed);
                return matchesCategory && matchesCompletion;
            });

            filteredTasks.sort((a, b) => {
                if (a.is_completed === b.is_completed) {
                    return new Date(b.completed_at) - new Date(a.completed_at);
                }
                return a.is_completed ? 1 : -1;
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
