<template>
  <div
      ref="modalRef"
      id="addDomainModal"
      class="modal fade"
      aria-labelledby="addDomainModalLabel"
      aria-hidden="true"
  >
    <div class="modal-dialog modal-md modal-dialog-centered">
      <form>
        <div class="modal-content mailbox-popup">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ (id ? "Update" : "Create") + " Domain" }}
            </h5>
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
                  <Field name="id" type="hidden" v-model="id" />
                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Name</label>
                      <input
                          type="text"
                          class="form-control"
                          placeholder="Name"
                          v-model="domain.name"
                      />
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label
                      >Template URL

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="feather feather-help-circle"
                            data-v-5522efca=""
                            data-bs-toggle="tooltip"
                            data-bs-html="true"
                            title="You can use the following placeholders in the url<br/>[market], [domain-display-name], [query], [user]"
                        >
                          <circle cx="12" cy="12" r="10"></circle>
                          <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                          <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                      </label>

                      <input
                          type="text"
                          class="form-control"
                          placeholder="Url"
                          v-model="domain.url"
                      />
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Countries</label>
                      <countries-select v-model="domain.countries" />
                    </div>
                  </div>
                </div>
                <!-- <div class="row">
                  <div class="col-md-12">
                    <div class="custom-file-container">
                      <label>Partnership</label>

                      <partnerships-select
                        ref="partnershipSelectComponent"
                        v-model="domain.partnership"
                        :multiple="false"
                        :closeOnSelect="true"
                      />
                    </div>
                  </div>
                </div> -->

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Status</label>
                      <select class="form-select" v-model="domain.status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                      </select>
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
                @click.prevent="submitForm"
            >
              {{ id ? "Update Domain" : "Add New Domain" }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
<script setup>
import { useStore } from "vuex";
import { ref, toRef, onMounted } from "vue";
import { Form, Field, ErrorMessage } from "vee-validate";
import { showMessage } from "@/utils/toast";
import { initTooltip } from "@/utils/tooltip";

import useBsModal from "@/composables/useBsModal";
import CountriesSelect from "@/components/form/CountriesSelect.vue";
import PartnershipsSelect from "@/components/form/taboola/PartnershipsSelect.vue";
import SubmitButton from "@/components/form/SubmitButton.vue";
import taboolaDomainsApi from "@/services/api/taboola/domains";

const emit = defineEmits(["closeModal", "domainCreated"]);
const props = defineProps({ isShow: Boolean });
const id = ref(null);
const domain = ref({
  name: "",
  url: "",
  countries: [],
  partnership: {},
  status: "active",
});
const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);
const store = useStore();

const setDomainData = (
    row = {
      name: "",
      url: "",
      countries: [],
      partnership: {},
      status: "active",
    }
) => {
  domain.value.name = row.name;
  domain.value.url = row.url;
  domain.value.countries = row.countries;
  domain.value.partnership = row.partnership;
  domain.value.status = row.status;
  id.value = row.id;
};

const submitForm = async () => {
  try {
    let res;
    if (id.value)
      res = await taboolaDomainsApi.updateDomain({
        id: id.value,
        ...domain.value,
      });
    else res = await taboolaDomainsApi.createDomain(domain.value);
    showMessage(res.message);
    emit("domainCreated");
    closeModal();
  } catch (error) {
    const errorMessage = error?.response?.data?.message;
    showMessage(errorMessage, "error");
  }
};

onMounted(() => {
  initTooltip();
});

defineExpose({ setDomainData });
</script>
