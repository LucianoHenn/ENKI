<template>
  <div class="px-3 py-2 d-flex gap-2 flex-row-reverse">
    <button class="btn btn-primary" @click="$emit('addImage')">
      Add Image
    </button>

    <button class="btn btn-primary" @click="$emit('importFromDrive')">
      Import from G. Drive
    </button>
  </div>

  <form class="p-3">
    <div class="row mb-3">
      <div class="col">
        <label class="form-label">Keywords</label>
        <div class="input-group">
          <input
            type="text"
            class="form-control"
            placeholder="Keywords (comma separated)"
            v-model="serverParams.keywords"
          />
          <span class="input-group-text">Num</span>
          <input
            type="number"
            class="form-control"
            min="0"
            placeholder="#"
            v-model="serverParams.numberOfKeywords"
            style="max-width: 5rem"
          />
        </div>
      </div>
      <div class="col">
        <label class="form-label">Image Dimensions (px)</label>
        <div class="row g-2">
          <div class="col">
            <input
              type="text"
              class="form-control"
              placeholder="Min height"
              v-model="serverParams.minHeight"
            />
          </div>
          <div class="col">
            <input
              type="text"
              class="form-control"
              placeholder="Max height"
              v-model="serverParams.maxHeight"
            />
          </div>
          <div class="col">
            <input
              type="text"
              class="form-control"
              placeholder="Min width"
              v-model="serverParams.minWidth"
            />
          </div>
          <div class="col">
            <input
              type="text"
              class="form-control"
              placeholder="Max width"
              v-model="serverParams.maxWidth"
            />
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <label class="form-label">Image Name</label>
        <input
          type="text"
          class="form-control"
          placeholder="Image names (comma separated)"
          v-model="serverParams.image_names"
        />
      </div>
      <div class="col">
        <label class="form-label">Tags</label>
        <tags-select v-model="serverParams.tags" />
      </div>
      <div class="col-auto d-flex align-items-end">
        <button type="button" class="btn btn-primary w-100" @click="loadItems">
          SEARCH
        </button>
      </div>
    </div>
  </form>

  <div class="row text-center text-lg-start px-3">
    <div
      class="col-lg-3 col-md-4 col-6"
      v-for="image in rows"
      v-bind:key="image.id"
    >
      <div class="content mb-4">
        <div class="content-overlay"></div>
        <img
          v-if="image.loaded === undefined"
          src="https://media1.giphy.com/media/hWZBZjMMuMl7sWe0x8/giphy.gif?cid=ecf05e47ru66kw9kub5kn77kvw5kkp2mju617h2x86ec77on&rid=giphy.gif&ct=g"
          class="content-image"
          style="width: 400px; height: 300px; object-fit: cover"
        />
        <img
          v-show="image.loaded !== undefined"
          @load="image.loaded = true"
          class="content-image"
          :src="image.url"
          alt=""
          style="width: 400px; height: 300px; object-fit: cover"
        />
        <div class="content-details fadeIn-top">
          <div class="d-flex justify-content-around">
            <svg
              @click="$emit('previewImage', image)"
              role="button"
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="feather feather-eye"
            >
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>

            <svg
              @click="$emit('addKeyword', image)"
              role="button"
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="feather feather-plus-circle"
            >
              <circle cx="12" cy="12" r="10" />
              <line x1="12" y1="8" x2="12" y2="16" />
              <line x1="8" y1="12" x2="16" y2="12" />
            </svg>

            <svg
              @click="$emit('editImage', image)"
              role="button"
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="feather feather-edit-2"
            >
              <path
                d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"
              ></path>
            </svg>

            <svg
              @click="deleteImage(image)"
              role="button"
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              class="feather feather-trash-2"
            >
              <polyline points="3 6 5 6 21 6"></polyline>
              <path
                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
              ></path>
              <line x1="10" y1="11" x2="10" y2="17"></line>
              <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
          </div>
        </div>
      </div>
    </div>
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
              >of {{ Math.ceil(totalRecords / serverParams.perPage) }}</span
            ></label
          >
        </form>
      </div>
      <button
        type="button"
        class="footer__navigation__page-btn"
        @click="updateParams({ page: parseInt(serverParams.page) - 1 })"
        :class="{ disabled: parseInt(serverParams.page) === 1 }"
      >
        <span aria-hidden="true" class="chevron left"></span
        ><span>Previous</span></button
      ><button
        type="button"
        class="footer__navigation__page-btn"
        @click="updateParams({ page: parseInt(serverParams.page) + 1 })"
      >
        <span>Next</span><span aria-hidden="true" class="chevron right"></span>
      </button>
    </div>
  </div>
</template>

<style>
.content {
  position: relative;
  margin: auto;
  overflow: hidden;
}

.content .content-overlay {
  background: rgba(0, 0, 0, 0.7);
  position: absolute;
  height: 99%;
  width: 100%;
  left: 0;
  top: 0;
  bottom: 0;
  right: 0;
  opacity: 0;
  -webkit-transition: all 0.4s ease-in-out 0s;
  -moz-transition: all 0.4s ease-in-out 0s;
  transition: all 0.4s ease-in-out 0s;
}

.content:hover .content-overlay {
  opacity: 1;
}

.content-image {
  width: 100%;
}

.content-details {
  position: absolute;
  text-align: center;
  padding-left: 1em;
  padding-right: 1em;
  width: 100%;
  top: 50%;
  left: 50%;
  opacity: 0;
  -webkit-transform: translate(-50%, -50%);
  -moz-transform: translate(-50%, -50%);
  transform: translate(-50%, -50%);
  -webkit-transition: all 0.3s ease-in-out 0s;
  -moz-transition: all 0.3s ease-in-out 0s;
  transition: all 0.3s ease-in-out 0s;
}

.content:hover .content-details {
  top: 50%;
  left: 50%;
  opacity: 1;
}

.content-details h3 {
  color: #fff;
  font-weight: 500;
  letter-spacing: 0.15em;
  margin-bottom: 0.5em;
  text-transform: uppercase;
}

.content-details svg {
  color: #fff;
  font-size: 0.8em;
}

.fadeIn-bottom {
  top: 80%;
}

.fadeIn-top {
  top: 20%;
}

.fadeIn-left {
  left: 20%;
}

.fadeIn-right {
  left: 80%;
}
</style>

<script setup>
import { ref, onMounted } from "vue";
import imageApi from "@/services/api/images";
import TagsSelect from "@/components/form/TagsSelect.vue";
import { showMessage, askForConfirmation } from "@/utils/toast";

const emit = defineEmits([
  "addImage",
  "previewImage",
  "importFromDrive",
  "editImage",
  "addKeyword",
]);
const src =
  "https://image.freepik.com/free-photo/stylish-young-woman-with-bags-taking-selfie_23-2147962203.jpg";

let rows = ref([]);
let isLoading = ref(false);
let totalRecords = ref(0);
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
});

const columns = ref([
  {
    label: "ID",
    field: "id",
  },
  {
    label: "Preview",
    field: "url",
    sortable: false,
  },
  {
    label: "Width",
    field: "width",
  },
  {
    label: "Height",
    field: "height",
  },
  {
    label: "Keywords",
    field: "keywords",
  },
  {
    label: "Created On",
    field: "created_at",
  },
  {
    label: "Actions",
    field: "actions",
    sortable: false,
    thClass: "text-center",
  },
]);

const deleteImage = async (image) => {
  try {
    const confirmation = await askForConfirmation(
      "Are you sure you want to de attach all the keywords, tags for this image?"
    );
    if (!confirmation.isConfirmed) return;
    const res = await imageApi.deleteImage(image);
    showMessage(res.message);
    loadItems();
  } catch (error) {
    showMessage(error.message, "error");
  }
};

const updateParams = (params) => {
  serverParams.value = Object.assign({}, serverParams.value, params);
  loadItems();
};

const loadItems = async () => {
  isLoading.value = true;
  try {
    const res = await imageApi.getImages(serverParams.value);
    rows.value = res.data;
    totalRecords.value = res.meta.total;
    isLoading.value = false;
  } catch (error) {
    showMessage(error.message, "error");
    console.log(error);
  }
};

defineExpose({ loadItems });
onMounted(() => {
  loadItems();
});
</script>
