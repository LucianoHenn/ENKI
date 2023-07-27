<template>
  <div
    ref="modalRef"
    id="addAssociateImageModal"
    class="modal fade"
    aria-labelledby="addAssociateImageModalLabel"
    aria-hidden="true"
    style="background: #50505021"
  >
    <div class="modal-dialog modal-md modal-dialog-centered associate-popup">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Select Associate Images</h5>
          <button
            type="button"
            data-dismiss="modal"
            data-bs-dismiss="modal"
            aria-label="Close"
            class="btn-close"
          ></button>
        </div>
        <div class="modal-body">
          <div class="add-associate-box">
            <div class="add-associate-content">
              <div class="px-3 py-2 row">
                <button
                  class="btn btn-primary mb-3 ms-auto w-25 mt-1"
                  @click.prevent="updateKeyword"
                  :class="
                    keyword.images.length === 0 && isLoading ? 'disabled' : ''
                  "
                >
                  <span
                    v-if="isLoading"
                    class="spinner-border text-white me-2 align-self-center loader-sm"
                    >Loading...</span
                  >
                  <span v-if="isLoading">Loading...</span>
                  <span v-else>Select Images</span>
                </button>
              </div>
              <div class="wall">
                <label
                  v-for="(item, index) in rows"
                  :key="index"
                  class="brick tile-picker m-2"
                  :class="{ active: isSelectImage(item) }"
                  @click.prevent="selectImage(item)"
                >
                  <input type="checkbox" :checked="isSelectImage(item)" />
                  <img
                    v-if="item.loaded === undefined"
                    src="https://media1.giphy.com/media/hWZBZjMMuMl7sWe0x8/giphy.gif?cid=ecf05e47ru66kw9kub5kn77kvw5kkp2mju617h2x86ec77on&rid=giphy.gif&ct=g"
                    class="tile-img"
                  />
                  <img
                    class="tile-img"
                    :src="item.url"
                    v-show="item.loaded !== undefined"
                    @load="item.loaded = true"
                  />
                  <i class="tile-checked"></i>
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.associate-popup {
  form {
    min-width: 500px;
  }
}
.wall {
  padding-left: 50px;
}
.brick {
  width: 200px;
  height: 150px;
  background: #f2f2f2;
}
.tile-picker {
  position: relative;
  cursor: pointer;
  background-color: #eaeaea;
  background-position: center center;
  background-size: cover;
  box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1);
  outline: 2px solid #fff;
  outline-offset: -3px;
  border: 1px solid #bbb;
}

.active.tile-picker {
  border-color: #aaa;
}

.active.tile-picker {
  outline-color: #33a7d2;
}

.tile-picker input[type="checkbox"] {
  opacity: 0;
  position: absolute;
  left: -80px;
}

.tile-checked {
  display: block;
  font-style: normal;
  width: 20px;
  height: 20px;
  position: absolute;
  top: -2px;
  right: -4px;
}

.tile-checked:after {
  content: "\2713";
  display: block;
  line-height: 18px;
  width: 18px;
  height: 18px;

  background-color: #1481b8;
  color: #fff;
  border-radius: 2px;
  font-size: 13px;
  text-align: center;
  font-weight: bold;

  opacity: 0;
  transition: opacity 0.34s ease-in-out;
}

input[type="checkbox"]:checked ~ .tile-checked:after {
  opacity: 1;
}

img {
  display: block;
  max-width: 100%;
  height: auto;
  border: 0;
  outline: 0;
  border-style: none;
  width: 200px;
  height: 148px;
  object-fit: cover;
}

.loading {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
}
</style>

<script setup>
import { ref, toRef, onMounted, defineExpose } from "vue";
import { showMessage } from "@/utils/toast";
import imageApi from "@/services/api/images";
import keywordApi from "@/services/api/database/keywords";
import useBsModal from "@/composables/useBsModal";
const keyword = ref({
  ids: [],
  images: [],
  defaultSelectedImages: [],
});

let rows = ref([]);
let isLoading = ref(false);
let totalRecords = ref(0);
let serverParams = ref({
  currentPerPage: 10,
  page: 1,
  perPage: 10,
});

const columns = ref([
  {
    label: "ID",
    field: "id",
  },
  {
    label: "Preview",
    field: "url",
  },
  {
    label: "Actions",
    field: "actions",
    thClass: "text-center",
  },
]);

const setKeywordDataId = (keywordIds) => {
  keyword.value.ids = keywordIds;
};
const setSelectedImages = (images) => {
  keyword.value.defaultSelectedImages = images;
};

const emit = defineEmits(["closeModal", "addSelectedImagesToKeyword"]);
const props = defineProps({ isShow: Boolean });
const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);

const onPageChange = (params) => {
  updateParams({ page: params.currentPage });
};

const onPerPageChange = (params) => {
  updateParams({ perPage: params.currentPerPage });
};

const updateParams = (params) => {
  serverParams.value = Object.assign({}, serverParams.value, params);
  loadItems();
};

const loadItems = async () => {
  isLoading.value = true;
  try {
    const res = await keywordApi.getAssociateImages({
      id: keyword.value.ids,
    });
    // remove default selected images from all images
    const images = res.images.filter(
      (image) =>
        !keyword.value.defaultSelectedImages.find(
          (selectedImage) => selectedImage === image.id
        )
    );

    rows.value = images;
    // totalRecords.value = res.meta.total;
    isLoading.value = false;

    // check if image is already selected
    // then push to keyword.images
  } catch (error) {
    showMessage(error.message, "error");
    console.log(error);
  }
};

const selectImage = (row) => {
  if (keyword.value.images.includes(row)) {
    keyword.value.images = keyword.value.images.filter(
      (item) => item.id !== row.id
    );
  } else {
    keyword.value.images.push(row);
  }
};

const isSelectImage = (row) => {
  return keyword.value.images.includes(row);
};

const updateKeyword = async () => {
  emit("addSelectedImagesToKeyword", keyword.value.images);
  keyword.value.images = [];
  closeModal();
};

defineExpose({ setKeywordDataId, loadItems, setSelectedImages });
</script>
