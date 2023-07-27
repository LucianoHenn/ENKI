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
            <h5 class="modal-title">Create Option</h5>
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
                      <Field
                        name="name"
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': errors.name }"
                        placeholder="Name"
                      />
                      <ErrorMessage class="invalid-feedback" name="name" />
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Value (in JSON)</label>
                      <Field
                        name="value"
                        as="textarea"
                        class="form-control option-value"
                        :class="{ 'is-invalid': errors.value }"
                        placeholder="Option value"
                        :row="15"
                      />
                      <ErrorMessage class="invalid-feedback" name="value" />
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Autoload Option</label>
                      <Field name="autoload" as="select" class="form-select">
                        <option selected value="0">No</option>
                        <option value="1">Yes</option>
                      </Field>
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
              :class="{disabled : store.getters.isLoading}"
              data-dismiss="modal"
              data-bs-dismiss="modal"
            >
              Discard
            </button>
            <submit-button
              class="btn btn-primary"
              buttonText="Add Option"
              textLoading="Loading..."
              :is-loading="store.getters.isLoading"
            />
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
import { toRef } from "vue";
import { Form, Field, ErrorMessage } from "vee-validate";
import { showMessage } from "@/utils/toast";
import { useCreateOption } from "@/composables/use-option";
import useBsModal from "@/composables/useBsModal";
import SubmitButton from "@/components/form/SubmitButton";

const emit = defineEmits(["closeModal", "optionCreated"]);
const store = useStore();
const props = defineProps({
  isShow: Boolean,
});

const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);

const { onSubmit, errors } = useCreateOption((res) => {
  emit("optionCreated", res.data);
  showMessage(res.message);
  closeModal();
});
</script>
