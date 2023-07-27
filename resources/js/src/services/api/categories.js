import request from "@/services/request";

export default {
  getCategories(params, config = {}) {
    return request("get", "/categories", params, config);
  },
};
