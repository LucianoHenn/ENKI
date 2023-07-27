<template>
  <div
    ref="modalRef"
    id="editImageModal"
    class="modal fade"
    aria-labelledby="editImageModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-md modal-dialog-centered edit-image-modal">
      <form @submit="onSubmit">
        <div class="modal-content mailbox-popup">
          <div class="modal-header">
            <h5 class="modal-title">Edit Image</h5>
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
                  <Field
                    name="id"
                    type="hidden"
                    class="hidden"
                    v-model="image.id"
                  />
                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Tags</label>
                      <Field
                        name="tags"
                        type="select"
                        v-model="image.tags"
                        class="form-select"
                        multiple
                      >
                        <tags-select @tag="addTag" v-model="image.tags" />
                      </Field>
                      <ErrorMessage class="invalid-feedback" name="tags" />
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Image Name</label>
                      <Field
                        name="image_name"
                        type="text"
                        v-model="image.image_name"
                        class="form-control"
                      >
                      </Field>
                      <ErrorMessage
                        class="invalid-feedback"
                        name="image_name"
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
            <submit-button
              class="btn btn-primary"
              buttonText="Update Image"
              textLoading="Loading..."
              :is-loading="store.getters.isLoading"
            />
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.edit-image-modal {
  form {
    min-width: 500px;
  }
}
</style>
<script setup>
import { useStore } from "vuex";
import { toRef, ref } from "vue";
import { Form, Field, ErrorMessage } from "vee-validate";
import { showMessage } from "@/utils/toast";
import { useUpdateImage } from "@/composables/use-image";
import useBsModal from "@/composables/useBsModal";
import TagsSelect from "@/components/form/TagsSelect.vue";
import SubmitButton from "@/components/form/SubmitButton.vue";

const image = ref({
  id: null,
  tags: [],
  image_name: "",
});

const emit = defineEmits(["closeModal", "imageUpdated"]);
const props = defineProps({ isShow: Boolean });
const store = useStore();

const setImageData = (data) => {
  image.value.id = data.id;
  image.value.tags = data.tags.map((tag) => {
    return tag.value;
  });
  image.value.image_name = data.image_name;
};

const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);
const { onSubmit, errors } = useUpdateImage((res) => {
  emit("imageUpdated", res.data);
  showMessage(res.message);
  closeModal();
});

const addTag = (newTag) => {
  image.value.tags.push(newTag);
};

defineExpose({ setImageData });
</script>
