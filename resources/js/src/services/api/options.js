import request from "@/services/request"

export default {
  getOptions(params, config = {}) {
    return request("get", "/options", params, config)
  },
  getOption(name, config = {}) {
    return request("get", `/options?name=${name}`, {}, config)
  },
  createOption(params, config = {}) {
    return request("post", `/options`, params, config)
  },
  updateOption(params, config = {}) {
    const { id, ...rest } = params
    return request("put", `/options/${id}`, rest, config)
  },
  deleteOption(params, config = {}) {
    const { id, ...rest } = params
    return request("delete", `/options/${id}`, rest, config)
  }
}
