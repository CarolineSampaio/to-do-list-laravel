<template>
    <div class="flex h-screen">
        <section
            class="flex-[70%] h-full bg-[#292929] overflow-hidden animate-slideOut left"
        ></section>

        <section
            class="flex-[30%] h-full animate-slide flex flex-col justify-center items-center right"
        >
            <img
                src="../../assets/logo.png"
                alt="logo do sistema PrioriTask"
                class="mb-4 md:mb-10 w-1/2 md:w-1/4"
            />

            <p class="text-lg md:text-xl text-gray-600 mb-5">Crie sua conta!</p>
            <form
                @submit.prevent="handleCreateAccount"
                class="flex flex-col gap-4 w-4/5 md:w-2/3"
            >
                <div class="flex flex-col">
                    <input
                        v-model="name"
                        type="text"
                        placeholder="Nome completo"
                        class="border rounded p-3 focus:outline-none focus:ring-2 focus:ring-amber-400"
                        :class="{ 'border-red-500': errors.name }"
                    />
                    <p v-if="errors.name" class="text-sm text-red-600 mt-1">
                        {{ errors.name }}
                    </p>
                </div>
                <div class="flex flex-col">
                    <input
                        v-model="email"
                        type="email"
                        placeholder="E-mail"
                        class="border rounded p-3 focus:outline-none focus:ring-2 focus:ring-amber-400"
                        :class="{ 'border-red-500': errors.email }"
                    />
                    <p v-if="errors.email" class="text-sm text-red-600 mt-1">
                        {{ errors.email }}
                    </p>
                </div>
                <div class="flex flex-col">
                    <input
                        v-model="password"
                        type="password"
                        placeholder="Senha"
                        class="border rounded p-3 focus:outline-none focus:ring-2 focus:ring-amber-400"
                        :class="{ 'border-red-500': errors.password }"
                    />
                    <p v-if="errors.password" class="text-sm text-red-600 mt-1">
                        {{ errors.password }}
                    </p>
                </div>
                <div class="flex flex-col">
                    <input
                        v-model="confirmPassword"
                        type="password"
                        placeholder="Confirme a senha"
                        class="border rounded p-3 focus:outline-none focus:ring-2 focus:ring-amber-400"
                        :class="{ 'border-red-500': errors.confirmPassword }"
                    />
                    <p
                        v-if="errors.confirmPassword"
                        class="text-sm text-red-600 mt-1"
                    >
                        {{ errors.confirmPassword }}
                    </p>
                </div>

                <button
                    type="submit"
                    class="bg-amber-400 text-gray-800 font-bold py-3 rounded hover:bg-amber-500 transition-all"
                >
                    Cadastrar
                </button>
            </form>

            <p class="text-gray-600 text-sm mt-2 text-center">
                Ao se inscrever, você concorda com os nossos termos de uso.
            </p>

            <p class="mt-4">
                <router-link
                    to="/"
                    class="text-gray-800 font-bold hover:text-amber-400"
                >
                    Já tem uma conta?
                </router-link>
            </p>

            <div
                v-if="snackbar.show"
                class="fixed top-5 left-1/2 transform -translate-x-1/2 px-4 py-3 rounded shadow-lg text-white"
                :class="
                    snackbar.type === 'success' ? 'bg-green-500' : 'bg-red-500'
                "
            >
                {{ snackbar.message }}
            </div>
        </section>
    </div>
</template>

<script>
import * as yup from "yup";
import { captureErrorYup } from "../utils/captureErrorYup";
import axios from "axios";
import { API_URL } from "../utils/constants";

export default {
    data() {
        return {
            name: "",
            email: "",
            password: "",
            confirmPassword: "",
            snackbar: {
                show: false,
                message: "",
                type: "",
            },
            errors: {},
        };
    },
    methods: {
        handleCreateAccount() {
            const schema = yup.object().shape({
                name: yup
                    .string()
                    .required("Digite seu nome completo.")
                    .matches(
                        /^[\p{L}\s]+$/u,
                        "O nome deve conter apenas letras e espaços."
                    )
                    .min(3, "O nome deve ter no mínimo 3 caracteres.")
                    .max(150, "O nome deve ter no máximo 150 caracteres."),
                email: yup
                    .string()
                    .required("Digite seu e-mail.")
                    .email("Forneça um endereço de email válido.")
                    .max(150, "O e-mail deve ter no máximo 150 caracteres."),
                password: yup
                    .string()
                    .required("Digite sua senha.")
                    .matches(
                        /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{6,25}$/,
                        "A senha deve ter 6 a 25 caracteres, incluindo uma letra maiúscula, um número e um caractere especial (!@#$%^&*)."
                    ),
                confirmPassword: yup
                    .string()
                    .required("Confirme sua senha.")
                    .oneOf(
                        [yup.ref("password")],
                        "As senhas devem ser idênticas."
                    ),
            });

            try {
                schema.validateSync(
                    {
                        name: this.name,
                        email: this.email,
                        password: this.password,
                        confirmPassword: this.confirmPassword,
                    },
                    { abortEarly: false }
                );
                this.errors = {};

                axios
                    .post(`${API_URL}/users`, {
                        name: this.name,
                        email: this.email,
                        password: this.password,
                    })
                    .then(() => {
                        this.name = "";
                        this.email = "";
                        this.password = "";
                        this.confirmPassword = "";
                        this.showSnackbar(
                            "Cadastro realizado com sucesso!",
                            "success"
                        );
                        setTimeout(() => this.$router.push("/"), 2000);
                    })
                    .catch((error) => {
                        this.showSnackbar(
                            (this.errorMessage =
                                error.response.data.message ||
                                "Erro ao cadastrar, tente novamente."),
                            "error"
                        );
                    });
            } catch (error) {
                if (error instanceof yup.ValidationError) {
                    this.errors = captureErrorYup(error);
                }
            }
        },
        showSnackbar(message, type) {
            this.snackbar.message = message;
            this.snackbar.type = type;
            this.snackbar.show = true;
            setTimeout(() => (this.snackbar.show = false), 2000);
        },
    },
};
</script>

<style scoped>
.container {
    display: flex;
    flex-direction: row;
    height: 100%;
}
.background {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.4;
}
section.left {
    flex: 70%;
    height: 100%;
    background-color: #292929;
    overflow: hidden;
    animation: slideOut 1s ease;
    animation-fill-mode: forwards;
}

section.right {
    height: 100%;
    flex: 30%;
    animation: slide 1s ease;
    animation-fill-mode: forwards;
}

@keyframes slide {
    0% {
        flex: 30%;
    }
    100% {
        flex: 60%;
    }
}

@keyframes slideOut {
    0% {
        flex: 70%;
    }
    100% {
        flex: 40%;
    }
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
