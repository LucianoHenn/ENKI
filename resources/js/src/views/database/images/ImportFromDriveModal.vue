<template>
  <div
    ref="modalRef"
    class="modal fade"
    id="importFromDriveModal"
    aria-labelledby="importFromDriveModalLavel"
    aria-hidden="true"
    data-keyboard="false"
    data-backdrop="static"
  >
    <div
      class="modal-dialog modal-fullscreen modal-dialog-centered import-from-drive-modal"
    >
      <div class="modal-content mailbox-popup">
        <div class="modal-header">
          <h5 class="modal-title h4" id="exampleModalFullscreenLabel">
            Import Images From Google Drive URL
          </h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body mb-3 mt-3">
          <div class="panel-body">
            <div id="iconsAccordion" class="accordion accordion-icons mb-2">
              <div class="card">
                <header class="card-header" role="button">
                  <section class="mb-0 mt-0">
                    <div
                      class="collapsed d-flex gap-3"
                      role="menu"
                      data-bs-toggle="collapse"
                      data-bs-target="#headingOne3"
                      aria-expanded="false"
                      aria-controls="headingOne3"
                    >
                      <div class="accordion-icon">
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="24"
                          height="24"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          class="feather feather-alert-circle"
                          style="color: #e7515a"
                        >
                          <circle cx="12" cy="12" r="10"></circle>
                          <line x1="12" y1="8" x2="12" y2="12"></line>
                          <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                      </div>
                      <span>How to setup the drive folder correctly!</span>
                      <div class="icons">
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="24"
                          height="24"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          class="feather feather-chevron-down"
                        >
                          <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                      </div>
                    </div>
                  </section>
                </header>
                <div
                  id="headingOne3"
                  class="collapse"
                  aria-labelledby="headingOne3"
                  data-bs-parent="#iconsAccordion"
                >
                  <div class="card-body">
                    <b>Google Folder / File structure</b>
                    <p class="">
                      The folder can not contain any other folder.
                      <br />
                      If any other folders are present they will be skipped.
                    </p>
                    <b>File Format</b>
                    <p class="">
                      Files are images. They must be png or jpg. The name of the
                      file is the keyword. We can have multiple image for the
                      same keyword. In this case files must be named with a ' -
                      {N}' suffix.
                      <br />
                      Valid names: hotel.png, hotel - 1.png, hotel - 2.jpg
                      <br />
                      Not valid names: hotel-1.png, hotel 2.png
                    </p>
                    <b>Permissions</b>
                    <p>
                      In order for the system to have access to the drive
                      folder, read permissions should be granted to the
                      following email address:
                      <i style="white-space: nowrap">{{ email }}</i>
                      <br />
                      The url will be autovalidated when clicking outside of the
                      text input.
                    </p>
                    <b>Categories</b>
                    <p>
                      You can either select one category for the whole folder or
                      upload a "csv" file where you can link different keyword
                      names with different categories.
                      <br />
                      The categories from the csv (if present) will predominate
                      over the one from the select input.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-3 mb-3">
              <form @submit.prevent="getFormValues">
                <div class="add-contact-box">
                  <div class="add-contact-content">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group mb-4">
                          <label
                            >Folder Structure
                            <svg
                              xmlns="http://www.w3.org/2000/svg"
                              width="24"
                              height="24"
                              viewBox="0 0 24 24"
                              fill="none"
                              stroke="currentColor"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              class="feather feather-help-circle"
                              data-v-5522efca=""
                              data-bs-toggle="tooltip"
                              data-bs-html="true"
                              title="This indicates the type of structure of your G. Drive folder. For more info click above"
                            >
                              <circle cx="12" cy="12" r="10"></circle>
                              <path
                                d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"
                              ></path>
                              <line
                                x1="12"
                                y1="17"
                                x2="12.01"
                                y2="17"
                              ></line></svg
                          ></label>

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
                                value="classic"
                                id="rdo1"
                                name="rdoinline"
                                v-model="folderStructure"
                              />
                              <label class="custom-control-label" for="rdo1"
                                >Default</label
                              >
                            </div>
                            <div
                              class="radio-classic radio-primary custom-control d-inline-flex custom-radio me-3"
                            >
                              <input
                                type="radio"
                                class="custom-control-input"
                                value="nested-folders"
                                id="rdo2"
                                name="rdoinline"
                                v-model="folderStructure"
                              />
                              <label class="custom-control-label" for="rdo2"
                                >Nested Folders</label
                              >
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-12">
                        <div class="form-group mb-4">
                          <label>Folder Url </label>
                          <Field
                            name="url"
                            class="form-control is-invalid"
                            v-model="url"
                          >
                            <input
                              :class="{
                                'is-invalid': isUrlValid === false,
                                'is-valid': isUrlValid === true,
                              }"
                              v-on:blur="checkFolderPermissions"
                              v-on:paste="checkFolderPermissions"
                              class="form-control form-control-sm"
                              v-model="url"
                            />
                          </Field>
                          <div class="valid-feedback">Looks good!</div>
                          <div class="invalid-feedback">
                            {{ urlError }}
                          </div>
                          <ErrorMessage class="invalid-feedback" name="url" />
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
                              :closeOnSelect="true"
                            />
                          </Field>
                          <ErrorMessage
                            class="invalid-feedback"
                            name="countries"
                          />
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
                          <ErrorMessage
                            class="invalid-feedback"
                            name="languages"
                          />
                        </div>
                      </div>

                      <div class="col-md-9">
                        <div class="form-group mb-4">
                          <label>Category</label>
                          <Field
                            name="category"
                            type="select"
                            v-model="category"
                            :class="{ 'is-invalid': errors.category }"
                            class="form-select"
                            multiple
                          >
                            <categories-select
                              v-model="category"
                              :multiple="false"
                              :class="{ 'is-invalid': errors.category }"
                              :closeOnSelect="true"
                              :placeholder="'Choose one...'"
                            />
                          </Field>
                          <ErrorMessage
                            class="invalid-feedback"
                            name="category"
                          />
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div
                          class="form-group mb-4 d-flex align-items-center justify-content-between h-100"
                        >
                          <span></span>
                          <span>OR</span>
                          <button
                            class="btn btn-primary mt-2"
                            @click.prevent="$emit('importSite')"
                          >
                            Import Categories from CSV
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <button
                  :disabled="!isValid()"
                  type="submit"
                  class="btn btn-primary"
                >
                  Import Images
                </button>
              </form>
            </div>

            <jobber-list
              ref="JobberListComponent"
              :class-name="'Google\\ImportImagesFromDrive'"
              :hide-button="true"
              @show-logs="showLogs"
            />
            <jobber-logs-modal
              ref="JobberLogsModalComponent"
              :is-show="showLogsModal"
              @close-static-modal="showLogsModal = false"
            />
          </div>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { toRef, ref, onMounted, watch } from "vue";
import { Form, Field, ErrorMessage } from "vee-validate";
import jobberApi from "@/services/api/jobber";
import { initTooltip } from "@/utils/tooltip";
import useBsModal from "@/composables/useBsModal";
import { showMessage, askForConfirmation } from "@/utils/toast";
import googleDriveApi from "@/services/api/google/import-images";
import CountriesSelect from "@/components/form/CountriesSelect.vue";
import LanguagesSelect from "@/components/form/LanguagesSelect.vue";
import TagsSelect from "@/components/form/TagsSelect.vue";
import CategoriesSelect from "@/components/form/CategoriesSelect.vue";
import JobberList from "@/components/jobber/jobberList.vue";
import JobberLogsModal from "@/components/jobber/jobberLogsModal.vue";
import { useCreateImage } from "@/composables/use-image";
import { getFileIdFromUrl } from "@/utils/drive";

const JobberListComponent = ref(null);
const showLogsModal = ref(false);
const JobberLogsModalComponent = ref(null);
const emit = defineEmits(["closeModal", "addImage"]);
const props = defineProps({ isShow: Boolean });
const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);
const languages = ref([]);
const countries = ref([]);
const tags = ref([]);
const url = ref();
const urlError = ref();
const category = ref();
const csvCategories = ref(null);
const folderStructure = ref("classic");

let email = ref("");

let isUrlValid = ref();

const { errors } = useCreateImage((res) => {
  showMessage(res.message);
});

const addTag = (newTag) => {
  tags.value.push(newTag);
};

const isValid = () => {
  if (
    languages.value.length === 0 ||
    countries.value.length === 0 ||
    tags.value.length === 0 ||
    isUrlValid.value !== true
  )
    return false;
  return true;
};

const updateCategories = (categories) => {
  csvCategories.value = categories;
};

const resetFormData = () => {
  countries.value = [];
  languages.value = [];
  tags.value = [];
  category.value = null;
  url.value = "";
  isUrlValid.value = null;
};

const getClientEmail = async () => {
  let res = await googleDriveApi.getClientEmail();
  email.value = res.data;
};

const checkFolderPermissions = async (e) => {
  if (!e.target.value.length) return;
  let res = await googleDriveApi.checkFolderPermissions({
    id: getFileIdFromUrl(e.target.value),
  });
  if (res.data === true) isUrlValid.value = true;
  else {
    urlError.value = res.message;
    isUrlValid.value = false;
  }
};

const getFormValues = async (event) => {
  const payload = {
    args: {
      countries: countries.value,
      languages: languages.value,
      folderUrl: url.value,
      tags: tags.value,
      category: category.value,
      csvCategories: csvCategories.value,
      folderStructure: folderStructure.value,
    },
    class: "Google\\ImportImagesFromDrive",
    description: "Test description",
  };

  const response = await jobberApi.createJobber(payload);

  if (response.success) {
    showMessage("Jobber created successfully");
    emit("JobberCreated");
    updateJobberList();
    resetFormData();
  }
};

const showLogs = (id) => {
  showLogsModal.value = true;
  JobberLogsModalComponent.value.getLogsByJobberId(id);
};

const updateJobberList = () => {
  JobberListComponent.value.loadItems();
};

onMounted(() => {
  getClientEmail();
  initTooltip();
});

defineExpose({ updateCategories, updateJobberList });
</script>
