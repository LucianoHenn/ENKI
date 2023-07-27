import request from "@/services/request";

export default {
  getCampaigns(params, config = {}) {
    return request("get", "/taboola/campaigns", params, config);
  },
  getCampaignsData() {
    return request("get", "/taboola/get-campaigns-data");
  },
  getWeeklyCampaignsData() {
    return request("get", "/taboola/get-weekly-campaigns-data");
  },
  deleteCampaign(params, config = {}) {
    const { id, ...rest } = params;
    return request("delete", `/taboola/campaigns/${id}`, rest, config);
  },
};
