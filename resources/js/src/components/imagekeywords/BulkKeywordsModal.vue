<template>
  <div
    ref="modalRef"
    class="modal fade"
    aria-hidden="true"
    style="background: #50505021"
  >
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Multiple Keywords</h5>
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
                  <div class="custom-file-container">
                    <label>Keywords: </label>
                    <textarea
                      style="height: 300px"
                      class="form-control"
                      v-model="bulkKeywords"
                      placeholder="Please enter a keyword per line"
                    ></textarea>
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
            data-dismiss="modal"
            data-bs-dismiss="modal"
          >
            Close
          </button>
          <button @click="sendKeywords" class="btn btn-primary">OK</button>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, toRef } from "vue";
import useBsModal from "@/composables/useBsModal";
const bulkKeywords = ref("");

const emit = defineEmits(["closeModal", "get-keywords"]);
const props = defineProps({ isShow: Boolean });

const sendKeywords = () => {
  emit("get-keywords", bulkKeywords.value);
  bulkKeywords.value = "";
  emit("closeModal");
};

const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);
</script>
