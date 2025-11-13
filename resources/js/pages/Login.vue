<template>
  <div class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card shadow-sm p-4" style="max-width: 420px; width: 100%;">
      <div class="text-center mb-4">
        <i class="fa fa-user-md fa-3x text-primary mb-2"></i>
        <h4 class="fw-bold">Staff Login</h4>
        <small class="text-muted">(Doctor / Manager)</small>
      </div>

      <form @submit.prevent="submit">
        <div class="mb-3">
          <label class="form-label fw-semibold">Email</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa fa-envelope"></i></span>
            <input
              v-model="form.email"
              type="email"
              class="form-control"
              placeholder="Enter your email"
              required
            />
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa fa-lock"></i></span>
            <input
              v-model="form.password"
              type="password"
              class="form-control"
              placeholder="Enter your password"
              required
            />
          </div>
        </div>

        <div class="d-grid mt-4">
          <button type="submit" class="btn btn-primary" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            Login
          </button>
        </div>

        <div v-if="error" class="alert alert-danger mt-3 text-center py-2">
          {{ error }}
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import axios from "../bootstrap";
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const form = reactive({ email: "", password: "" });
const error = ref("");
const loading = ref(false);

async function submit() {
  error.value = "";
  loading.value = true;
  try {
    await axios.get("/sanctum/csrf-cookie");
    await axios.post("/api/login", { email: form.email, password: form.password });
    const res = await axios.get("/api/user");
    const user = res.data;

    if (user.role === "manager") {
      router.push("/manager/dashboard");
    } else if (user.role === "doctor") {
      router.push("/doctor/dashboard");
    } else {
      error.value = "Role not allowed here";
      await axios.post("/api/logout");
    }
  } catch (e) {
    error.value = e.response?.data?.message || "Login failed";
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
body {
  background-color: #f8f9fa;
}
.card {
  border-radius: 12px;
}
.input-group-text {
  background-color: #f1f3f5;
}
</style>
