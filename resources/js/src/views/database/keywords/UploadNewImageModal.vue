<template>
  <div
    ref="modalRef"
    id="uploadImageModal"
    class="modal fade"
    aria-labelledby="uploadImageModalLabel"
    aria-hidden="true"
    style="background: #50505021"
  >
    <div class="modal-dialog modal-md modal-dialog-centered">
      <form @submit="onSubmit" enctype="multipart/form-data">
        <div class="modal-content mailbox-popup">
          <div class="modal-header">
            <h5 class="modal-title">Upload Images</h5>
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
                      <label>Upload (Allow Multiple)</label>
                      <Field name="ids" type="hidden" v-model="keyword.ids" />
                      <Field
                        @change="previewFiles"
                        name="images"
                        :class="{ 'is-invalid': errors.images }"
                        multiple
                        type="file"
                        class="form-control"
                      />
                      <ErrorMessage class="invalid-feedback" name="images" />
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
              v-if="!Array.isArray(keyword.ids)"
              class="btn btn-primary"
              buttonText="Add Image"
              textLoading="Loading..."
              :is-loading="store.getters.isLoading"
            />
            <button
              v-else
              @click.prevent="createKeywords"
              type="button"
              class="btn btn-primary"
              textLoading=""
              :is-loading="store.getters.isLoading"
            >
              <span
                v-if="isLoading"
                class="spinner-border text-white me-2 align-self-center loader-sm"
                >{{ textLoading }}</span
              >
              <span v-if="isLoading">Loading...</span>
              <span v-else>Add Keywords</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
<script setup>
import { useStore } from "vuex";
import { ref, toRef } from "vue";
import { Form, Field, ErrorMessage } from "vee-validate";
import { showMessage } from "@/utils/toast";
import { useUploadImage } from "@/composables/database/use-keyword";
import useBsModal from "@/composables/useBsModal";
import SubmitButton from "@/components/form/SubmitButton.vue";
import keywordApi from "@/services/api/database/keywords";

const store = useStore();
const keyword = ref({ ids: [] });

const emit = defineEmits(["closeModal", "imageUploaded"]);
const props = defineProps({ isShow: Boolean });
let images = [];

const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);
const { onSubmit, errors } = useUploadImage((res) => {
  emit("imageUploaded", res.data);
  modalRef.value.querySelector('input[type="file"]').value = "";
  showMessage(res.message);
  closeModal();
});

const createKeywords = async () => {
  keyword.value.images = images;
  const formData = new FormData();
  keyword.value.images.forEach((image) => {
    formData.append("images[]", image);
  });
  keyword.value.tags.forEach((tag) => {
    formData.append("tags[]", tag);
  });
  keyword.value.countries.forEach((country) => {
    formData.append("countryIds[]", country.id);
  });

  formData.append("keywords", keyword.value.keywords);
  formData.append("category", keyword.value.category.id);
  formData.append("languageId", keyword.value.language.id);
  try {
    const res = await keywordApi.createKeywordWithImageUpload(formData, {
      "Content-Type": "multipart/form-data",
    });
    showMessage(res.message);
    closeModal();
  } catch (e) {
    showMessage(e.message, "error");
  }
};

const previewFiles = (event) => {
  images = Array.from(event.target.files);
};

const setKeywordData = (data) => {
  keyword.value.keywords = data.keywords;
  keyword.value.category = data.category;
  keyword.value.countries = data.countries;
  keyword.value.language = data.languages;
  keyword.value.tags = data.tags;
};

const setKeywordDataId = (keywordIds) => {
  keyword.value.ids = keywordIds;
};
defineExpose({ setKeywordDataId, setKeywordData });
</script>
