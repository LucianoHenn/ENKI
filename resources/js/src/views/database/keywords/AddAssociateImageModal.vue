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
          <h5 class="modal-title">Associate Images</h5>
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
              <form class="px-3" v-if="!usingUnsplashAPI">
                <div class="input-group mb-2">
                  <span class="input-group-text">Keywords</span>
                  <input
                    type="text"
                    class="form-control"
                    style="flex: 4"
                    placeholder="Keywords to search (comma separated)"
                    v-model="serverParams.keywords"
                  />
                </div>

                <div class="input-group mb-2">
                  <span class="input-group-text">Image Name</span>
                  <input
                    type="text"
                    class="form-control"
                    style="flex: 4"
                    placeholder="Image names (comma separated)"
                    v-model="serverParams.image_names"
                  />
                </div>

                <div class="input-group mb-2 d-flex">
                  <span style="z-index: 1" class="input-group-text">Tags</span>
                  <tags-select
                    v-model="serverParams.tags"
                    class="flex-grow-1"
                  />
                </div>
              </form>

              <div class="px-3 py-2 row" v-if="usingUnsplashAPI">
                <div class="col-12 px-0">
                  <div class="input-group mb-2">
                    <input
                      v-model="serverParams.keywords"
                      type="text"
                      class="form-control"
                      aria-label="Text input with dropdown button"
                      @keyup.enter="loadItems()"
                      placeholder="Search..."
                    />
                    <span class="input-group-text" id="basic-addon2">
                      <svg
                        role="button"
                        @click="loadItems()"
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="feather feather-search"
                      >
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                      </svg>
                    </span>
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-between px-3">
                <button
                  v-if="!usingUnsplashAPI"
                  @click="loadItems"
                  class="btn btn-primary w-25 mt-1 mb-3"
                >
                  SEARCH
                </button>
                <button
                  class="btn btn-primary ms-auto w-25 mt-1 mb-3"
                  @click.prevent="onSubmit"
                  :class="keyword.image_ids.length > 0 ? '' : 'disabled'"
                >
                  <span
                    v-if="isLoading"
                    class="spinner-border text-white me-2 align-self-center loader-sm"
                    >Loading...</span
                  >
                  <span v-if="isLoading">Loading...</span>
                  <span v-else>{{
                    props.isUpdate && keyword.value?.ids
                      ? "Update Keyword"
                      : props.isUpdate
                      ? "Select Images"
                      : "Create Keywords"
                  }}</span>
                </button>
              </div>

              <div class="wall">
                <h4
                  class="mt-5 text-center text-primary"
                  v-if="images.length === 0 && totalRecords === 0"
                >
                  No images were found with that keyword.
                </h4>
                <label
                  v-for="(item, index) in images"
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
              <div class="vgt-wrap__footer vgt-clearfix">
                <div class="footer__row-count vgt-pull-left">
                  <form>
                    <label
                      for="vgt-select-rpp-1042633949806"
                      class="footer__row-count__label"
                      >Rows per page:</label
                    ><select
                      autocomplete="off"
                      name="perPageSelect"
                      class="footer__row-count__select"
                      v-model="serverParams.perPage"
                      @change="loadItems()"
                    >
                      <option value="8">8</option>
                      <option value="12">12</option>
                      <option value="16">16</option>
                      <option value="20">20</option>
                      <option value="48">48</option>
                      <option value="298">All</option>
                    </select>
                  </form>
                </div>
                <div class="footer__navigation vgt-pull-right">
                  <div class="footer__navigation__page-info">
                    <form @submit.prevent="loadItems()">
                      <label class="page-info__label"
                        ><span>page</span
                        ><input
                          class="footer__navigation__page-info__current-entry"
                          v-model="serverParams.page"
                          type="text"
                        /><span
                          >of
                          {{
                            Math.ceil(totalRecords / serverParams.perPage)
                          }}</span
                        ></label
                      >
                    </form>
                  </div>
                  <button
                    type="button"
                    class="footer__navigation__page-btn"
                    @click="
                      updateParams({ page: parseInt(serverParams.page) - 1 })
                    "
                    :class="{ disabled: parseInt(serverParams.page) === 1 }"
                  >
                    <span aria-hidden="true" class="chevron left"></span
                    ><span>Previous</span></button
                  ><button
                    type="button"
                    class="footer__navigation__page-btn"
                    @click="
                      updateParams({ page: parseInt(serverParams.page) + 1 })
                    "
                  >
                    <span>Next</span
                    ><span aria-hidden="true" class="chevron right"></span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
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
import { ref, toRef, onMounted } from "vue";
import { showMessage } from "@/utils/toast";
import imageApi from "@/services/api/images";
import unsplashApi from "@/services/api/unsplash/photos";
import keywordApi from "@/services/api/database/keywords";
import useBsModal from "@/composables/useBsModal";
import ExpandableImage from "./ExpandableImage";
import TagsSelect from "@/components/form/TagsSelect.vue";

const usingUnsplashAPI = ref(false);
const componentKey = ref(Math.round(Math.random() * 1000));
const keyword = ref({
  ids: [],
  image_ids: [],
  fromUnsplash: false,
  unsplashName: "",
  image_urls: [],
});

const images = ref([]);
const selectedImages = ref([]);
let isLoading = ref(false);
let totalRecords = ref(null);

let serverParams = ref({
  keywords: "",
  page: 1,
  perPage: 12,
  maxWidth: "",
  minWidth: "",
  maxHeight: "",
  minHeight: "",
  numberOfKeywords: null,
  image_names: "",
  orientation: "squarish",
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
  keyword.value.image_ids = [];
  keyword.value.image_urls = [];
};

const setDefaultKeywordQuery = (keyword) => {
  serverParams.value.keywords = keyword;
  loadItems();
};

const emit = defineEmits([
  "closeModal",
  "imageAddedToKeyword",
  "onSubmit",
  "imageAddedToNewKeyword",
]);
const props = defineProps({ isShow: Boolean, isUpdate: Boolean });
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

const setKeywordData = (data) => {
  keyword.value.keywords = data.keywords;
  keyword.value.category = data.category;
  keyword.value.countries = data.countries;
  keyword.value.language = data.languages;
  keyword.value.tags = data.tags;
  keyword.value.fromUnsplash = data.fromUnsplash;
};

const loadItems = async () => {
  isLoading.value = true;
  try {
    let res;
    if (usingUnsplashAPI.value) {
      res = await unsplashApi.searchPhotos(serverParams.value);
      totalRecords.value = res.data.length;
    } else {
      res = await imageApi.getImages(serverParams.value);
      totalRecords.value = res.meta.total;
    }
    images.value = res.data;

    isLoading.value = false;
  } catch (error) {
    showMessage(error.message, "error");
    console.log(error);
  }
};

const selectImage = (item) => {
  if (keyword.value.image_ids.includes(item.id)) {
    keyword.value.image_ids = keyword.value.image_ids.filter(
      (id) => id !== item.id
    );
    keyword.value.image_urls = keyword.value.image_urls.filter(
      (url) => url !== item.url
    );
  } else {
    keyword.value.image_ids.push(item.id);
    keyword.value.image_urls.push(item.url);
  }
};

const isSelectImage = (item) => {
  return keyword.value.image_ids.includes(item.id);
};

const setUseUnsplashApi = (bool) => {
  usingUnsplashAPI.value = bool;
  images.value = [];
  totalRecords.value = null;
  keyword.value.image_ids = [];
  keyword.value.urls = [];
  serverParams.value.keywords = "";
  componentKey.value = !componentKey.value;
};

const onSubmit = () => {
  keyword.value.unsplashName = serverParams.value.keywords;
  if (props.isUpdate && keyword.value.ids) updateKeyword();
  else if (props.isUpdate) {
    emit("imageAddedToNewKeyword", keyword.value);
    closeModal();
  } else createKeywords();
};

const updateKeyword = async () => {
  isLoading.value = true;
  keyword.value.fromUnsplash = usingUnsplashAPI.value;
  const res = await keywordApi.addAssociateImages(keyword.value);
  if (res) {
    showMessage(res.message);
    isLoading.value = false;
    emit("imageAddedToKeyword", res.data);
    closeModal();
  }

  if (res.error) {
    showMessage(res.message, "error");
    isLoading.value = false;
  }
};

const createKeywords = async () => {
  isLoading.value = true;
  try {
    const res = await keywordApi.createKeyword(keyword.value);
    showMessage(res.message);
    isLoading.value = false;
    closeModal();
  } catch (e) {
    showMessage(e.message, "error");
  }
  isLoading.value = false;
};

defineExpose({
  setKeywordDataId,
  setUseUnsplashApi,
  setKeywordData,
  setDefaultKeywordQuery,
});
</script>
