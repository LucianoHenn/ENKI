<template>
  <div class="layout-px-spacing">
    <div class="row layout-spacing layout-top-spacing">
      <div class="col-lg-12">
        <div class="col-sm-12 layout-spacing">
          <div class="panel br-6 p-0">
            <option-list
              ref="optionListComponent"
              @edit-option="editOption"
              @add-option="showOptionModal = true"
            />

            <create-option-modal
              ref="optionCreateModal"
              :is-show="showOptionModal"
              @close-modal="showOptionModal = false"
              @option-created="updateOptionList"
            />

            <edit-option-modal
              ref="optionEditModalComponent"
              @close-modal="showEditModal = false"
              :is-show="showEditModal"
              @option-updated="updateOptionList"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useMeta } from "@/composables/use-meta";
import OptionList from "./OptionList.vue";
import CreateOptionModal from "./CreateOptionModal.vue";
import EditOptionModal from "./EditOptionModal.vue";

const showOptionModal = ref(false);
const optionEditModalComponent = ref(null);
const showEditModal = ref(false);
const optionListComponent = ref(null);
const optionCreateModal = ref(null);

useMeta({ title: "Options" });

const editOption = (option) => {
  showEditModal.value = true;
  optionEditModalComponent.value.setOptionData(option);
};

const updateOptionList = () => {
  optionListComponent.value.loadItems();
};
</script>
