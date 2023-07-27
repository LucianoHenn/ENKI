<template>
  <div
    ref="modalRef"
    id="addAssociateImageModal"
    class="modal fade"
    aria-labelledby="addAssociateImageModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-md modal-dialog-centered">
      <form @submit="onSubmit" enctype="multipart/form-data">
        <div class="modal-content mailbox-popup">
          <div class="modal-header">
            <h5 class="modal-title">Bulk edit modal</h5>
            <button
              type="button"
              data-dismiss="modal"
              data-bs-dismiss="modal"
              aria-label="Close"
              class="btn-close"
            ></button>
          </div>
          <div class="modal-body">
            <div class="add-associate-box">
              <div class="add-associate-content">
                <p>
                  Choose the next step, upload a new image for these keywords or
                  choose images from the list.
                </p>
                <div class="d-flex gap-2 justify-content-around">
                  <button
                    class="btn btn-primary"
                    @click.prevent="emit('uploadImages', keyword.ids)"
                  >
                    Upload new Images
                  </button>
                  <button
                    class="btn btn-primary"
                    @click.prevent="emit('addAssocImage', keyword)"
                  >
                    Choose Images From list
                  </button>
                  <button
                    class="btn btn-primary"
                    @click.prevent="emit('assignCategory', keyword.ids)"
                  >
                    Assign Category
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
<script setup>
import { ref, toRef } from "vue";
import useBsModal from "@/composables/useBsModal";
const keyword = ref({
  ids: [],
  image_ids: [],
});

const setKeywordDataId = (keywordIds) => {
  keyword.value.ids = keywordIds;
};

const emit = defineEmits([
  "closeModal",
  "uploadImages",
  "addAssocImage",
  "assignCategory",
]);
const props = defineProps({ isShow: Boolean });
const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);
defineExpose({ setKeywordDataId });
</script>
