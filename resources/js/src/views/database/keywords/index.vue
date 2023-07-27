<template>
  <div class="layout-px-spacing">
    <div class="row layout-spacing layout-top-spacing">
      <div class="col-sm-12 layout-spacing">
        <div class="panel br-6 p-0">
          <keyword-list
            @edit-keyword="editKeyword"
            @add-keyword="showCreateModal = true"
            @view-keyword="viewKeyword"
            @bulk-edit-keyword="bulkEditKeyword"
            @selected-rows="selectedRows"
            @associate-categories="showImportCategoryModal = true"
            ref="keywordListComponent"
          />

          <create-keyword-modal
            ref="createKeywordModalComponent"
            :is-show="showCreateModal"
            @close-modal="showCreateModal = false"
            @keyword-created="reFreshList"
          />

          <edit-keyword-modal
            ref="keywordEditModalComponent"
            :is-show="showEditModal"
            @close-modal="showEditModal = false"
            @keyword-updated="reFreshList"
          />

          <detail-key-word-modal
            ref="detailKeyWordModalComponent"
            :is-show="showDetailModal"
            @close-modal="showDetailModal = false"
            @keyword-updated="reFreshList"
            @upload-images="uploadImage"
            @add-assoc-image="addAssocImage"
          />

          <assign-category-modal
            ref="assignCategoryComponent"
            :is-show="showAssignCategoryModal"
            @close-modal="showAssignCategoryModal = false"
            @category-assigned="reFreshList"
          />

          <upload-new-image-modal
            ref="uploadNewImageComponent"
            :is-show="showUploadImageModal"
            @close-modal="showUploadImageModal = false"
            @image-uploaded="reFreshList"
          />

          <add-associate-image-modal
            ref="addAssociateImageComponent"
            :is-show="showAddAssociateImageModal"
            :is-update="true"
            @on-submit="updateKeyword"
            @close-modal="showAddAssociateImageModal = false"
            @image-added-to-keyword="reFreshList"
          />

          <bulk-edit-modal
            ref="bulkEditModalComponent"
            :is-show="showBulkEditModal"
            @close-modal="showBulkEditModal = false"
            @keywords-updated="reFreshList"
            @upload-images="uploadImage"
            @add-assoc-image="addAssocImage"
            @assign-category="assignCategory"
          />

          <import-category-modal
            ref="importCategoryModal"
            @close-modal="showImportCategoryModal = false"
            :is-show="showImportCategoryModal"
            :title="'Keyword-Category Association'"
            @import-categories="importCategories"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useMeta } from "@/composables/use-meta";
import keywordApi from "@/services/api/database/keywords";
import KeywordList from "./KeywordList.vue";
import CreateKeywordModal from "./CreateKeywordModal.vue";
import EditKeywordModal from "./EditKeywordModal.vue";
import DetailKeyWordModal from "./DetailKeyWordModal.vue";
import UploadNewImageModal from "./UploadNewImageModal.vue";
import AddAssociateImageModal from "./AddAssociateImageModal.vue";
import BulkEditModal from "./BulkEditModal.vue";
import AssignCategoryModal from "./AssignCategoryModal.vue";
import { showMessage } from "@/utils/toast";
import ImportCategoryModal from "../../../components/modals/ImportCategoryModal.vue";

useMeta({ title: "Keyword List" });

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDetailModal = ref(false);
const showUploadImageModal = ref(false);
const showAssignCategoryModal = ref(false);
const showAddAssociateImageModal = ref(false);
const showBulkEditModal = ref(false);

const createKeywordModalComponent = ref(null);
const keywordEditModalComponent = ref(null);
const keywordListComponent = ref(null);
const detailKeyWordModalComponent = ref(null);
const uploadNewImageComponent = ref(null);
const addAssociateImageComponent = ref(null);
const assignCategoryComponent = ref(null);
const bulkEditModalComponent = ref(null);
const showImportCategoryModal = ref(false);

const ids = ref([]);

const viewKeyword = (keyword) => {
  showDetailModal.value = true;
  detailKeyWordModalComponent.value.setKeywordData(keyword);
};

const editKeyword = (keyword) => {
  showEditModal.value = true;
  keywordEditModalComponent.value.setKeywordData(keyword);
};

const uploadImage = (keywordIds) => {
  showDetailModal.value = false;
  showBulkEditModal.value = false;
  showUploadImageModal.value = true;
  uploadNewImageComponent.value.setKeywordDataId(keywordIds);
};

const addAssocImage = (obj) => {
  showDetailModal.value = false;
  showBulkEditModal.value = false;
  showAddAssociateImageModal.value = true;
  addAssociateImageComponent.value.setUseUnsplashApi(obj.fromUnsplash);
  addAssociateImageComponent.value.setKeywordDataId(obj.keywordId);
  addAssociateImageComponent.value.setDefaultKeywordQuery(obj.keyword);
};

const assignCategory = (keywordIds) => {
  showDetailModal.value = false;
  showBulkEditModal.value = false;
  showAssignCategoryModal.value = true;
  assignCategoryComponent.value.setKeywordDataId(keywordIds);
};

const selectedRows = (selectedId) => {
  ids.value = selectedId;
};

const bulkEditKeyword = () => {
  if (ids.value.length > 0) {
    showBulkEditModal.value = true;
    bulkEditModalComponent.value.setKeywordDataId(ids);
  } else {
    showMessage(
      "Please select at least one keyword to use bulk edit function",
      "error"
    );
  }
};

const importCategories = async (keywordCategoriesObj) => {
  const res = await keywordApi.assignBulkCategoryToKeywords({
    keywordCategoriesObj,
  });
  if (res.success) {
    showMessage("Categories added succesfully!");
    reFreshList();
  } else
    showMessage("Ups, something happened. Please try again later", "error");
};

const reFreshList = () => {
  keywordListComponent.value.loadItems();
};
</script>
