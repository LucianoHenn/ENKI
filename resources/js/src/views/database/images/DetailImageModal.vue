<template>
  <div
    ref="modalRef"
    id="detailImageModal"
    class="modal fade"
    aria-labelledby="detailImageModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-md modal-dialog-centered">
      <div class="modal-content mailbox-popup">
        <div class="modal-header">
          <h5 class="modal-title">Image Overview</h5>
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
                <div class="col-md-8">
                  <img
                    :src="image.url"
                    class="img-fluid w-100 rounded border border-secondary"
                  />
                </div>
                <div class="col-md-4">
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                      Name: {{ image.image_name }}
                    </li>
                    <li class="list-group-item">Id: {{ image.id }}</li>
                    <li class="list-group-item">
                      Width: {{ `${image.width} px` }}
                    </li>
                    <li class="list-group-item">
                      Height: {{ `${image.height} px` }}
                    </li>
                    <li class="list-group-item">
                      Size: {{ `${Math.round(image.size / 1024)} Kbyte` }}
                    </li>
                    <li class="list-group-item">
                      Created at: {{ image.created_at }}
                    </li>
                    <li class="list-group-item">
                      Keywords: {{ image.keywordsCount }}
                    </li>
                    <li class="list-group-item">
                      Tags: {{ image.tags.toString().replace(/,/g, ", ") }}
                    </li>
                  </ul>
                </div>
              </div>

              <div class="table-responsive mt-4">
                <v-client-table
                  :data="keywordItems"
                  :columns="columns"
                  :options="tableOption"
                >
                  <template #actions="props">
                    <div class="actions text-center">
                      <span
                        class="danger"
                        role="button"
                        @click="removeKeyword(props.row)"
                      >
                        <svg
                          width="24"
                          height="24"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          class="feather feather-x-circle table-cancel"
                        >
                          <circle cx="12" cy="12" r="10"></circle>
                          <line x1="15" y1="9" x2="9" y2="15"></line>
                          <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                      </span>
                    </div>
                  </template>
                  <template #language="props">
                    {{
                      `${props.row.language.name} (${props.row.language.code})`
                    }}
                  </template>
                  <template #country="props">
                    {{ props.row.country.name }}
                    <span class="text-uppercase"
                      >({{ props.row.country.code }})</span
                    ></template
                  >
                </v-client-table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.modal {
  &.show {
    .modal-dialog {
      @media screen and (min-width: 768px) {
        min-width: 780px;
      }
    }
  }

  &-content {
    @media screen and (min-width: 768px) {
      min-width: 780px;
    }
  }
}
</style>

<script setup>
import { toRef, ref } from "vue";
import useBsModal from "@/composables/useBsModal";
import imageApi from "@/services/api/images";
import { showMessage, askForConfirmation } from "@/utils/toast";

const image = ref({
  id: null,
  url: "",
  info: "",
  keywords: "",
  translated_keywords: "",
  countries: [],
  tags: [],
  languages: [],
  image: "",
});

const emit = defineEmits(["closeModal", "deleteKeyword"]);
const props = defineProps({ isShow: Boolean });

const columns = ref(["id", "keyword", "country", "language", "actions"]);
const keywordItems = ref([]);
const tableOption = ref({
  perPage: 10,
  perPageValues: [5, 10, 20, 50],
  skin: "table table-striped",
  columnsClasses: { actions: "actions text-center" },
  sortable: [],
  pagination: {
    show: true,
    chunk: 10,
    align: "center",
    nav: "scroll",
  },
  filterable: false,
  texts: {
    count: "Showing {from} to {to} of {count}",
    filter: false,
    hidePerPageSelect: false,
  },
  resizableColumns: false,
});

const setImageData = (data) => {
  image.value = JSON.parse(JSON.stringify(data));
  image.value.keywordsCount = data.keywords.length;

  image.value.tags = data.tags.map((tag) => {
    return tag.value;
  });

  keywordItems.value = data.keywords;
};

const removeKeyword = async (keyword) => {
  const confirmation = await askForConfirmation(
    "Are you sure you want to remove this keyword?"
  );
  if (!confirmation.isConfirmed) return;

  imageApi.deleteKeyword({
    id: image.value.id,
    keywordId: keyword.id,
  });

  emit("deleteKeyword");
  image.value.keywords = image.value.keywords.filter((item) => {
    return item.id !== keyword.id;
  });
  showMessage("Keyword removed successfully", "success");
  image.value.keywordsCount = image.value.keywords.length;
};

const { modalRef } = useBsModal(toRef(props, "isShow"), emit);
defineExpose({ setImageData });
</script>
