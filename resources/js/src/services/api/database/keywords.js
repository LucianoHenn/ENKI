import request from "@/services/request";

export default {
  getKeywords(params, config = {}) {
    return request("get", "/database/keywords", params, config);
  },
  searchKeyword(params, config = {}) {
    return request("get", "/database/keywords/search", params, config);
  },
  getKeywordById(params, config = {}) {
    const { id, ...rest } = params;
    return request("get", `/database/keywords/${id}`, rest, config);
  },
  createKeyword(params, config = {}) {
    return request("post", `/database/keywords`, params, config);
  },
  createKeywordsInCampaignGenerator(params, config = {}) {
    return request(
      "post",
      `/database/keywords/storeDirectlyFromCampaignGenerator`,
      params,
      config
    );
  },
  createKeywordWithImageUpload(params, config = {}) {
    return request(
      "post",
      `/database/keywords/storeWithUploadImage`,
      params,
      config
    );
  },
  updateKeyword(params, config = {}) {
    const { id, ...rest } = params;
    return request("put", `/database/keywords/${id}`, rest, config);
  },
  deleteKeyword(params, config = {}) {
    const { id, ...rest } = params;
    return request("delete", `/database/keywords/${id}`, rest, config);
  },
  checkImagesRelated(params, config = {}) {
    const { id, ...rest } = params;
    return request("get", `/database/keywords/checkImages/${id}`, rest, config);
  },
  getAssociateImages(params, config = {}) {
    const { id, ...rest } = params;
    return request("get", `/database/keywords/images/${id}`, rest, config);
  },
  removeAssociateImages(params, config = {}) {
    const { id, ...rest } = params;
    return request("put", `/database/keywords/images/${id}`, rest, config);
  },
  uploadImage(params, config = {}) {
    return request("post", `/database/keywords/upload-images`, params, config);
  },
  addAssociateImages(params, config = {}) {
    return request(
      "put",
      `/database/keywords/add-associate-images`,
      params,
      config
    );
  },
  assignCategoryToKeywords(params, config = {}) {
    return request("put", `/database/keywords/assign-category`, params, config);
  },
  assignBulkCategoryToKeywords(params, config = {}) {
    return request(
      "put",
      `/database/keywords/assign-bulk-category`,
      params,
      config
    );
  },
};
