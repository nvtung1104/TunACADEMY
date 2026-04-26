<template>
    <RouterView />
</template>

<script setup>
import { onMounted } from "vue";
import { useAuthStore } from "@stores/auth";

const auth = useAuthStore();

onMounted(async () => {
    if (auth.token) {
        await auth.fetchUser().catch(() => auth.logout());
    }
});
</script>
