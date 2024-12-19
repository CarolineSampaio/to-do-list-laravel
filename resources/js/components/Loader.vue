<template>
    <div v-if="show" class="loader-overlay">
        <div class="loader"></div>
    </div>
</template>

<script>
export default {
    props: {
        show: {
            type: Boolean,
            required: true,
        },
    },
};
</script>

<style scoped>
.loader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10;
}

.loader {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #FBBF24;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
