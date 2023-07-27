import request from "@/services/request";

export default {
  getTemplates(params, config = {}) {
    return request("get", "/taboola/templates", params, config);
  },
  createTemplate(params, config = {}) {
    return request("post", `/taboola/templates`, params, config);
  },
  updateTemplate(params, config = {}) {
    const { id, ...rest } = params;
    return request("put", `/taboola/templates/${id}`, rest, config);
  },
  deleteTemplate(params, config = {}) {
    const { id, ...rest } = params;
    return request("delete", `/taboola/templates/${id}`, rest, config);
  },
};
