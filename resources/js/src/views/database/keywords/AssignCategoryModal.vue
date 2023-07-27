<template>
  <div
    ref="modalRef"
    id="assignCategoryModal"
    class="modal fade"
    aria-labelledby="assignCategoryModalLabel"
    aria-hidden="true"
    style="background: #50505021"
  >
    <div class="modal-dialog modal-md modal-dialog-centered">
      <form enctype="multipart/form-data">
        <div class="modal-content mailbox-popup">
          <div class="modal-header">
            <h5 class="modal-title">Assign New Category</h5>
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
                      <label>Categories</label>
                      <Field
                        name="categories"
                        type="select"
                        v-model="categories"
                        class="form-select"
                        multiple
                      >
                        <CategoriesSelect
                          v-model="categories"
                          :closeOnSelect="true"
                        />
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
              data-dismiss="modal"
              data-bs-dismiss="modal"
            >
              Discard
            </button>
            <button
              class="btn btn-primary"
              :class="categories.length === 0 ? 'disabled' : ''"
              @click.prevent="updateKeyword"
            >
              <span
                v-if="isLoading"
                class="
                  spinner-border
                  text-white
                  me-2
                  align-self-center
                  loader-sm
                "
                >Loading...</span
              >
              <span v-if="isLoading">Loading...</span>
              <span v-else>Update Keyword</span>
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
import { Field } from "vee-validate";
import { showMessage } from "@/utils/toast";
import useBsModal from "@/composables/useBsModal";
import CategoriesSelect from "@/components/form/CategoriesSelect.vue";
import keywordApi from "@/services/api/database/keywords";

const keyword = ref({ ids: [] });
let categories = ref([]);
let isLoading = ref(false);

const emit = defineEmits(["closeModal", "categoryAssigned"]);
const props = defineProps({ isShow: Boolean });

const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);

const updateKeyword = async () => {
  isLoading.value = true;
  try {
    const res = await keywordApi.assignCategoryToKeywords({
      ids: keyword.value.ids,
      category: categories.value,
    });
    isLoading.value = false;
    showMessage(res.message);
    isLoading.value = false;
    keyword.value = [];
    categories.value = [];
    emit("categoryAssigned", res.data);
    closeModal();
  } catch (error) {
    showMessage(error.message, "error");
    isLoading.value = false;
  }
};

const setKeywordDataId = (keywordIds) => {
  keyword.value.ids = keywordIds;
};
defineExpose({ setKeywordDataId });
</script>
