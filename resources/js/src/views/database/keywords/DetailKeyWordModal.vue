<template>
  <div
    ref="modalRef"
    id="keywordDetailModal"
    class="modal fade"
    aria-labelledby="keywordDetailModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-md modal-dialog-centered">
      <form @submit="onSubmit">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Keyword Detail</h5>
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
                    <p>
                      <strong>Id:</strong>
                      <span class="badge badge-primary py-1 px-2">{{
                        keyword.id
                      }}</span>
                    </p>
                    <p><strong>Keyword:</strong> {{ keyword.keyword }}</p>
                    <p>
                      <strong>Category:</strong>
                      {{ keyword.category ? keyword.category.name : "-" }}
                    </p>
                    <p>
                      <strong>Translation:</strong>
                      {{ keyword.english_translation }}
                    </p>
                    <p><strong>Tags:</strong> {{ keyword.tags.toString() }}</p>
                  </div>
                </div>
                <div class="mt-4">
                  <ul class="nav nav-tabs" id="keywordTab" role="tablist">
                    <li class="nav-item" role="presentation">
                      <button
                        class="nav-link active"
                        id="images"
                        data-bs-toggle="tab"
                        data-bs-target="#images-content"
                        type="button"
                        role="tab"
                        aria-controls="images-content"
                        aria-selected="false"
                      >
                        Images
                      </button>
                    </li>
                  </ul>

                  <div
                    class="tab-content mt-2 p-2 w-100"
                    id="keywordTabContent"
                  >
                    <div
                      class="tab-pane fade show active"
                      id="images-content"
                      role="tabpanel"
                      aria-labelledby="images"
                    >
                      <div
                        class="d-flex justify-content-end align-items-center gap-2 mb-4"
                      >
                        <strong>Associations: </strong>
                        <div class="custom-dropdown dropdown btn-group">
                          <div
                            class="btn-group"
                            href="#"
                            role="button"
                            id="accotiations"
                            data-bs-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                          >
                            <button type="button" class="btn btn-primary plus">
                              +
                            </button>
                            <div role="group" class="btn-group">
                              <div
                                class="dropdown b-dropdown custom-dropdown show btn-group"
                              >
                                <a
                                  class="btn dropdown-toggle btn-primary toggle"
                                >
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
                                    data-v-c66cbe98=""
                                  >
                                    <polyline
                                      points="6 9 12 15 18 9"
                                    ></polyline>
                                  </svg>
                                </a>
                              </div>
                            </div>
                          </div>
                          <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                              <a
                                href="javascript:void(0);"
                                class="dropdown-item"
                                @click="
                                  emit('addAssocImage', {
                                    keywordId: keyword.id,
                                    fromUnsplash: false,
                                  })
                                "
                                >From Images</a
                              >
                            </li>
                            <li>
                              <a
                                href="javascript:void(0);"
                                class="dropdown-item"
                                @click="
                                  emit('addAssocImage', {
                                    keywordId: keyword.id,
                                    fromUnsplash: true,
                                    keyword: keyword.keyword,
                                  })
                                "
                                >From Unsplash</a
                              >
                            </li>
                            <li>
                              <a
                                href="javascript:void(0);"
                                class="dropdown-item"
                                @click="emit('uploadImages', keyword.id)"
                                >Upload New Image</a
                              >
                            </li>
                          </ul>
                        </div>
                        <button
                          class="btn btn-primary"
                          :class="{ disabled: !selectedImages.length }"
                          @click.prevent="removeAssociateImages"
                        >
                          -
                        </button>
                      </div>

                      <label
                        v-for="(item, index) in keyword.images"
                        :key="index"
                        class="brick tile-picker m-2"
                        :class="{ active: isSelectImage(item.id) }"
                        @click.prevent="selectImage(item.id)"
                      >
                        <input
                          type="checkbox"
                          :checked="isSelectImage(item.id)"
                        />
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
      </form>
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
import { toRef, ref, onMounted } from "vue";
import useBsModal from "@/composables/useBsModal";
import feather from "feather-icons";
import keywordApi from "@/services/api/database/keywords";
import { showMessage } from "@/utils/toast";

const selectedImages = ref([]);

const keyword = ref({
  id: null,
  keyword: "",
  english_translation: "",
  keywords: "",
  images: [],
  tags: [],
});

const emit = defineEmits([
  "closeModal",
  "keywordUpdated",
  "uploadImages",
  "addAssocImage",
]);
const props = defineProps({ isShow: Boolean });

const setKeywordData = (data) => {
  keyword.value = JSON.parse(JSON.stringify(data));
  keyword.value.tags = data.tags.map((tag) => {
    return tag.value;
  });
};

const selectImage = (id) => {
  if (selectedImages.value.includes(id)) {
    selectedImages.value = selectedImages.value.filter((image) => image !== id);
  } else {
    selectedImages.value.push(id);
  }
};

const isSelectImage = (id) => {
  return selectedImages.value.includes(id);
};

const removeAssociateImages = () => {
  const params = {
    id: keyword.value.id,
    image_ids: selectedImages.value,
  };

  keywordApi.removeAssociateImages(params).then((response) => {
    showMessage(response.message);
    keyword.value.images = keyword.value.images.filter(
      (image) => !selectedImages.value.includes(image.id)
    );
    selectedImages.value = [];
    emit("keywordUpdated");
  });
};

const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);
defineExpose({ setKeywordData });

onMounted(() => {
  feather.replace();
});
</script>
