<template>
  <div
    ref="modalRef"
    id="addKeywordImageModal"
    class="modal fade"
    aria-labelledby="addKeywordImageModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-md modal-dialog-centered">
      <form @submit="onSubmit">
        <div class="modal-content mailbox-popup">
          <div class="modal-header">
            <h5 class="modal-title">Add keywords to image</h5>
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
                      <label>Keywords</label>
                      <Field
                        name="id"
                        type="hidden"
                        class="hidden"
                        v-model="formData.id"
                      />
                      <Field
                        name="keywords"
                        as="textarea"
                        class="form-control"
                        v-model="formData.keywords"
                        :class="{ 'is-invalid': errors.keywords }"
                      >
                        <textarea
                          name="keywords"
                          class="form-control"
                          rows="5"
                          v-model="formData.keywords"
                          placeholder="Enter keywords"
                          />
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
                        class="form-select"
                        v-model="formData.tags"
                        :class="{ 'is-invalid': errors.tags }"
                        multiple
                      >
                        <tags-select @tag="addTag" v-model="formData.tags" />
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
                        :class="{ 'is-invalid': errors.countries }"
                        class="form-select"
                        v-model="formData.countries"
                        multiple
                      >
                        <countries-select
                          v-model="formData.countries"
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
                        v-model="formData.languages"
                      >
                        <languages-select
                          v-model="formData.languages"
                          :multiple="false"
                          :class="{ 'is-invalid': errors.languages }"
                        />
                      </Field>
                      <ErrorMessage class="invalid-feedback" name="languages" />
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
              buttonText="Add Keywords"
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
import { toRef, ref } from "vue";
import { Form, Field, ErrorMessage } from "vee-validate";
import { showMessage } from "@/utils/toast";
import { useAddKeyword } from "@/composables/use-image";
import useBsModal from "@/composables/useBsModal";
import CountriesSelect from "@/components/form/CountriesSelect.vue";
import LanguagesSelect from "@/components/form/LanguagesSelect.vue";
import TagsSelect from "@/components/form/TagsSelect.vue";
import SubmitButton from "@/components/form/SubmitButton.vue";

const formData = ref({
  id: null,
  keywords: "",
  countries: [],
  tags: [],
  languages: [],
});

const emit = defineEmits(["closeModal", "keywordAdded"]);
const props = defineProps({ isShow: Boolean });
const store = useStore();
const setFormData = (data) => {
  formData.value.id = data.id;
};
const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);
const { onSubmit, errors } = useAddKeyword((res) => {
  emit("keywordAdded", res.data);
  showMessage(res.message);
  closeModal();
});

const addTag = (newTag) => {
  formData.value.tags.push(newTag);
};

defineExpose({ setFormData });
</script>
