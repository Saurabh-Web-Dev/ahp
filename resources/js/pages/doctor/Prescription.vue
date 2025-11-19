<template>
  <div class="layout">
    <!-- Sidebar -->
    <Sidebar :class="{ active: sidebarOpen }" />

    <!-- Main Content -->
    <div class="main-content p-3">
        <div class="container-fluid">
            <!-- Header -->
            <div class="card shadow-sm p-3 mb-3 d-flex flex-md-row justify-content-between align-items-center">
                <div>
                    <button class="btn btn-outline-primary d-lg-none me-2" @click="toggleSidebar">
                        <i class="fa fa-bars"></i>
                    </button>
                    <h5 class="mb-1 d-inline">
                        Welcome, {{ auth.loading ? 'Loading...' : auth.user?.name }}
                    </h5>
                    <p class="text-muted mb-0">Role: {{ auth.loading ? 'Loading...' : auth.user?.role }}</p>
                </div>

                <button @click="logout" class="btn btn-outline-danger btn-sm mt-3 mt-md-0" :disabled="logoutLoading">
                    <span v-if="logoutLoading" class="spinner-border spinner-border-sm me-2"></span>
                    <i class="fa fa-sign-out"></i> Logout
                </button>
            </div>

            <!-- Prescription Section -->
            <div class="card shadow-sm p-4">
                <h4 class="fw-bold mb-4 text-primary d-flex align-items-center">
                    <i class="fa fa-file-medical me-2"></i>
                    Prescription — {{ patient?.name || 'Loading...' }}
                    <span
                        v-if="isEditing"
                        class="badge bg-warning text-dark ms-3"
                    >
                    Editing Mode
                    </span>
                </h4>

            <!-- Tabs -->
            <ul class="nav nav-tabs nav-fill bg-light rounded-3 mb-3">
                <li class="nav-item">
                <button
                    class="nav-link"
                    :class="{ active: activeTab === 'add' }"
                    @click="activeTab = 'add'"
                >
                    <i class="fa fa-plus-circle me-1"></i> Add Prescription
                </button>
                </li>
                <li class="nav-item">
                <button
                    class="nav-link"
                    :class="{ active: activeTab === 'history' }"
                    @click="loadHistory"
                >
                    <i class="fa fa-history me-1"></i> History
                </button>
                </li>
            </ul>

            <!-- ADD PRESCRIPTION TAB -->
            <div v-if="activeTab === 'add'">
                <div v-if="loadingExisting" class="text-center py-3">
                    <div class="spinner-border text-primary"></div>
                    <p class="text-muted mt-2">Loading existing prescription...</p>
                </div>

                <form @submit.prevent="savePrescription" :class="{ 'opacity-50': formDisabled }">
                    <div class="position-relative">
                        <textarea
                            v-model="form.diagnosis"
                            class="form-control"
                            rows="3"
                            placeholder="e.g., Fever, Cold, Headache"
                            required
                            :disabled="formDisabled"
                        >
                        </textarea>
                        <div v-if="aiLoading" class="ai-loader mt-2">
                            <div class="shimmer"></div>
                        </div>

                        <button type="button" class="btn btn-info border position-absolute end-0 top-0 mt-1 me-1"@click="getAiSuggestion" :disabled="aiLoading">
                            <span v-if="!aiLoading">
                                <i class="fa fa-magic me-1"></i> Get AI Suggestions
                            </span>

                            <span v-else>
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                Thinking...
                            </span>
                        </button>


                    </div>

                <h6 class="fw-bold mt-4 mb-2">Medicines</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                        <th>Medicine Name</th>
                        <th>Dosage</th>
                        <th>Duration</th>
                        <th>Instructions</th>
                        <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(med, index) in form.medicines" :key="index">
                        <td>
                            <input
                            v-model="med.medicine_name"
                            class="form-control form-control-sm"
                            required
                            :disabled="formDisabled"
                            />
                        </td>
                        <td>
                            <input
                            v-model="med.dosage"
                            class="form-control form-control-sm"
                            :disabled="formDisabled"
                            />
                        </td>
                        <td>
                            <input
                            v-model="med.duration"
                            class="form-control form-control-sm"
                            :disabled="formDisabled"
                            />
                        </td>
                        <td>
                            <input
                            v-model="med.instructions"
                            class="form-control form-control-sm"
                            :disabled="formDisabled"
                            />
                        </td>
                        <td>
                            <button
                            type="button"
                            class="btn btn-sm btn-outline-danger"
                            @click="removeMedicine(index)"
                            :disabled="formDisabled"
                            >
                            <i class="fa fa-times"></i>
                            </button>
                        </td>
                        </tr>
                    </tbody>
                    </table>
                </div>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    @click="addMedicine"
                    :disabled="formDisabled"
                >
                    <i class="fa fa-plus me-1"></i> Add Medicine
                </button>

                <div class="mt-3">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea
                    v-model="form.notes"
                    class="form-control"
                    rows="3"
                    placeholder="Enter any additional notes..."
                    :disabled="formDisabled"
                    ></textarea>
                </div>

                <div class="text-end mt-4">
                    <button
                    type="submit"
                    class="btn btn-primary"
                    :disabled="saving"
                    >
                    <span
                        v-if="saving"
                        class="spinner-border spinner-border-sm"
                    ></span>
                    <i v-else class="fa fa-save me-1"></i>
                        {{ isEditing ? "Update Prescription" : "Save Prescription" }}
                    </button>
                </div>
                </form>
            </div>

            <!-- HISTORY TAB -->
            <div v-else>
                <div v-if="loadingHistory" class="text-center py-4">
                <div class="spinner-border text-primary"></div>
                <p class="text-muted mt-2">Loading prescription history...</p>
                </div>

                <div v-else-if="!history.length" class="text-center py-4 text-muted">
                <i class="fa fa-info-circle me-1"></i>No prescriptions found
                </div>

                <div v-else class="accordion" id="prescriptionHistory">
                <div
                    class="accordion-item"
                    v-for="(pres, i) in history"
                    :key="pres.id"
                >
                    <h2 class="accordion-header">
                    <button
                        class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        :data-bs-target="'#collapse' + pres.id"
                    >
                        {{ formatDate(pres.created_at) }} — {{ pres.diagnosis }}
                    </button>
                    </h2>
                    <div
                    :id="'collapse' + pres.id"
                    class="accordion-collapse collapse"
                    data-bs-parent="#prescriptionHistory"
                    >
                    <div class="accordion-body">
                        <ul class="list-group">
                        <li
                            class="list-group-item"
                            v-for="m in pres.medicines"
                            :key="m.id"
                        >
                            <strong>{{ m.medicine_name }}</strong> —
                            {{ m.dosage }}, {{ m.duration }} <br />
                            <small class="text-muted">{{ m.instructions }}</small>
                        </li>
                        </ul>
                        <p class="mt-2 mb-0">
                        <strong>Notes:</strong> {{ pres.notes || '-' }}
                        </p>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import axios from "axios";
import Sidebar from "./common/sidebar.vue";
import { useAuthStore } from '../../store/useAuthStore'
import { inject } from 'vue'
// --- Authentication state ---
const auth = useAuthStore();
auth.fetchUser();
// --- Routing ---
const router = useRouter();
const route = useRoute();
const isEditing = ref(false);
// --- State ---
const activeTab = ref("add");
const appointmentId = ref(route.params.id);
const patient = ref(null);
const saving = ref(false);
const loadingHistory = ref(false);
const logoutLoading = ref(false);
const sidebarOpen = ref(false);
const aiLoading = ref(false);
const aiText = ref(""); // typing effect text
const loadingExisting = ref(false);
const formDisabled = ref(false);

const toast = inject("toast")


// --- Form ---
const form = ref({
  diagnosis: "",
  notes: "",
  medicines: [{ medicine_name: "", dosage: "", duration: "", instructions: "" }],
});

const history = ref([]);

// --- Lifecycle ---
onMounted(async () => {
  await loadPatient();
  await loadExistingPrescription();
});

async function loadExistingPrescription() {
  loadingExisting.value = true;
  formDisabled.value = true; // disable entire form

  try {
    const res = await axios.get(`/api/appointments/${appointmentId.value}/prescription`);

    if (res.data) {
      isEditing.value = true;

      form.value.diagnosis = res.data.diagnosis;
      form.value.notes = res.data.notes;
      form.value.medicines = res.data.medicines.map(m => ({
        medicine_name: m.medicine_name,
        dosage: m.dosage,
        duration: m.duration,
        instructions: m.instructions
      }));
    }
  } catch (err) {
    console.log("No existing prescription");
  } finally {
    loadingExisting.value = false;
    formDisabled.value = false; // re-enable when done
  }
}





// --- API Calls ---
async function loadPatient() {
  try {
    const res = await axios.get(`/api/appointments/${appointmentId.value}`);
    patient.value = res.data.patient;
  } catch (err) {
    console.error("Failed to load patient", err);
  }
}

async function savePrescription() {
  saving.value = true;
  try {
    await axios.post(`/api/appointments/${appointmentId.value}/prescription`, form.value);

    toast.value.showToast("success", isEditing.value
      ? "Prescription updated successfully!"
      : "Prescription saved successfully!"
    );
  } catch (e) {
    console.error(e);
    toast.value.showToast("error", "Failed to save prescription");
  } finally {
    saving.value = false;
  }
}


async function loadHistory() {
  activeTab.value = "history";
  loadingHistory.value = true;
  try {
    const res = await axios.get(`/api/patients/${patient.value.id}/prescriptions`);
    history.value = res.data;
  } catch (e) {
    console.error(e);
  } finally {
    loadingHistory.value = false;
  }
}

async function getAiSuggestion() {

  if (!form.value.diagnosis) {
    return alert("Please enter a disease!");
  }

  aiLoading.value = true;
  formDisabled.value = true;
  aiText.value = "";

  // Fake typing text effect
  let dots = 0;
  const interval = setInterval(() => {
    dots = (dots + 1) % 4;
    aiText.value = "AI is analyzing" + ".".repeat(dots);
  }, 400);

  try {
    const res = await axios.post("/api/ai/homeopathy-suggest", {
      diagnosis: form.value.diagnosis
    });

    if (res.data?.medicines?.length) {
      form.value.medicines = res.data.medicines;
    } else {
      aiText.value = "No AI suggestions found.";
    }

  } catch (err) {
    console.error(err);
    aiText.value = "AI failed — try again!";
  } finally{
    formDisabled.value = false;
  }

  clearInterval(interval);

  setTimeout(() => {
    aiLoading.value = false;
    aiText.value = "";
  }, 800);
}



// --- Utility Functions ---
function addMedicine() {
  form.value.medicines.push({
    medicine_name: "",
    dosage: "",
    duration: "",
    instructions: "",
  });
}
function removeMedicine(i) {
  form.value.medicines.splice(i, 1);
}
function formatDate(date) {
  return new Date(date).toLocaleDateString("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
}
function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value;
}
async function logout() {
  logoutLoading.value = true;
  try {
    await axios.post("/api/logout");
    localStorage.removeItem("user");
    router.push("/login");
  } catch (e) {
    console.error(e);
    alert("Logout failed");
  } finally {
    logoutLoading.value = false;
  }
}
</script>

<style scoped>
/* Layout container */
.layout {
  display: flex;
  min-height: 100vh;
  background-color: #f8f9fb;
}

/* Sidebar fixed on left */
Sidebar {
  width: 250px;
  min-height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
  background-color: #0d6efd;
  color: #fff;
  z-index: 1000;
  transition: all 0.3s ease;
}

/* Main content */
.main-content {
  flex-grow: 1;
  margin-left: 250px;
  transition: margin-left 0.3s ease;
}

/* Mobile view */
@media (max-width: 992px) {
  Sidebar {
    left: -250px;
  }
  Sidebar.active {
    left: 0;
  }
  .main-content {
    margin-left: 0;
  }
}
.ai-loader {
  height: 28px;
  border-radius: 4px;
  background: #e9f3ff;
  position: relative;
  overflow: hidden;
}

.ai-loader .shimmer {
  width: 40%;
  height: 100%;
  background: linear-gradient(90deg, #e9f3ff, #d2e7ff, #e9f3ff);
  animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(200%); }
}

</style>
