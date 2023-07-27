import request from "@/services/request"

export default {
  getJobbers(params, config = {}) {
    return request("get", "/jobbers", params, config)
  },
  createJobber(params, config = {}) {
    return request("post", `/jobbers`, params, config)
  },
  updateJobber(params, config = {}) {
    return request("put", `/jobbers/${id}`, params, config)
  },
  getJobber(params, config = {}) {
    const { id, ...rest } = params
    return request("get", `/jobbers/${id}`, rest, config)
  }
}
