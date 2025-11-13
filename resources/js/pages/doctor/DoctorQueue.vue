<template>
  <div class="d-flex">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Main Content -->
    <div class="container-fluid p-4 main-content">
      <!-- Header -->
      <div class="card shadow-sm p-3 mb-3 d-flex flex-md-row justify-content-between align-items-center">
        <div>
          <h5 class="mb-1">Welcome, {{ auth.user?.name || '' }}</h5>
          <p class="text-muted mb-0">Role: {{ auth.user?.role || '' }}</p>
        </div>
        <button
          @click="logout"
          class="btn btn-outline-danger btn-sm mt-3 mt-md-0"
          :disabled="logoutLoading"
        >
          <span
            v-if="logoutLoading"
            class="spinner-border spinner-border-sm me-2"
          ></span>
          <i class="fa fa-sign-out"></i> Logout
        </button>
      </div>

      <!-- Queue Table -->
      <div class="card shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0">🩺 Today's Appointments</h5>
        </div>

        <div class="card-body table-responsive">
          <table class="table table-striped align-middle">
            <thead class="table-light">
              <tr>
                <th>Token</th>
                <th>Patient</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>

            <tbody>
              <!-- Loader -->
              <tr v-if="loading">
                <td colspan="4" class="text-center py-4">
                  <div class="spinner-border text-primary" role="status"></div>
                  <p class="text-muted mt-2 mb-0">Loading appointments...</p>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-else-if="!appointments.length">
                <td colspan="4" class="text-center text-muted py-4">
                  No appointments found for today
                </td>
              </tr>

              <!-- Appointments -->
              <tr v-else v-for="a in appointments" :key="a.id">
                <td><strong>{{ a.appointment_no }}</strong></td>
                <td>{{ a.patient?.name || 'Unknown' }}</td>
                <td>
                  <span
                    :class="[
                      'badge',
                      a.status === 'completed' ? 'bg-success' : 'bg-warning',
                    ]"
                  >
                    {{ a.status }}
                  </span>
                </td>
                <td>
                    <button
                        class="btn btn-sm btn-outline-success"
                        v-if="a.status === 'pending'"
                        @click="markCompleted(a.id)"
                        :disabled="actionLoading === a.id"
                    >
                        <span
                        v-if="actionLoading === a.id"
                        class="spinner-border spinner-border-sm me-2"
                        ></span>
                        Complete
                    </button>
                    <router-link :to="`/doctor/prescription/${a.id}`" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-file-medical"></i> Prescription
                    </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../store/useAuthStore'
import axios from '../../bootstrap'
import Sidebar from './common/sidebar.vue'

const router = useRouter()
const auth = useAuthStore()

// State
const appointments = ref([])
const loading = ref(false)
const logoutLoading = ref(false)
const actionLoading = ref(null)

// Fetch queue data
const getQueue = async () => {
  try {
    loading.value = true
    const res = await axios.get('/api/appointments/today')

    // Sort: pending first, completed last
    appointments.value = res.data.sort((a, b) => {
      if (a.status === 'pending' && b.status === 'completed') return -1
      if (a.status === 'completed' && b.status === 'pending') return 1
      return a.appointment_no - b.appointment_no
    })
  } catch (error) {
    console.error('Error fetching queue:', error)
  } finally {
    loading.value = false
  }
}

// Mark appointment as completed
const markCompleted = async (id) => {
  try {
    actionLoading.value = id
    await axios.put(`/api/appointments/${id}/status`, { status: 'completed' })

    // Instead of full reload, just update local list
    const index = appointments.value.findIndex((a) => a.id === id)
    if (index !== -1) {
      appointments.value[index].status = 'completed'
    }

    // Re-sort to move completed to bottom
    appointments.value.sort((a, b) => {
      if (a.status === 'pending' && b.status === 'completed') return -1
      if (a.status === 'completed' && b.status === 'pending') return 1
      return a.appointment_no - b.appointment_no
    })
  } catch (error) {
    console.error('Error updating status:', error)
  } finally {
    actionLoading.value = null
  }
}

// Load user and queue
onMounted(async () => {
  await auth.fetchUser()
  if (!auth.user || auth.user.role !== 'doctor') {
    router.push('/')
    return
  }
  await getQueue()
})

// Logout
const logout = async () => {
  try {
    logoutLoading.value = true
    await auth.logout()
    router.push('/')
  } finally {
    logoutLoading.value = false
  }
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
  border-radius: 7px;
}

.badge {
  font-size: 0.9rem;
}
</style>
