import { defineStore } from 'pinia'
import axios from '../bootstrap'
import { ref } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('user')) || null)
  const loading = ref(true)

  const fetchUser = async () => {
    try {
      const res = await axios.get('/api/user')
      user.value = res.data
      localStorage.setItem('user', JSON.stringify(res.data))
    } catch (e) {
      user.value = null
      localStorage.removeItem('user')
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    await axios.post('/api/logout')
    user.value = null
    localStorage.removeItem('user')
  }

  return { user, loading, fetchUser, logout }
})
