<template>
    <HeaderMenu v-if="renderMenu" />

    <router-view></router-view>
</template>

<script>
import HeaderMenu from "@/components/HeaderMenu.vue";

export default {
    name: "App",
    components: {
        HeaderMenu,
    },
    computed: {
        renderMenu() {
            return (
                this.$route.path !== "/" && this.$route.path !== "/signup"
            );
        },
    },
};
</script>
