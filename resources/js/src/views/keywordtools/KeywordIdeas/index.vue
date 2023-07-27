<template>
  <div class="layout-px-spacing">
    <div class="row layout-spacing layout-top-spacing">
      <div class="col-sm-12 layout-spacing">
        <div class="panel br-6 p-0">
          <keyword-ideas-list
            @add-keywords="addKeywords"
            ref="keywordListComponent"
          />
          <add-keyword-modal
            ref="addKeywordModalComponent"
            :is-show="showAddKeywordModal"
            @close-modal="showAddKeywordModal = false"
            @upload-images="uploadImages"
            @add-assoc-images="addAssocImages"
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
            :is-update="false"
            @close-modal="showAddAssociateImageModal = false"
            @image-added-to-keyword="reFreshList"
            @on-submit="createKeywords"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useMeta } from "@/composables/use-meta";
import KeywordIdeasList from "./KeywordIdeasList.vue";
import AddKeywordModal from "./AddKeywordModal.vue";
import UploadNewImageModal from "../../database/keywords/UploadNewImageModal.vue";
import AddAssociateImageModal from "../../database/keywords/AddAssociateImageModal.vue";
import keywordApi from "@/services/api/database/keywords";
import { showMessage } from "@/utils/toast";

const addKeywordModalComponent = ref(null);
const showUploadImageModal = ref(false);
const showAddAssociateImageModal = ref(false);
const addAssociateImageComponent = ref(null);
const uploadNewImageComponent = ref(null);

const showAddKeywordModal = ref(false);
const addKeywords = (selectedKeywordIdeas) => {
  showAddKeywordModal.value = true;
  addKeywordModalComponent.value.setFormData(selectedKeywordIdeas);
};

const uploadImages = (data) => {
  showAddKeywordModal.value = false;
  showUploadImageModal.value = true;
  uploadNewImageComponent.value.setKeywordData(data);
};

const addAssocImages = (obj) => {
  showAddKeywordModal.value = false;
  showAddAssociateImageModal.value = true;
  addAssociateImageComponent.value.setUseUnsplashApi(obj.fromUnsplash);
  addAssociateImageComponent.value.setKeywordData(obj);
};

useMeta({ title: "Keyword Ideas List" });
</script>
