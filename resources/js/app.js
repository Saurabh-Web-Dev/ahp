import { createApp } from 'vue';
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router';
import Login from './pages/Login.vue';
import DoctorDashboard from './pages/doctor/DoctorDashboard.vue';
import ManagerDashboard from './pages/manager/ManagerDashboard.vue';
import PatientList from './pages/manager/PatientList.vue';
import ManagerAppointments from './pages/manager/ManagerAppointments.vue';
import DoctorQueue from './pages/doctor/DoctorQueue.vue';
import Prescription from './pages/doctor/Prescription.vue';
import axios from './bootstrap';
import App from './App.vue';  // ✅ your root component
import 'bootstrap/dist/css/bootstrap.min.css'; // ✅ add this line
import 'bootstrap'; // optional (for JS components like modal, tooltip)


const routes = [
  { path: '/', component: Login },

  ////////////////////////////////////////////////
  ///// Manager Routing //////////////////////////

  { path: '/manager/dashboard', component: ManagerDashboard },
  { path: '/manager/patient/list', component: PatientList },
  { path: '/manager/appointment/list', component: ManagerAppointments },


  //////////////////////////////////////////////////////////////////////
  //////////// Doctor Routing //////////////////////////////////////////
  //////////////////////////////////////////////////////////////////////

  { path: '/doctor/dashboard', component: DoctorDashboard },
  { path: '/doctor/appointment/list', component: DoctorQueue },
  { path: '/doctor/prescription/:id', component: Prescription }
];

const router = createRouter({
  history: createWebHistory('/app'),
  routes,
});

// ✅ use App.vue as root
    const app = createApp(App);
    app.use(createPinia())
    app.use(router);
    app.mount('#app');
