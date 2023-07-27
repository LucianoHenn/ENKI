<template>
  <div
    ref="modalRef"
    id="addKeywordImageModal"
    class="modal fade"
    aria-labelledby="addKeywordImageModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-md modal-dialog-centered">
      <form>
        <div class="modal-content mailbox-popup">
          <div class="modal-header">
            <h5 class="modal-title">Add new keywords</h5>
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

                      <textarea
                        name="keywords"
                        class="form-control"
                        rows="5"
                        v-model="formData.keywords"
                        placeholder="Enter keywords"
                        :class="{ 'is-invalid': errors.keywords }"
                      />

                      <div v-if="errors.keywords" class="invalid-feedback">
                        At least one keyword is required
                      </div>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Tags</label>

                      <tags-select
                        @tag="addTag"
                        v-model="formData.tags"
                        :class="{ 'is-invalid': errors.tags }"
                      />
                      <div v-if="errors.tags" class="invalid-feedback">
                        At least one tag is required
                      </div>
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Countries</label>

                      <countries-select
                        :class="{ 'is-invalid': errors.countries }"
                        v-model="formData.countries"
                      />

                      <div v-if="errors.countries" class="invalid-feedback">
                        At least one country is required
                      </div>
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Language</label>

                      <languages-select
                        v-model="formData.languages"
                        :multiple="false"
                        :class="{ 'is-invalid': errors.languages }"
                      />

                      <div v-if="errors.languages" class="invalid-feedback">
                        At least one language is required
                      </div>
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Category</label>

                      <categories-select
                        v-model="formData.category"
                        :closeOnSelect="true"
                        :multiple="false"
                      />
                    </div>
                  </div>

                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Image Source</label>

                      <!-- Inline radio -->
                      <div
                        role="radiogroup"
                        tabindex="-1"
                        class="bv-no-focus-ring"
                      >
                        <div
                          class="radio-classic radio-primary custom-control d-inline-flex custom-radio me-3"
                        >
                          <input
                            type="radio"
                            class="custom-control-input"
                            value="1"
                            id="rdo1"
                            name="rdoinline"
                            v-model="imageSource"
                          />
                          <label class="custom-control-label" for="rdo1"
                            >From Images</label
                          >
                        </div>
                        <div
                          class="radio-classic radio-primary custom-control d-inline-flex custom-radio me-3"
                        >
                          <input
                            type="radio"
                            class="custom-control-input"
                            value="2"
                            id="rdo2"
                            name="rdoinline"
                            v-model="imageSource"
                          />
                          <label class="custom-control-label" for="rdo2"
                            >From Unsplash</label
                          >
                        </div>
                        <div
                          class="radio-classic radio-primary custom-control d-inline-flex custom-radio me-3"
                        >
                          <input
                            type="radio"
                            class="custom-control-input"
                            value="3"
                            id="rdo3"
                            name="rdoinline"
                            v-model="imageSource"
                          />
                          <label class="custom-control-label" for="rdo3"
                            >Upload New</label
                          >
                        </div>
                      </div>
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
            <button
              @click.prevent="onSubmit"
              type="button"
              class="btn btn-primary"
              textLoading=""
            >
              <span>Add Keywords</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
<script setup>
import { useStore } from "vuex";
import { toRef, ref, watch } from "vue";
import { showMessage } from "@/utils/toast";
import { useAddKeyword } from "@/composables/use-image";
import useBsModal from "@/composables/useBsModal";
import CountriesSelect from "@/components/form/CountriesSelect.vue";
import LanguagesSelect from "@/components/form/LanguagesSelect.vue";
import TagsSelect from "@/components/form/TagsSelect.vue";
import SubmitButton from "@/components/form/SubmitButton.vue";
import CategoriesSelect from "@/components/form/CategoriesSelect.vue";
import Multiselect from "@suadelabs/vue3-multiselect";
import "@suadelabs/vue3-multiselect/dist/vue3-multiselect.css";

const imageSource = ref(1);

const formData = ref({
  keywords: "",
  countries: [],
  tags: [],
  languages: "",
  category: "",
});

const errors = ref({
  keywords: false,
  countries: false,
  languages: false,
  tags: false,
});

const emit = defineEmits([
  "closeModal",
  "keywordAdded",
  "addAssocImages",
  "uploadImages",
]);
const props = defineProps({ isShow: Boolean });
const store = useStore();

const setFormData = (data) => {
  formData.value = {
    keywords: "",
    countries: [],
    tags: [],
    languages: "",
    category: "",
  };
  formData.value.keywords = data.toString();
};
const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);

watch(formData.value, () => {
  if (Object.values(errors.value).filter(Boolean).length > 0) validate();
});

const addTag = (newTag) => {
  formData.value.tags.push(newTag);
};

const validate = () => {
  errors.value = {
    keywords: false,
    countries: false,
    languages: false,
    tags: false,
  };
  if (!formData.value.keywords.trim()) {
    errors.value.keywords = true;
  }
  if (!formData.value.countries.length) {
    errors.value.countries = true;
  }
  if (!formData.value.tags.length) {
    errors.value.tags = true;
  }
  if (!formData.value.languages) {
    errors.value.languages = true;
  }

  return Object.values(errors.value).filter(Boolean).length === 0;
};

const onSubmit = () => {
  if (!validate()) {
    showMessage("Please fill all required fields", "error");
    return;
  }
  // formData.value.keywords = formData.value.keywords.split(",");
  if (imageSource.value == 3) emit("uploadImages", formData.value);
  else {
    formData.value.fromUnsplash = imageSource.value == 2;
    emit("addAssocImages", formData.value);
  }
};

defineExpose({ setFormData });
</script>

<style>
.multiselect.is-invalid {
  border: 1px solid #f86c6b !important;
  border-radius: 5px;
}
</style>
