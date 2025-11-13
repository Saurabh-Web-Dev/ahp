import { defineStore } from 'pinia'
import axios from '../bootstrap'
import { ref } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)

  // Fetch user only once
  const fetchUser = async () => {
    if (user.value || loading.value) return
    try {
      loading.value = true
      const res = await axios.get('/api/user')
      user.value = res.data
    } catch (e) {
      user.value = null
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    await axios.post('/api/logout')
    user.value = null
  }

  return { user, loading, fetchUser, logout }
})
