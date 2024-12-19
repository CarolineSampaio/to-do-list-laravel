<template>
    <div class="flex h-screen w-screen m-0 p-0">
        <section class="hidden md:flex flex-1 relative left">
            <div
                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-white min-w-[500px]"
            >
                <h3 class="text-3xl font-semibold">
                    Em qualquer hora ou lugar...
                </h3>
                <span class="text-5xl font-bold">
                    Gerencie <span class="type-it text-amber-400"></span>
                </span>
            </div>
        </section>

        <section
            class="flex flex-col justify-center items-center w-full md:w-1/3 p-4"
        >
            <img
                src="../../assets/logo.png"
                alt="logo do sistema PrioriTask"
                class="mb-4 md:mb-10 w-1/2 md:w-2/5"
            />
            <p class="text-lg text-gray-500 mb-5">Acesse sua conta</p>

            <Loader :show="isLoading" />
            <form
                @submit.prevent="handleLogin"
                class="flex flex-col w-3/4 gap-4"
            >
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">
                        E-mail
                    </label>
                    <input
                        v-model="email"
                        type="email"
                        placeholder="Digite seu e-mail"
                        class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-amber-400"
                        :class="{ 'border-red-500': errors.email }"
                    />
                    <p v-if="errors.email" class="text-sm text-red-600 mt-1">
                        {{ errors.email }}
                    </p>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">
                        Senha
                    </label>
                    <input
                        v-model="password"
                        type="password"
                        placeholder="Digite sua senha"
                        class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-amber-400"
                        :class="{ 'border-red-500': errors.password }"
                    />
                    <p v-if="errors.password" class="text-sm text-red-600 mt-1">
                        {{ errors.password }}
                    </p>
                </div>

                <button
                    type="submit"
                    class="bg-amber-400 text-gray-800 font-bold rounded-lg py-2 hover:bg-amber-500 transition"
                >
                    Entrar
                </button>
            </form>

            <p class="mt-6">
                Ainda não tem conta?
                <router-link
                    to="/register"
                    class="text-amber-500 font-semibold hover:underline"
                >
                    Cadastre-se
                </router-link>
            </p>
            <div
                v-if="loginError"
                class="fixed top-4 right-4 bg-red-600 text-white px-6 py-2 rounded-lg shadow-lg"
            >
                {{ errorMessage }}
            </div>
        </section>
    </div>
</template>

<script>
import * as yup from "yup";
import { captureErrorYup } from "../utils/captureErrorYup";
import axios from "axios";
import TypeIt from "typeit";
import { API_URL } from "../utils/constants";
import Loader from "@/components/Loader.vue";

export default {
    components: {
        Loader,
    },
    data() {
        return {
            email: "",
            password: "",
            errors: {},
            loginError: false,
            errorMessage: "",
            isLoading: false,
        };
    },
    methods: {
        handleLogin() {
            const schema = yup.object().shape({
                email: yup
                    .string()
                    .required("E-mail é obrigatório.")
                    .email("E-mail inválido.")
                    .max(150, "O e-mail não pode ter mais de 150 caracteres."),

                password: yup
                    .string()
                    .required("Senha é obrigatória.")
                    .min(6, "A senha deve ter no mínimo 6 caracteres.")
                    .max(25, "A senha não pode ter mais de 25 caracteres.")
                    .matches(
                        /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{6,25}$/,
                        "A senha deve conter pelo menos uma letra maiúscula, um número e um caractere especial."
                    ),
            });

            try {
                schema.validateSync(
                    {
                        email: this.email,
                        password: this.password,
                    },
                    { abortEarly: false }
                );

                this.errors = {};
                this.isLoading = true;
                document.body.style.overflow = "hidden";

                axios
                    .post(`${API_URL}/login`, {
                        email: this.email,
                        password: this.password,
                    })
                    .then((response) => {
                        localStorage.setItem(
                            "logged_user",
                            JSON.stringify(response.data.data.token)
                        );
                        this.$router.push("/home");
                    })
                    .catch((error) => {
                        if (error.response) {
                            const status = error.response.status;
                            const responseData = error.response.data;

                            if (status === 401 || status === 422) {
                                this.errorMessage =
                                    responseData.message ||
                                    "Email ou senha incorretos.";

                                this.loginError = true;
                                setTimeout(() => {
                                    this.loginError = false;
                                }, 3000);
                            } else if (status === 500) {
                                this.errorMessage =
                                    "Erro interno. Tente novamente mais tarde.";

                                this.loginError = true;
                                setTimeout(() => {
                                    this.loginError = false;
                                }, 3000);
                            }
                        } else {
                            this.errorMessage =
                                "Ocorreu um erro. Tente novamente mais tarde.";

                            this.loginError = true;
                            setTimeout(() => {
                                this.loginError = false;
                            }, 3000);
                        }
                    })
                    .finally(() => {
                        this.isLoading = false;
                        document.body.style.overflow = "";
                    });
            } catch (error) {
                if (error instanceof yup.ValidationError) {
                    this.errors = captureErrorYup(error);
                }
            }
        },
        typeIt() {
            new TypeIt(".type-it", {
                speed: 150,
                startDelay: 1000,
                waitUntilVisible: true,
                loop: true,
            })
                .type("tarefas!", { delay: 400 })
                .pause(600)
                .delete(8)
                .type("metas!", { delay: 400 })
                .pause(600)
                .delete(6)
                .type("projetos!", { delay: 400 })
                .pause(600)
                .delete(9)
                .type("ideias!", { delay: 400 })
                .pause(600)
                .delete(9)
                .type("anotações!", { delay: 400 })
                .pause(1000)
                .delete(12)
                .go();
        },
    },
    mounted() {
        document.querySelector(".type-it").innerHTML = "";
        this.typeIt();
    },
};
</script>

<style scoped>
.flex {
    display: flex;
}

section.left {
    background-color: #292929;
    width: 70%;
    overflow: hidden;
}

.background {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.4;
    z-index: -1;
}

.float {
    position: absolute;
    top: 54%;
    left: 35%;
    transform: translate(-50%, -50%);
    color: #fff4d3;
    font-size: 2rem;
    font-weight: bold;
    min-width: 55vh;
    z-index: 1;
}

.type-it {
    font-weight: bold;
    color: #ffc107;
}

section.right {
    width: 30%;
}

.v-form {
    width: 70%;
    gap: 8px;
}

a {
    color: #292929;
    font-weight: bold;
    text-decoration: none;
}

a:hover {
    color: #ffc107;
}
</style>
