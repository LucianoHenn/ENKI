<template>
  <div
    ref="modalRef"
    id="addPartnershipModal"
    class="modal fade"
    aria-labelledby="addPartnershipModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-md modal-dialog-centered">
      <form>
        <div class="modal-content mailbox-popup">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ (id ? "Update" : "Create") + " Partnership" }}
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
                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Name</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="partnership.name"
                        placeholder="Name"
                      />
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Countries</label>
                      <countries-select v-model="partnership.countries" />
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
              @click.prevent="submitForm"
            >
              {{ id ? "Update Partnership" : "Add New Partnership" }}
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
import { showMessage } from "@/utils/toast";
import { initTooltip } from "@/utils/tooltip";

import taboolaPartnershipApi from "@/services/api/taboola/partnerships";
import useBsModal from "@/composables/useBsModal";
import CountriesSelect from "@/components/form/CountriesSelect.vue";

const emit = defineEmits(["closeModal", "partnershipCreated"]);
const props = defineProps({ isShow: Boolean });
const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);
const store = useStore();
const id = ref(null);
const partnership = ref({ name: "", countries: [] });

const submitForm = async () => {
  const { name, countries } = partnership.value;
  try {
    let res;
    if (id.value)
      res = await taboolaPartnershipApi.updatePartnership({
        id: id.value,
        name,
        countries,
      });
    else
      res = await taboolaPartnershipApi.createPartnership({ name, countries });
    showMessage(res.message);
    emit("partnershipCreated");
    closeModal();
  } catch (error) {
    const errorMessage = error?.response?.data?.message;
    showMessage(errorMessage, "error");
  }
};

const setPartnershipData = (row = { name: "", countries: [], id: null }) => {
  partnership.name = row.name;
  partnership.countries = row.countries;
  id.value = row.id;
};

defineExpose({ setPartnershipData });

onMounted(() => {
  initTooltip();
});
</script>
