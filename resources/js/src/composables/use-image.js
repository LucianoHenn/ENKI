import { useForm } from "vee-validate"
import imageApi from "@/services/api/images"
import { errorHandler, serializeFormData } from "@/utils/form"

const DEFAULT_CREATION_VALUES = {
  keywords: "",
  countries: [],
  languages: null,
  tags: [],
  images: [],
}

export const useCreateImage = (onSuccess) => {
  const form = useForm({
    initialValues: {
      ...DEFAULT_CREATION_VALUES,
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    const formData = serializeFormData(values);
    try {
      const response = await imageApi.createImage(formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      form.resetForm();
      onSuccess && onSuccess(response)
    } catch (e) {
      errorHandler(e, form);
    }
  })

  form.onSubmit = onSubmit
  return form
}

export const useUpdateImage = (onSuccess) => {
  const form = useForm({
    initialValues: {
      id: null,
      keywords: "",
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    const formData = serializeFormData(values);
    try {
      const response = await imageApi.updateImage({
        id: values.id,
        data: formData,
      }, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      })
      onSuccess && onSuccess(response)
    } catch (e) {
      errorHandler(e, form);
    }
  })

  form.onSubmit = onSubmit
  return form
}

export const useAddKeyword = (onSuccess) => {
  const form = useForm({
    initialValues: {
      keywords: "",
      countries: [],
      languages: null,
      tags: [],
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      const response = await imageApi.addKeyword(values)
      onSuccess && onSuccess(response)
      form.resetForm();
    } catch (e) {
      errorHandler(e, form);
    }
  })

  form.onSubmit = onSubmit
  return form
}
