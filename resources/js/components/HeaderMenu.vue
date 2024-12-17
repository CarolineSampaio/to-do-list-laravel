<template>
    <header>
        <div class="menu">
            <img
                src="../../assets/logo_menu.png"
                alt="logo do sistema PrioriTask"
                @click="redirectToHome"
                class="md:ml-10 ml-4"
            />

            <!-- desktop -->
            <nav class="hidden md:flex gap-4 mr-10">
                <router-link to="/home">Página Inicial</router-link>
                <router-link to="/tasks">Tarefas</router-link>
                <router-link to="/categories">Categorias</router-link>
                <router-link to="/" @click="logout">Logout</router-link>
            </nav>

            <!-- mobile-->
            <div class="flex md:hidden items-center mt-auto md:mr-10 mr-4">
                <button @click="toggleMobileMenu" class="text-white">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-8 w-8"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>
            </div>
        </div>

        <div
            v-show="mobileMenuOpen"
            class="md:hidden absolute top-16 left-0 right-0 bg-[#292929] text-white p-4 z-10"
        >
            <ul class="flex flex-col gap-4">
                <li>
                    <router-link to="/home" @click="toggleMobileMenu"
                        >Página Inicial</router-link
                    >
                </li>
                <li>
                    <router-link to="/tasks" @click="toggleMobileMenu"
                        >Tarefas</router-link
                    >
                </li>
                <li>
                    <router-link to="/categories" @click="toggleMobileMenu"
                        >Categorias</router-link
                    >
                </li>
                <li>
                    <router-link to="/" @click="logout">Logout</router-link>
                </li>
            </ul>
        </div>
    </header>
</template>

<script>
export default {
    name: "HeaderMenu",
    data() {
        return {
            mobileMenuOpen: false,
        };
    },
    methods: {
        redirectToHome() {
            window.location.href = "/home";
        },
        logout() {
            localStorage.removeItem("logged_user");
            window.location.href = "/";
        },
        toggleMobileMenu() {
            this.mobileMenuOpen = !this.mobileMenuOpen;
        },
    },
};
</script>

<style scoped>
header {
    height: 80px;
    background-color: #292929;
}

img {
    margin-top: 10px;
    height: 60px;
    cursor: pointer;
}

.menu {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

nav a {
    color: white;
    text-decoration: none;
    font-size: 1.2rem;
    font-weight: bold;
    padding: 5px 10px;
}

nav a:hover {
    background-color: white;
    color: #ffca28;
    padding: 5px 10px;
    border-radius: 5px;
}

.md\\:hidden {
    display: none;
}

@media (max-width: 790px) {
    .md\:flex {
        display: none;
    }
    .md\:hidden {
        display: block;
    }
    .flex-col {
        display: flex;
        flex-direction: column;
    }
    .gap-4 {
        gap: 1rem;
    }
}
</style>
