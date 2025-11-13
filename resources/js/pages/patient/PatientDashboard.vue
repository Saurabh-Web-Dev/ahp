<template>
  <div style="padding:20px;max-width:900px;margin:auto">
    <h1>Patient Panel</h1>
    <div v-if="user">
      <p><strong>Name:</strong> {{ user.name }}</p>
      <p><strong>Email:</strong> {{ user.email }}</p>
      <p><strong>Role:</strong> {{ user.role }}</p>
      <h3>Reports</h3>
      <p>-- Reports list (placeholder) --</p>
    </div>
    <button @click="logout" style="margin-top:12px">Logout</button>
  </div>
</template>

<script setup>
    import axios from '../../bootstrap';
    import { ref, onMounted } from 'vue';
    import { useRouter } from 'vue-router';
    const user = ref(null);
    const router = useRouter();

    onMounted(async () => {
        try {
            const res = await axios.get('/api/user');
            user.value = res.data;
            if (user.value.role !== 'patient') {
                router.push('/');
            }
        } catch {
            router.push('/');
        }
    });

    async function logout() {
        await axios.post('/api/logout');
        router.push('/');
    }
</script>
