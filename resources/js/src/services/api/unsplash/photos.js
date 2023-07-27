import request from "@/services/request";

export default {
  searchPhotos(params, config = {}) {
    return request("get", "/unsplash/search", params, config);
  },
};
