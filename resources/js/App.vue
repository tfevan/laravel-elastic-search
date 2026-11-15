<script setup lang="ts">
import { ref } from "vue";

const message = ref<string>("Laravel + Vue 3 + TypeScript is working");
</script>

<template>
    <main class="p-8">
        <h1 class="text-2xl font-bold">{{ message }}</h1>
    </main>
</template>
