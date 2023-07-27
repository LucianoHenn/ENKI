<template>
  <div
    ref="modalRef"
    id="addImageModal"
    class="modal fade"
    aria-labelledby="addImageModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-md modal-dialog-centered">
      <form @submit="onSubmit">
        <div class="modal-content mailbox-popup">
          <div class="modal-header">
            <h5 class="modal-title">Create Image</h5>
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
                      <label
                        >Keywords
                        <small
                          >(use comma or new line for each keyword)</small
                        ></label
                      >
                      <Field
                        name="keywords"
                        as="textarea"
                        class="form-control"
                        :class="{ 'is-invalid': errors.keywords }"
                      >
                        <textarea
                          name="keywords"
                          class="form-control"
                          rows="5"
                          placeholder="Enter keywords"
                        ></textarea>
                      </Field>
                      <ErrorMessage class="invalid-feedback" name="keywords" />
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Tags</label>
                      <Field
                        name="tags"
                        type="select"
                        v-model="tags"
                        class="form-select"
                        :class="{ 'is-invalid': errors.tags }"
                        multiple
                      >
                        <tags-select
                          @tag="addTag"
                          v-model="tags"
                          :class="{ 'is-invalid': errors.tags }"
                        />
                      </Field>
                      <ErrorMessage class="invalid-feedback" name="tags" />
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Countries</label>
                      <Field
                        name="countries"
                        type="select"
                        v-model="countries"
                        :class="{ 'is-invalid': errors.countries }"
                        class="form-select"
                        multiple
                      >
                        <countries-select
                          v-model="countries"
                          :class="{ 'is-invalid': errors.countries }"
                        />
                      </Field>
                      <ErrorMessage class="invalid-feedback" name="countries" />
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Languages</label>
                      <Field
                        name="languages"
                        type="select"
                        class="form-select"
                        v-model="languages"
                        multiple
                      >
                        <languages-select
                          v-model="languages"
                          :multiple="false"
                          :class="{ 'is-invalid': errors.languages }"
                        />
                      </Field>
                      <ErrorMessage class="invalid-feedback" name="languages" />
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Category</label>
                      <Field
                        name="category"
                        type="select"
                        v-model="category"
                        :class="{ 'is-invalid': errors.category }"
                        class="form-select"
                      >
                        <categories-select
                          v-model="category"
                          :class="{ 'is-invalid': errors.category }"
                          :closeOnSelect="true"
                          :multiple="false"
                        />
                      </Field>
                      <ErrorMessage class="invalid-feedback" name="category" />
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div
                      class="custom-file-container"
                      data-upload-id="mySecondImage"
                    >
                      <label>Upload (Allow Multiple)</label>
                      <Field
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
              class="btn btn-primary"
              buttonText="Add Image"
              textLoading="Loading..."
              :is-loading="store.getters.isLoading"
            />
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
import { useCreateImage } from "@/composables/use-image";

import useBsModal from "@/composables/useBsModal";
import CountriesSelect from "@/components/form/CountriesSelect.vue";
import LanguagesSelect from "@/components/form/LanguagesSelect.vue";
import TagsSelect from "@/components/form/TagsSelect.vue";
import CategoriesSelect from "@/components/form/CategoriesSelect.vue";
import SubmitButton from "@/components/form/SubmitButton.vue";

const emit = defineEmits(["closeModal", "imageCreated"]);
const props = defineProps({ isShow: Boolean });
const isLoading = ref(false);
const languages = ref([]);
const countries = ref([]);
const category = ref();
const tags = ref([]);
const store = useStore();

const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);

const { onSubmit, errors } = useCreateImage((res) => {
  emit("imageCreated", res.data);
  modalRef.value.querySelector('input[type="file"]').value = "";
  isLoading.value = false;
  showMessage(res.message);
  closeModal();
});

const addTag = (newTag) => {
  tags.value.push(newTag);
};
</script>
