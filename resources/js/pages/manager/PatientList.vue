<template>
  <div class="dashboard-wrapper">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Main Content -->
    <div class="dashboard-content">
      <div class="container-fluid py-4">
        <!-- Welcome Section -->
        <div v-if="user" class="mb-4">
          <div class="card shadow-sm border-0 p-3 d-flex flex-md-row justify-content-between align-items-center">
            <div>
              <h5 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa fa-user-circle text-primary"></i> Welcome, {{ user.name }}
              </h5>
              <p class="text-muted mb-0">
                <i class="fa fa-id-badge me-1"></i> Role: {{ user.role }}
              </p>
            </div>
            <button @click="logout" class="btn btn-outline-danger btn-sm mt-3 mt-md-0">
              <i class="fa fa-sign-out"></i> Logout
            </button>
          </div>

          <!-- Patient Header -->
          <div class="patient-header card shadow-sm border-0 p-3 mb-4 mt-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
              <h4 class="fw-bold mb-0 text-primary d-flex align-items-center gap-2">
                <i class="fa fa-users text-secondary"></i> Patient List
              </h4>

              <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                <!-- Search -->
                <div class="position-relative flex-grow-1">
                  <i class="fa fa-search position-absolute text-muted"
                     style="left: 12px; top: 50%; transform: translateY(-50%)"></i>
                  <input
                    type="text"
                    v-model="searchQuery"
                    @input="debounceSearch"
                    class="form-control ps-5 shadow-sm"
                    placeholder="Search by name or ID..."
                  />
                </div>

                <!-- Add -->
                <button class="btn btn-success shadow-sm d-flex align-items-center gap-2" @click="openModal">
                  <i class="fa fa-user-plus"></i>
                  <span>Add Patient</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Patient Table -->
          <div class="card shadow-sm border-0">
            <div class="card-body table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th><i class="fa fa-user"></i> Name</th>
                    <th><i class="fa fa-birthday-cake"></i> Age</th>
                    <th><i class="fa fa-venus-mars"></i> Gender</th>
                    <th><i class="fa fa-users"></i> Parent</th>
                    <th><i class="fa fa-map-marker"></i> Location</th>
                    <th><i class="fa fa-calendar"></i> Added</th>
                    <th><i class="fa fa-cogs"></i> Action</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Loader -->
                  <tr v-if="loading">
                    <td colspan="8" class="text-center py-4">
                      <div class="spinner-border text-primary" role="status"></div>
                      <p class="text-muted mt-2 mb-0">Loading patients...</p>
                    </td>
                  </tr>

                  <!-- No Data -->
                  <tr v-else-if="!patients.length">
                    <td colspan="8" class="text-center text-muted">
                      <i class="fa fa-info-circle me-2"></i>No patients found
                    </td>
                  </tr>

                  <!-- Data Rows -->
                  <tr v-else v-for="(p, index) in patients" :key="p.id">
                    <td>{{ p.patient_id }}</td>
                    <td>{{ p.name }}</td>
                    <td>{{ p.age }}</td>
                    <td>{{ p.gender }}</td>
                    <td>{{ p.parent }}</td>
                    <td>{{ p.location }}</td>
                    <td>{{ formatDate(p.created_at) }}</td>
                    <td>
                        <button class="btn btn-sm btn-warning me-2" @click="editPatient(p)">
                            <i class="fa fa-pencil-square"></i> Edit
                        </button>
                        <button
                        class="btn btn-sm btn-info me-2"
                        @click="openDetails(p)"
                        :disabled="detailsLoader === p.id"
                        >
                            <span v-if="detailsLoader === p.id" class="spinner-border spinner-border-sm"></span>
                            <span v-else>
                                <i class="fa fa-eye"></i> Details
                            </span>
                        </button>

                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Pagination -->
              <nav v-if="pagination.last_page > 1" class="mt-3">
                <ul class="pagination justify-content-center">
                  <li
                    class="page-item"
                    :class="{ disabled: pagination.current_page === 1 || loading }"
                  >
                    <button
                      class="page-link"
                      @click="fetchPatients(pagination.current_page - 1)"
                      :disabled="pagination.current_page === 1 || loading"
                    >
                      Prev
                    </button>
                  </li>

                  <li
                    v-for="page in pagination.last_page"
                    :key="page"
                    class="page-item"
                    :class="{ active: page === pagination.current_page }"
                  >
                    <button
                      class="page-link"
                      @click="fetchPatients(page)"
                      :disabled="loading"
                    >
                      {{ page }}
                    </button>
                  </li>

                  <li
                    class="page-item"
                    :class="{ disabled: pagination.current_page === pagination.last_page || loading }"
                  >
                    <button
                      class="page-link"
                      @click="fetchPatients(pagination.current_page + 1)"
                      :disabled="pagination.current_page === pagination.last_page || loading"
                    >
                      Next
                    </button>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Patient Modal -->
    <!-- 🌟 Beautiful Add/Edit Patient Modal -->
    <div
    class="modal fade"
    id="patientModal"
    tabindex="-1"
    ref="modalRef"
    data-bs-backdrop="static"
    >
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 animate__animated animate__fadeInDown">
        <!-- Header -->
        <div class="modal-header bg-primary text-white rounded-top-4 py-3">
            <h5 class="modal-title d-flex align-items-center gap-2">
            <i :class="isEditing ? 'fa fa-edit' : 'fa fa-user-plus'"></i>
            {{ isEditing ? "Edit Patient" : "Add New Patient" }}
            </h5>
            <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"
            aria-label="Close"
            ></button>
        </div>

        <!-- Body -->
        <div class="modal-body p-4 bg-light rounded-bottom-4">
            <form @submit.prevent="validateAndSave">
                <div class="row g-3">
                    <!-- Full Name -->
                    <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="fa fa-user text-primary me-1"></i> Full Name
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': errors.name }"
                        placeholder="Enter full name"
                    />
                    <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
                    </div>

                    <!-- DOB -->
                    <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="fa fa-calendar text-primary me-1"></i> Date of Birth
                    </label>
                    <input
                        v-model="form.dob"
                        @change="calculateAge"
                        type="date"
                        class="form-control"
                        :class="{ 'is-invalid': errors.dob }"
                    />
                    <div v-if="errors.dob" class="invalid-feedback">{{ errors.dob }}</div>
                    </div>

                    <!-- Age -->
                    <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fa fa-hourglass-half text-primary me-1"></i> Age
                    </label>
                    <input
                        v-model="form.age"
                        type="number"
                        class="form-control bg-light"
                        readonly
                    />
                    </div>

                    <!-- Gender -->
                    <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fa fa-venus-mars text-primary me-1"></i> Gender
                    </label>
                    <select
                        v-model="form.gender"
                        class="form-select"
                        :class="{ 'is-invalid': errors.gender }"
                    >
                        <option value="">Select</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                    </select>
                    <div v-if="errors.gender" class="invalid-feedback">{{ errors.gender }}</div>
                    </div>

                    <!-- Parent -->
                    <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fa fa-users text-primary me-1"></i> Parent Name
                    </label>
                    <input
                        v-model="form.parent"
                        type="text"
                        class="form-control"
                        placeholder="Enter parent name"
                        :class="{ 'is-invalid': errors.parent }"
                    />
                    <div v-if="errors.parent" class="invalid-feedback">{{ errors.parent }}</div>
                    </div>

                    <!-- Location -->
                    <div class="col-12">
                    <label class="form-label fw-semibold">
                        <i class="fa fa-map-marker text-primary me-1"></i> Home Location
                    </label>
                    <input
                        v-model="form.location"
                        type="text"
                        class="form-control"
                        placeholder="Enter home location"
                        :class="{ 'is-invalid': errors.location }"
                    />
                    <div v-if="errors.location" class="invalid-feedback">{{ errors.location }}</div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="text-end mt-4">
                    <button
                    type="button"
                    class="btn btn-light border me-2"
                    data-bs-dismiss="modal"
                    >
                    <i class="fa fa-times-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success px-4" :disabled="savePatientLoader">
                        <span v-if="savePatientLoader" class="spinner-border spinner-border-sm"></span>
                        <i class="fa" :class="isEditing ? 'fa-save' : 'fa-check-circle'"></i>
                        {{ isEditing ? "Update Patient" : "Save Patient" }}
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>
    </div>

  </div>

  <!-- Patient Detials Model -->
   <!-- 🧍 Patient Details Modal -->
<div class="modal fade" id="patientDetailsModal" tabindex="-1" ref="detailsModalRef">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-3 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="bi bi-person-vcard me-2"></i> Patient Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" v-if="selectedPatient">
        <div class="mb-3">
          <h5 class="fw-bold">{{ selectedPatient.name }}</h5>
          <p class="mb-0 text-muted">Gender: {{ selectedPatient.gender }} | Age: {{ selectedPatient.age }}</p>
          <p class="mb-0 text-muted">Parent: {{ selectedPatient.parent }}</p>
          <p class="mb-0 text-muted">Location: {{ selectedPatient.location }}</p>
        </div>

        <hr>

        <h6 class="fw-bold mb-2"><i class="bi bi-calendar-event me-1"></i> Appointments</h6>

        <div v-if="appointments.length">
          <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Date</th>
                <th>Doctor</th>
                <th>Status</th>
                <th>Prescription</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(a, i) in appointments" :key="a.id">
                <td>{{ i + 1 }}</td>
                <td>{{ formatDate(a.appointment_date) }}</td>
                <td>{{ a.doctor?.name || '-' }}</td>
                <td>
                  <span :class="statusClass(a.status)">{{ a.status }}</span>
                </td>
                <td>
                  <button
                    v-if="user.role === 'doctor'"
                    class="btn btn-sm btn-outline-success"
                    @click="openPrescription(a)"
                  >
                    <i class="bi bi-capsule"></i> Add Prescription
                  </button>
                  <span v-else class="text-muted">View Only</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <p v-else class="text-muted fst-italic">No appointments found.</p>
      </div>
    </div>
  </div>
</div>

</template>

<script setup>
import axios from "../../bootstrap";
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import * as bootstrap from "bootstrap";
import Sidebar from "./common/Sidebar.vue";
import { inject } from 'vue'

const router = useRouter();
const user = ref(null);
const modalRef = ref(null);
let modalInstance = null;

const patients = ref([]);
const pagination = ref({ current_page: 1, last_page: 1 });
const loading = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const searchQuery = ref("");
let searchTimer = null;
const detailsLoader = ref(null);
const savePatientLoader = ref(false);
const toast = inject("toast");
/////////////////
const detailsModalRef = ref(null);
const selectedPatient = ref(null);
const appointments = ref([]);
let detailsModal = null;

async function fetchPatients(page = 1) {
  loading.value = true;
  try {
    const params = searchQuery.value ? { q: searchQuery.value, page } : { page };
    const res = await axios.get(`/api/patients`, { params });
    patients.value = res.data.data || res.data;
    pagination.value = {
      current_page: res.data.current_page || 1,
      last_page: res.data.last_page || 1,
    };
  } catch (error) {
    console.error("Failed to fetch patients", error);
  } finally {
    loading.value = false;
  }
}

// Debounce for search
function debounceSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => fetchPatients(1), 400);
}



const form = ref({
  name: "",
  dob: "",
  age: "",
  gender: "",
  parent: "",
  location: "",
});

const errors = ref({
  name: "",
  dob: "",
  gender: "",
  parent: "",
  location: "",
});
onMounted(async () => {
  try {
    const res = await axios.get("/api/user");
    user.value = res.data;
    if (user.value.role !== "manager") {
      router.push("/");
      return;
    }
    await fetchPatients();
    modalInstance = new bootstrap.Modal(modalRef.value);
    detailsModal = new bootstrap.Modal(detailsModalRef.value);
  } catch {
    router.push("/");
  }
});

async function openDetails(patient) {
  detailsLoader.value = patient.id;
  selectedPatient.value = patient;

  try {
      const res = await axios.get(`/api/patients/${patient.id}/appointments`);
      appointments.value = res.data.appointments;
      detailsModal.show();
  } catch (err) {
      console.error("Error fetching patient details", err);
  } finally {
      detailsLoader.value = null;
  }
}


function statusClass(status) {
  return {
    "badge bg-warning text-dark": status === "pending",
    "badge bg-success": status === "completed",
    "badge bg-danger": status === "cancelled",
  };
}

function openPrescription(appointment) {
  // You can open another modal for prescription entry
  console.log("Open prescription for", appointment);
}

function openModal() {
  isEditing.value = false;
  editingId.value = null;
  form.value = { name: "", dob: "", age: "", gender: "", parent: "", location: "" };
  modalInstance.show();
}

function calculateAge() {
  if (form.value.dob) {
    const dob = new Date(form.value.dob);
    const diff = Date.now() - dob.getTime();
    const ageDt = new Date(diff);
    form.value.age = Math.abs(ageDt.getUTCFullYear() - 1970);
  }
}


function validateForm() {
  errors.value = {
    name: form.value.name ? "" : "Full name is required",
    dob: form.value.dob ? "" : "Date of birth is required",
    gender: form.value.gender ? "" : "Please select gender",
    parent: form.value.parent ? "" : "Parent name is required",
    location: form.value.location ? "" : "Location is required",
  };

  return !Object.values(errors.value).some((msg) => msg);
}

async function validateAndSave() {
  if (!validateForm()) {
    return;
  }
  await savePatient();
}

async function savePatient() {
    savePatientLoader.value = true;
    try {
        await axios.get("/sanctum/csrf-cookie");
        if (isEditing.value) {
            const res = await axios.put(`/api/patients/${editingId.value}`, form.value);
            const index = patients.value.findIndex((p) => p.id === editingId.value);
            if (index !== -1) patients.value[index] = res.data.patient;
        } else {
            const res = await axios.post("/api/patients", form.value);
            patients.value.unshift(res.data.patient);
        }
        modalInstance.hide();
        toast.value.showToast("success", isEditing.value
            ? "Patient updated successfully!"
            : "Patient saved successfully!"
        );
    } catch (err) {
        console.error(err);
        alert(err.response?.data?.message || "Error saving patient");
    }finally{
        savePatientLoader.value = false;
    }
}

function editPatient(patient) {
  isEditing.value = true;
  editingId.value = patient.id;
  form.value = { ...patient };
  modalInstance.show();
}

function formatDate(dateString) {
  if (!dateString) return "-";
  const date = new Date(dateString);
  const today = new Date();
  const yesterday = new Date();
  yesterday.setDate(today.getDate() - 1);

  if (date.toDateString() === today.toDateString()) return "Today";
  if (date.toDateString() === yesterday.toDateString()) return "Yesterday";
  return date.toLocaleDateString();
}

async function logout() {
  await axios.post("/api/logout");
  router.push("/");
}
</script>

<style scoped>
.dashboard-wrapper {
  display: flex;
  min-height: 100vh;
  background: #f8f9fb;
}
.dashboard-content {
  flex: 1;
  margin-left: 260px;
  transition: margin-left 0.3s ease;
}
@media (max-width: 992px) {
  .dashboard-content {
    margin-left: 0;
  }
}
.card {
  border-radius: 12px;
  transition: all 0.3s ease;
}
.card:hover {
  transform: translateY(-2px);
}
.table th i {
  margin-right: 4px;
}
.page-item.active .page-link {
  background-color: #0d6efd;
  border-color: #0d6efd;
  color: #fff;
}
.page-link {
  cursor: pointer;
}
.patient-header input:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
}
.btn-success, .btn-warning {
  border-radius: 8px;
  font-weight: 500;
}
@media (max-width: 768px) {
  .patient-header h4 {
    text-align: center;
  }
}
</style>
