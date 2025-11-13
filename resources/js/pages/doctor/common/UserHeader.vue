<template>
    <div class="card shadow-sm p-3 mb-3 d-flex flex-md-row justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">Welcome, {{ user.name }}</h5>
            <p class="text-muted mb-0">Role: {{ user.role }}</p>
        </div>
        <button @click="logout" class="btn btn-outline-danger btn-sm mt-3 mt-md-0">
            <i class="bi bi-box-arrow-right"></i> Logout
        </button>
    </div>
</template>
<script setup>
    import axios from "../../../bootstrap";
    import { useRouter } from "vue-router";
    const router = useRouter();

    async function logout() {
        await axios.post("/api/logout");
        router.push("/");
    }
</script>
