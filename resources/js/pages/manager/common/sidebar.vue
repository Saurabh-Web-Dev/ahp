<template>
  <div>
    <!-- Overlay for mobile -->
    <div v-if="isOpen" class="sidebar-overlay" @click="toggleSidebar"></div>

    <!-- Sidebar -->
    <aside class="sidebar bg-dark text-white shadow-lg" :class="{ open: isOpen }">
      <!-- Header -->
      <div class="sidebar-header text-center py-4 border-bottom border-secondary">
        <img :src="logo" alt="App Logo" class="sidebar-logo mb-2" />
        <h5 class="mb-0 fw-bold">HealthCare+</h5>
      </div>

      <!-- Menu -->
      <ul class="sidebar-menu list-unstyled mt-3">
        <li v-for="item in menu" :key="item.label">
          <router-link
            :to="item.href"
            class="sidebar-link d-flex align-items-center px-4 py-2 text-white text-decoration-none"
            active-class="active"
            exact-active-class="exact-active"
          >
            <i :class="item.icon + ' me-3'"></i>
            <span>{{ item.label }}</span>
          </router-link>
        </li>
      </ul>

      <!-- Footer / Logout -->
      <div class="mt-auto border-top border-secondary text-center py-3">
        <button class="btn btn-outline-light btn-sm" @click="logout">
            <span v-if="logoutLoading" class="spinner-border spinner-border-sm me-2"></span>
            <i class="fa fa-sign-out me-1"></i> Logout
        </button>
      </div>
    </aside>

    <!-- Mobile toggle -->
    <button class="btn btn-primary sidebar-toggle d-lg-none" @click="toggleSidebar">
      <i class="fa fa-bars"></i>
    </button>
  </div>
</template>

<script setup>
import axios from "../../../bootstrap";
import { useRouter } from "vue-router";
import { ref } from "vue";
import logoImage from "../../../../assets/images/logo-icon.png";
const logoutLoading = ref(false);
const router = useRouter();
const isOpen = ref(false);
const logo = logoImage;

const menu = [
  { label: "Dashboard", icon: "fa fa-home", href: "/manager/dashboard" },
  { label: "Patients", icon: "fa fa-users", href: "/manager/patient/list" },
  { label: "Appointment", icon: "fa fa-calendar", href: "/manager/appointment/list"}
];

function toggleSidebar() {
  isOpen.value = !isOpen.value;
}

async function logout() {
    logoutLoading.value = true;
    await axios.post("/api/logout");
    router.push("/");
}
</script>

<style scoped>
/* Sidebar base */
.sidebar {
  position: fixed;
  top: 0;
  left: -260px;
  width: 260px;
  height: 100vh;
  background: linear-gradient(180deg, #007bff, #0056b3);
  color: #fff;
  display: flex;
  flex-direction: column;
  transition: left 0.3s ease;
  z-index: 1050;
  border-right: 2px solid rgba(255, 255, 255, 0.1);
}
.sidebar.open {
  left: 0;
}

/* Header */
.sidebar-header {
  background: rgba(0, 0, 0, 0.1);
}
.sidebar-logo {
  width: 60px;
  height: 60px;
  border-radius: 50%;
}

/* Links */
.sidebar-link {
  color: #eaeaea;
  text-decoration: none;
  font-weight: 500;
  border-left: 4px solid transparent;
  transition: all 0.2s ease;
}

/* Inactive state */
.sidebar-link:not(.active):hover {
  background: rgba(255, 255, 255, 0.1);
  border-left-color: #ffc107;
  color: #fff;
}

/* Active link */
.sidebar-link.active,
.sidebar-link.exact-active {
  background: rgba(255, 255, 255, 0.2);
  border-left-color: #ffc107;
  color: #fff;
  font-weight: 600;
}

/* Logout section */
.sidebar button {
  width: 80%;
}

/* Overlay (mobile) */
.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 1040;
}

/* Mobile toggle */
.sidebar-toggle {
  position: fixed;
  top: 15px;
  left: 15px;
  z-index: 1100;
  border-radius: 50%;
  width: 44px;
  height: 44px;
}

/* Desktop */
@media (min-width: 992px) {
  .sidebar {
    left: 0;
  }
  .sidebar-toggle,
  .sidebar-overlay {
    display: none;
  }
}
</style>
