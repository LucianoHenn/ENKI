<template>
  <div
      ref="modalRef"
      id="addOptionModal"
      class="modal fade"
      aria-labelledby="addOptionModalLabel"
      aria-hidden="true"
  >
    <div class="modal-dialog modal-md modal-dialog-centered">
      <form @submit="onSubmit">
        <div class="modal-content mailbox-popup">
          <div class="modal-header">
            <h5 class="modal-title">Create New Template</h5>
            <button
                type="button"
                data-dismiss="modal"
                data-bs-dismiss="modal"
                aria-label="Close"
                class="btn-close"
            ></button>
          </div>
          <div class="modal-body">
            <div class="add-contact-box">
              <div class="add-contact-content">
                <div class="row">
                  <div class="mb-4">
                    <label class="col-form-label">Template Description</label>
                    <div>
                      <input
                          type="text"
                          class="form-control"
                          placeholder="Description"
                          v-model="description"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button
                type="button"
                class="btn btn-default"
                :class="{ disabled: store.getters.isLoading }"
                data-dismiss="modal"
                data-bs-dismiss="modal"
            >
              Discard
            </button>
            <button
                type="button"
                class="btn btn-primary"
                :class="{ disabled: store.getters.isLoading }"
                @click.prevent="createTemplate"
            >
              Add new template
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
<style scoped lang="scss">
.option-value {
  min-height: 350px;
}
</style>
<script setup>
import { useStore } from "vuex";
import { toRef, ref } from "vue";
import { Form, Field, ErrorMessage } from "vee-validate";
import { showMessage } from "@/utils/toast";
import { useCreateOption } from "@/composables/use-option";
import useBsModal from "@/composables/useBsModal";
import templatesApi from "@/services/api/taboola/templates";

const emit = defineEmits(["closeModal", "submitFormDirectly"]);
const description = ref("");
const template = ref({
  campaign_settings: {
    name_suffix: "",
    marketing_objective: "",
    brand_name: "",
    conversion_event: "",
  },
  targeting: {
    country_targeting: [],
    platform_targeting: [],
    browser_targeting: [],
    os_targeting: [],
    excludes: {
      country_targeting: false,
      platform_targeting: false,
      browser_targeting: false,
      os_targeting: false,
    },
  },
  budget: {
    cpc: "",
    spending_limit: "",
    spending_limit_model: "ENTIRE",
    daily_budget: "",
  },
});
const store = useStore();
const props = defineProps({
  isShow: Boolean,
});

const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);

const createTemplate = async () => {
  if (description.value.trim() === "") {
    showMessage("Description can not be empty", "error");
    return;
  }
  const payload = {
    template: template.value,
    description: description.value,
  };
  const response = await templatesApi.createTemplate(payload);
  if (response.success) {
    showMessage("Template created successfully");
    emit("submitFormDirectly");
    closeModal();
  } else {
    showMessage("Could not create template, please try again later", "error");
  }
};

const setData = (data) => {
  template.value = data;
};

defineExpose({ setData });
</script>
