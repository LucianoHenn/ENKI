<template>
  <div
    ref="modalRef"
    id="addKeywordModal"
    class="modal fade"
    aria-labelledby="addKeywordModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-md modal-dialog-centered">
      <form @submit="onSubmit">
        <div class="modal-content mailbox-popup">
          <div class="modal-header">
            <h5 class="modal-title">Create Keyword</h5>
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
                        multiple
                        :class="{ 'is-invalid': errors.tags }"
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
                      <label>Language</label>
                      <Field
                        name="language"
                        type="select"
                        class="form-select"
                        v-model="language"
                        :class="{ 'is-invalid': errors.language }"
                      >
                        <languages-select
                          v-model="language"
                          :class="{ 'is-invalid': errors.language }"
                        />
                      </Field>
                      <ErrorMessage class="invalid-feedback" name="language" />
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Category</label>
                      <Field
                        name="category"
                        type="select"
                        v-model="category"
                        class="form-select"
                      >
                        <categories-select
                          v-model="category"
                          :closeOnSelect="true"
                          :multiple="false"
                        />
                      </Field>
                      <ErrorMessage class="invalid-feedback" name="category" />
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
              Discard
            </button>
            <button type="submit" class="btn btn-primary">Add Keyword</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
<script setup>
import { ref, toRef } from "vue";
import { Form, Field, ErrorMessage } from "vee-validate";
import { showMessage } from "@/utils/toast";
import { useCreateKeyword } from "@/composables/database/use-keyword";
import useBsModal from "@/composables/useBsModal";
import CountriesSelect from "@/components/form/CountriesSelect.vue";
import LanguagesSelect from "@/components/form/LanguagesSelect.vue";
import TagsSelect from "@/components/form/TagsSelect.vue";
import CategoriesSelect from "@/components/form/CategoriesSelect.vue";

const emit = defineEmits(["closeModal", "keywordCreated"]);
const props = defineProps({ isShow: Boolean });

const language = ref(null);
const countries = ref([]);
const tags = ref([]);
const category = ref(null);

const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);
const { onSubmit, errors } = useCreateKeyword((res) => {
  emit("keywordCreated", res.data);
  showMessage(res.message);
  closeModal();
});

const addTag = (newTag) => {
  tags.value.push(newTag);
};
</script>
