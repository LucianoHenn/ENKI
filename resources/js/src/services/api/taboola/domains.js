import request from "@/services/request";

export default {
  getDomains(params, config = {}) {
    return request("get", "/taboola/domains", params, config);
  },
  createDomain(params, config = {}) {
    return request("post", `/taboola/domains`, params, config);
  },
  updateDomain(params, config = {}) {
    const { id, ...rest } = params;
    return request("put", `/taboola/domains/${id}`, rest, config);
  },
  deleteDomain(params, config = {}) {
    const { id, ...rest } = params;
    return request("delete", `/taboola/domains/${id}`, rest, config);
  },
};
