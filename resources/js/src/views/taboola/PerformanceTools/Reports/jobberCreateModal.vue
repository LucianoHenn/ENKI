<template>
  <!-- Modal -->
  <div
    ref="staticModalRef"
    class="modal fade"
    id="createModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="createModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="createModalLabel">Create new report</h5>
          <button
            type="button"
            data-dismiss="modal"
            data-bs-dismiss="modal"
            aria-label="Close"
            class="btn-close"
          ></button>
        </div>
        <div class="modal-body">
          <p class="modal-text">
            Performance analyzer report for Taboola vs Yahoo
          </p>

          <!-- Select tag -->
          <div class="mb-3">
            <label for="selectOption" class="form-label">Ad Accounts:</label>
            <options-select
              v-model="ad_accounts"
              :closeOnSelect="true"
              optionName="taboola_adaccounts"
              :multiple="true"
            />
            <small id="dateBeginHelp" class="form-text text-muted"
              >Select the one or more ad accounts.</small
            >
          </div>

          <!-- Time range selector -->
          <div class="mb-3">
            <label for="dateBegin" class="form-label">Date Begin:</label>
            <input
              v-model="date_begin"
              type="date"
              class="form-control"
              id="dateBegin"
              aria-describedby="dateBeginHelp"
            />
            <small id="dateBeginHelp" class="form-text text-muted"
              >Select the beginning date.</small
            >
          </div>
          <div class="mb-3">
            <label for="dateEnd" class="form-label">Date End:</label>
            <input
              v-model="date"
              type="date"
              class="form-control"
              id="dateEnd"
              aria-describedby="dateEndHelp"
            />
            <small id="dateEndHelp" class="form-text text-muted"
              >Select the end date.</small
            >
          </div>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn"
            data-dismiss="modal"
            data-bs-dismiss="modal"
          >
            <i class="flaticon-cancel-12"></i> Discard
          </button>
          <button type="button" class="btn btn-primary" @click="submitForm">
            Create
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, toRef, onMounted, computed } from "vue";
import { showMessage } from "@/utils/toast";
import useBsModal from "@/composables/useBsStaticModal";
import OptionsSelect from "@/components/form/taboola/OptionsSelect.vue";
import jobberApi from "@/services/api/jobber";

// Modal settings
const emit = defineEmits(["JobberCreated", "CloseModal"]);
const props = defineProps({ isShow: Boolean });
const { staticModalRef, closeStaticModal } = useBsModal(
  toRef(props, "isShow"),
  emit
);

const ad_accounts = ref([]);
const date_begin = ref(null);
const date = ref(null);

const submitForm = async () => {
  const identifiers = ad_accounts.value.map((x) => x.name);

  if (!date_begin.value || !date.value || !identifiers.length) {
    showMessage("All fields are required", "error");
    return;
  }

  const payload = {
    args: {
      ad_accounts: identifiers,
      date: date.value,
      date_begin: date_begin.value,
    },
    class: "Taboola\\PerformanceAnalyzerReport",
    description: "",
  };
  console.log("payload payload", payload);

  try {
    const response = await jobberApi.createJobber(payload);

    if (response.success) {
      showMessage("Jobber created successfully");
      emit("JobberCreated");
      emit("CloseModal");
      //resetFormData();
    }
  } catch (e) {
    showMessage(e?.response?.data?.message, "error");
  }
};
</script>
