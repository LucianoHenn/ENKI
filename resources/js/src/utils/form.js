import { showMessage } from './toast';

export const errorHandler = (e, form) => {
  if (e?.response?.status === 422) {
    const errors = e?.response?.data?.errors
    if (errors) {
      for (const [key, value] of Object.entries(errors)) {
        form.setFieldError(key, Array.isArray(value) ? value[0] : value)
      }
    }
  }
  showMessage(e?.response?.data?.message || e?.message || 'Something went wrong', 'error');
  console.error(e)
}

export const serializeFormData = (values) => {
  let formData = new FormData();
  for (let key in values ) {
    if (Array.isArray(values[key])) {
      values[key].forEach((item) => {
        formData.append(`${key}[]`, item.id? item.id : item);
      })
    }
    else if (typeof values[key] === 'object' && (values[key] !== null && values[key].id !== undefined)) {
      formData.append(key, values[key].id);
    } else {
      if (values[key] === undefined || values[key] === null) {
        formData.append(key, '');
      }
      else {
        formData.append(key, values[key]);
      }
    }
  }
  return formData;
};
