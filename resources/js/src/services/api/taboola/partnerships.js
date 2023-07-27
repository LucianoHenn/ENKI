import request from "@/services/request";

export default {
  getPartnerships(params, config = {}) {
    return request("get", "/taboola/taboolaPartnerships", params, config);
  },
  createPartnership(params, config = {}) {
    return request("post", `/taboola/taboolaPartnerships`, params, config);
  },
  updatePartnership(params, config = {}) {
    const { id, ...rest } = params;
    return request("put", `/taboola/taboolaPartnerships/${id}`, rest, config);
  },
  deletePartnership(params, config = {}) {
    const { id, ...rest } = params;
    return request(
      "delete",
      `/taboola/taboolaPartnerships/${id}`,
      rest,
      config
    );
  },
};
