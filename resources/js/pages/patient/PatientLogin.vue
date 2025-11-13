<template>
  <div class="container d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card shadow-sm p-4" style="max-width: 420px; width: 100%;">
      <h3 class="text-center mb-4 text-primary">Patient Login</h3>

      <form @submit.prevent="submit">
        <div class="mb-3">
          <label for="email" class="form-label fw-semibold">Email address</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="form-control"
            placeholder="Enter your email"
            required
            :disabled="loading"
          />
        </div>

        <div class="mb-3">
          <label for="password" class="form-label fw-semibold">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="form-control"
            placeholder="Enter your password"
            required
            :disabled="loading"
          />
        </div>

        <div class="d-grid mt-4">
          <button type="submit" class="btn btn-primary btn-lg" :disabled="loading">
            <span v-if="!loading">Login</span>
            <span v-else class="d-flex justify-content-center align-items-center">
              <span class="spinner-border spinner-border-sm me-2" role="status"></span>
              Logging in...
            </span>
          </button>
        </div>

        <div v-if="error" class="alert alert-danger mt-3 mb-0 py-2 text-center">
          {{ error }}
        </div>
      </form>

      <p class="text-center mt-4 mb-0 text-muted">
        Don’t have an account?
        <a href="#" class="text-decoration-none">Register here</a>
      </p>
    </div>

    <!-- Optional Full-Screen Loader Overlay -->
    <div
      v-if="loading"
      class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-white bg-opacity-75"
      style="z-index: 1050;"
    >
      <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import axios from '../../bootstrap';
import { useRouter } from 'vue-router';

const form = reactive({
  email: '',
  password: ''
});
const error = ref('');
const loading = ref(false);
const router = useRouter();

const submit = async () => {
  error.value = '';
  loading.value = true;

  try {
    await axios.get('/sanctum/csrf-cookie');
    await axios.post('/api/patient/login', {
      email: form.email,
      password: form.password
    });
    router.push('/dashboard');
  } catch (e) {
    console.error(e);
    error.value = e.response?.data?.message || 'Login failed';
  } finally {
    loading.value = false;
  }
};
</script>
