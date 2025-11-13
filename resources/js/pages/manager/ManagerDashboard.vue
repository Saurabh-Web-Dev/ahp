<template>
  <div class="d-flex">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Main Content -->
    <div class="container-fluid p-4 main-content">
      <!-- Header -->
      <div
        class="card shadow-sm p-3 mb-3 d-flex flex-md-row justify-content-between align-items-center"
      >
        <div>
          <h5 class="mb-1">Welcome, {{ user?.name || '' }}</h5>
          <p class="text-muted mb-0">Role: {{ user?.role || '' }}</p>
        </div>
        <button
          @click="logout"
          class="btn btn-outline-danger btn-sm mt-3 mt-md-0"
          :disabled="logoutLoading"
        >
          <span v-if="logoutLoading" class="spinner-border spinner-border-sm me-2"></span>
          <i class="fa fa-sign-out"></i> Logout
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="row g-3 mb-4">
        <div class="col-md-4" v-for="card in statsCards" :key="card.title">
          <div
            class="card shadow-sm border-0 p-4 text-white"
            :style="{ background: card.bg }"
          >
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-1 text-light">{{ card.title }}</h6>
                <h3 class="fw-bold mb-0">{{ card.value }}</h3>
              </div>
              <i :class="[ card.icon, 'fa-2x opacity-75']"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Chart Section -->
      <div class="card shadow-sm p-4 mb-4">
        <h5 class="mb-3 text-primary">📊 Appointments - Last 7 Days</h5>
        <Line v-if="chartData.datasets.length" :data="chartData" :options="chartOptions" />
        <div v-else class="text-center text-muted py-5">Loading chart...</div>
      </div>

      <!-- Quick Action -->
      <div class="card shadow-sm p-4 text-center">
        <h5 class="text-primary mb-3">👥 Manage Patients</h5>
        <router-link to="/manager/patient/list" class="btn btn-success px-4">
          View Patients
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import Sidebar from './common/Sidebar.vue'
import axios from '../../bootstrap'

// Chart.js imports
import {
  Chart as ChartJS,
  LineElement,
  CategoryScale,
  LinearScale,
  PointElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(LineElement, CategoryScale, LinearScale, PointElement, Title, Tooltip, Legend)

const router = useRouter()
const user = ref(null)
const logoutLoading = ref(false)
const statsCards = ref([])
const chartData = ref({ labels: [], datasets: [] })

const chartOptions = {
  responsive: true,
  plugins: { legend: { position: 'top' } },
  scales: { y: { beginAtZero: true } }
}

const fetchDashboardData = async () => {
  try {
    const res = await axios.get('/api/manager/dashboard-stats')

    statsCards.value = [
      { title: "Today's Pending", value: res.data.today_pending || 0, bg: 'linear-gradient(135deg, #f6ad55, #ed8936)', icon: 'fa fa-clock-o' },
      { title: "Today's Completed", value: res.data.today_completed || 0, bg: 'linear-gradient(135deg, #48bb78, #38a169)', icon: 'fa fa-check-circle' },
      { title: 'Total Appointments', value: res.data.total || 0, bg: 'linear-gradient(135deg, #4299e1, #3182ce)', icon: 'fa fa-calendar' }
    ]

    const last7 = res.data.last7days || []
    await nextTick()
    chartData.value = {
      labels: last7.map(d => d.date),
      datasets: [
        { label: 'Pending', data: last7.map(d => d.pending), borderColor: '#f6ad55', backgroundColor: 'rgba(246, 173, 85, 0.2)', tension: 0.3, fill: true },
        { label: 'Completed', data: last7.map(d => d.completed), borderColor: '#48bb78', backgroundColor: 'rgba(72, 187, 120, 0.2)', tension: 0.3, fill: true }
      ]
    }
  } catch (err) {
    console.error('Dashboard data fetch error:', err)
  }
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/user')
    user.value = res.data
    if (user.value.role !== 'manager') router.push('/')
    else await fetchDashboardData()
  } catch {
    router.push('/')
  }
})

const logout = async () => {
  logoutLoading.value = true
  await axios.post('/api/logout')
  router.push('/')
  logoutLoading.value = false
}
</script>

<style scoped>
.main-content {
  margin-left: 260px;
  transition: margin-left 0.3s ease;
  min-height: 100vh;
  background-color: #f8f9fb;
}
@media (max-width: 992px) {
  .main-content {
    margin-left: 0;
  }
}
.card {
  border-radius: 12px;
}
.card canvas {
  min-height: 320px;
}
</style>
