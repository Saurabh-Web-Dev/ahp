<template>
  <div class="d-flex flex-column flex-md-row">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Main Content -->
    <div class="container-fluid p-4 main-content">
      <!-- Add Appointment Form -->
      <div class="card p-4 mb-4 shadow-sm border-0">
        <h4 class="mb-3 text-primary fw-bold d-flex align-items-center gap-2">
          <i class="fa fa-calendar-plus-o"></i> Add New Appointment
        </h4>

        <form @submit.prevent="createAppointment" novalidate>
          <div class="row g-3">
            <!-- Patient Search -->
            <div class="col-md-5 position-relative">
              <label class="form-label fw-semibold">
                <i class="fa fa-user text-secondary me-1"></i> Search Patient
              </label>
              <div class="position-relative">
                <input
                  type="text"
                  class="form-control ps-5"
                  placeholder="Type patient name or unique ID..."
                  v-model="search"
                  @input="searchPatients"
                  :class="{ 'is-invalid': errors.patient }"
                />
                <i
                  class="fa fa-search position-absolute text-muted"
                  style="left: 12px; top: 50%; transform: translateY(-50%)"
                ></i>

                <!-- Search Results -->
                <ul
                  v-if="searchResults.length"
                  class="list-group position-absolute w-100 mt-1 shadow-sm rounded-3"
                  style="z-index: 10; max-height: 220px; overflow-y: auto;"
                >
                  <li
                    v-for="p in searchResults"
                    :key="p.id"
                    @click="selectPatient(p)"
                    class="list-group-item list-group-item-action"
                    style="cursor: pointer;"
                  >
                    <strong>{{ p.name }}</strong>
                    <small class="text-muted"> ({{ p.gender }})</small>
                  </li>
                </ul>
              </div>
              <div class="invalid-feedback">{{ errors.patient }}</div>
            </div>

            <!-- Doctor Select -->
            <div class="col-md-5">
              <label class="form-label fw-semibold">
                <i class="fa fa-stethoscope text-secondary me-1"></i> Select Doctor
              </label>
              <select
                v-model="form.doctor_id"
                class="form-select"
                :class="{ 'is-invalid': errors.doctor }"
              >
                <option disabled value="">-- Select Doctor --</option>
                <option v-for="d in doctors" :key="d.id" :value="d.id">
                  Dr. {{ d.name }}
                </option>
              </select>
              <div class="invalid-feedback">{{ errors.doctor }}</div>
            </div>

            <!-- Submit Button -->
            <div class="col-md-2 d-flex align-items-end">
              <button
                class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2"
                type="submit"
                :disabled="loading"
              >
                <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                <i v-else class="fa fa-plus-circle"></i>
                <span>{{ loading ? "Adding..." : "Add" }}</span>
              </button>
            </div>
          </div>
        </form>

        <!-- Success Alert -->
        <transition name="fade">
          <div
            v-if="newToken"
            class="alert alert-success mt-4 d-flex align-items-center gap-2"
          >
            <i class="fa fa-check-circle"></i>
            Appointment created successfully! Token No:
            <strong>{{ newToken }}</strong>
          </div>
        </transition>
      </div>

      <!-- Appointment List -->
      <div class="card shadow-sm border-0 position-relative">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h5 class="mb-0 text-primary d-flex align-items-center gap-2">
            <i class="fa fa-list"></i> Appointments
          </h5>
          <span class="badge bg-primary">{{ appointments.length }} Total</span>
        </div>

        <!-- Loader Overlay -->
        <div v-if="tableLoading" class="table-loader-overlay">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2 text-primary fw-semibold">Loading appointments...</p>
        </div>

        <div class="card-body table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th><i class="fa fa-hashtag"></i></th>
                <th><i class="fa fa-calendar"></i> Date</th>
                <th><i class="fa fa-ticket"></i> Token</th>
                <th><i class="fa fa-user"></i> Patient</th>
                <th><i class="fa fa-user-md"></i> Doctor</th>
                <th><i class="fa fa-info-circle"></i> Status</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(a, index) in appointments"
                :key="a.id"
                class="table-row"
              >
                <td>{{ index + 1 }}</td>
                <td>
                  <span
                    :class="{
                      'badge bg-success': formatDate(a.appointment_date) === 'Today',
                      'badge bg-warning text-dark': formatDate(a.appointment_date) === 'Yesterday',
                      'text-muted': !['Today', 'Yesterday'].includes(formatDate(a.appointment_date))
                    }"
                  >
                    {{ formatDate(a.appointment_date) }}
                  </span>
                </td>
                <td><strong>{{ a.appointment_no }}</strong></td>
                <td>
                  <i class="fa fa-user-circle text-primary me-1"></i>
                  {{ a.patient.name }}
                </td>
                <td>
                  <i class="fa fa-user-md text-secondary me-1"></i>
                  Dr. {{ a.doctor?.name || "-" }}
                </td>
                <td>
                    <span
                        class="badge"
                        :class="{
                        'bg-warning text-dark': a.status === 'pending',
                        'bg-success': a.status === 'completed',
                        'bg-secondary': a.status !== 'pending' && a.status !== 'completed'
                        }">
                        <i
                        class="fa"
                        :class="{
                            'fa-clock-o': a.status === 'pending',
                            'fa-check': a.status === 'completed',
                            'fa-minus-circle': a.status !== 'pending' && a.status !== 'completed'
                        }"
                        ></i>
                        {{ a.status }}
                    </span>
                  <!-- View Prescription Button -->
                    <button v-if="a.status === 'completed'" class="btn btn-sm btn-outline-primary ms-2" @click="openPrescription(a.id)">
                        <i class="fa fa-file-text"></i> View
                    </button>
                </td>
              </tr>

              <!-- No Data -->
              <tr v-if="!appointments.length && !tableLoading">
                <td colspan="7" class="text-center text-muted py-4">
                  <i class="fa fa-info-circle me-2"></i>
                  No appointments found
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!-- Prescription Modal -->
    <div v-if="showModal" class="modal-backdrop-custom" @click.self="showModal = false">
        <div class="modal-box">
            <h4 class="text-primary">
            <i class="fa fa-file-text"></i> Prescription Details
            </h4>

            <div v-if="prescription">
            <p><strong>Diagnosis:</strong> {{ prescription.diagnosis }}</p>
            <p><strong>Notes:</strong> {{ prescription.notes }}</p>

            <h5 class="mt-3">Medicines</h5>
            <ul class="list-group">
                <li v-for="m in prescription.medicines" :key="m.id" class="list-group-item">
                <strong>{{ m.medicine_name }}</strong>
                — <i>{{ m.dosage }}</i> INTERVAL {{ m.duration }}
                <br>
                <small class="text-muted">{{ m.instructions }}</small>
                </li>
            </ul>
            </div>

            <div v-else>
            <p class="text-muted">No prescription found.</p>
            </div>

            <button class="btn btn-danger w-100 mt-3" @click="showModal = false">
            Close
            </button>
        </div>
    </div>


</template>


<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import Sidebar from "./common/Sidebar.vue";

const search = ref("");
const searchResults = ref([]);
const doctors = ref([]);
const appointments = ref([]);
const form = ref({ patient_id: "", doctor_id: "" });
const newToken = ref("");
const loading = ref(false);
const errors = ref({});
const tableLoading = ref(true);
const showModal = ref(false);
const prescription = ref(null);




// Load data
onMounted(() => {
  getDoctors();
  getAppointments();
});

const openPrescription = async (appointmentId) => {
    // console.log(appointmentId);
    // return;
  try {
    const res = await axios.get(`/api/appointments/${appointmentId}`);
    prescription.value = res.data.prescription || null;
    showModal.value = true;
  } catch (error) {
    console.error(error);
    alert("Unable to load prescription!");
  }
};


// Search patients
const searchPatients = async () => {
  if (search.value.length < 2) {
    searchResults.value = [];
    return;
  }
  const res = await axios.get(`/api/patients/search?q=${search.value}`);
  searchResults.value = res.data;
};

const selectPatient = (p) => {
  form.value.patient_id = p.id;
  search.value = p.name;
  searchResults.value = [];
};

// Get doctors
const getDoctors = async () => {
  const res = await axios.get("/api/users?role=doctor");
  doctors.value = res.data;
};

const getAppointments = async () => {
  tableLoading.value = true;
  try {
    const res = await axios.get("/api/appointments");
    appointments.value = res.data.data || res.data;
  } catch (err) {
    console.error(err);
  } finally {
    setTimeout(() => (tableLoading.value = false), 400); // smooth fade-out
  }
};

// Validation
const validateForm = () => {
  errors.value = {};
  if (!form.value.patient_id) errors.value.patient = "Please select a patient.";
  if (!form.value.doctor_id) errors.value.doctor = "Please select a doctor.";
  return Object.keys(errors.value).length === 0;
};

// Create appointment
const createAppointment = async () => {
  if (!validateForm()) return;

  loading.value = true;
  try {
    const res = await axios.post("/api/appointments", form.value);
    newToken.value = res.data.appointment.appointment_no;
    form.value = { patient_id: "", doctor_id: "" };
    search.value = "";
    await getAppointments();
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

// Date formatter
function formatDate(dateStr) {
  if (!dateStr) return "-";
  const date = new Date(dateStr);
  const today = new Date();
  const yesterday = new Date();
  yesterday.setDate(today.getDate() - 1);

  const isToday = date.toDateString() === today.toDateString();
  const isYesterday = date.toDateString() === yesterday.toDateString();

  if (isToday) return "Today";
  if (isYesterday) return "Yesterday";

  return date.toLocaleDateString("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
}
</script>

<style scoped>
.main-content {
  margin-left: 260px;
  background-color: #f8f9fb;
  min-height: 100vh;
  transition: margin-left 0.3s ease;
}

.card {
  border-radius: 14px;
}

.form-control:focus,
.form-select:focus {
  box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
  border-color: #0d6efd;
}

.table th {
  font-weight: 600;
}

.badge {
  font-size: 0.85rem;
  padding: 0.5em 0.75em;
  border-radius: 6px;
}
/* Loader Overlay */
.table-loader-overlay {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.7);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 10;
  backdrop-filter: blur(3px);
  border-radius: 0.75rem;
}

/* Table Row Hover Effect */
.table-row:hover {
  background-color: #f8faff;
  transition: background-color 0.2s ease;
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .main-content {
    margin-left: 0 !important;
    padding: 1rem;
  }

  .table thead {
    display: none;
  }

  .table tr {
    display: block;
    margin-bottom: 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
    padding: 0.75rem;
  }

  .table td {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem;
    border: none;
  }

  .table td::before {
    content: attr(data-label);
    font-weight: 600;
    color: #6c757d;
  }
}

.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-box {
  background: white;
  padding: 20px;
  width: 420px;
  border-radius: 10px;
  box-shadow: 0 0 20px rgba(0,0,0,0.2);
}


</style>
