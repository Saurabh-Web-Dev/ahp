import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import PatientLogin from './pages/patient/PatientLogin.vue';
import PatientDashboard from './pages/patient/PatientDashboard.vue';
import axios from './bootstrap';
import App from './App.vue';  // ✅ your root component
import 'bootstrap/dist/css/bootstrap.min.css'; // ✅ add this line
import 'bootstrap'; // optional (for JS components like modal, tooltip)


const routes = [
  { path: '/', component: PatientLogin },
  { path: '/login', component: PatientLogin },
  { path: '/dashboard', component: PatientDashboard }
];

const router = createRouter({
  history: createWebHistory('/patient'),
  routes,
});

createApp(App)
  .use(router)
  .mount('#patientApp');

