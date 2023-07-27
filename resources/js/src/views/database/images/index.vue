<template>
  <div class="layout-px-spacing">
    <div class="row layout-spacing layout-top-spacing">
      <div class="col-sm-12 layout-spacing">
        <div class="panel br-6 p-0">
          <div>
            <image-list
              @edit-image="editImage"
              @import-from-drive="handleOpenDriveModal"
              @add-image="showCreateModal = true"
              @preview-image="detailModal"
              @add-keyword="addKeyword"
              ref="imageListComponent"
            />
          </div>
          <create-image-modal
            ref="createImageModalComponent"
            :is-show="showCreateModal"
            @close-modal="showCreateModal = false"
            @image-created="reFreshList"
          />
          <edit-image-modal
            ref="imageEditModalComponent"
            :is-show="showEditModal"
            @close-modal="showEditModal = false"
            @image-updated="reFreshList"
          />
          <detail-image-modal
            ref="imageDetailModalComponent"
            :is-show="showDetailModal"
            @delete-keyword="reFreshList"
            @close-modal="showDetailModal = false"
          />
          <add-keyword-modal
            ref="addKeywordImageModalComponent"
            :is-show="showAddKeywordModal"
            @close-modal="showAddKeywordModal = false"
            @keyword-added="reFreshList"
          />
          <import-from-drive-modal
            ref="importFromDriveModalComponent"
            :is-show="showImportFromDriveModal"
            @close-modal="showImportFromDriveModal = false"
            @add-image="showCreateModal = true"
            @import-site="showImportCategoryModal = true"
          />

          <import-category-modal
            ref="importCategoryModal"
            @close-modal="showImportCategoryModal = false"
            :is-show="showImportCategoryModal"
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
import { showMessage } from "@/utils/toast";
import ImageList from "./ImageList.vue";
import CreateImageModal from "./CreateImageModal.vue";
import ImportFromDriveModal from "./ImportFromDriveModal.vue";
import EditImageModal from "./EditImageModal.vue";
import DetailImageModal from "./DetailImageModal.vue";
import AddKeywordModal from "./AddKeywordModal.vue";
import ImportCategoryModal from "../../../components/modals/ImportCategoryModal.vue";

useMeta({ title: "Images List" });

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDetailModal = ref(false);
const showAddKeywordModal = ref(false);
const showImportFromDriveModal = ref(false);
const showImportCategoryModal = ref(false);
const createImageModalComponent = ref(null);
const imageEditModalComponent = ref(null);
const imageListComponent = ref(null);
const importFromDriveModalComponent = ref(null);
const imageDetailModalComponent = ref(null);
const addKeywordImageModalComponent = ref(null);

const editImage = (image) => {
  showEditModal.value = true;
  imageEditModalComponent.value.setImageData(image);
};

const importCategories = (data) => {
  showMessage("Categories added succesfully!");
  importFromDriveModalComponent.value.updateCategories(data);
};

const detailModal = (image) => {
  showDetailModal.value = true;
  imageDetailModalComponent.value.setImageData(image);
};

const handleOpenDriveModal = () => {
  showImportFromDriveModal.value = true;
  importFromDriveModalComponent.value.updateJobberList();
};

const addKeyword = (image) => {
  showAddKeywordModal.value = true;
  addKeywordImageModalComponent.value.setFormData(image);
};

const reFreshList = () => {
  imageListComponent.value.loadItems();
};
</script>
