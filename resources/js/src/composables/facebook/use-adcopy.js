import { useForm } from "vee-validate"
import adCopyApi from "@/services/api/facebook/adcopies"
import { errorHandler, serializeFormData } from "@/utils/form"


const DEFAULT_CREATION_VALUES = {
  description: "",
  title: [],
  body: [],
  link: [],
  facebook_call_to_actions: []
}

export const useCreateAdCopy = (onSuccess) => {
  const form = useForm({
    initialValues: {
      ...DEFAULT_CREATION_VALUES
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      const response = await adCopyApi.createAdCopy(values)
      form.resetForm();
      onSuccess && onSuccess(response)
    } catch (e) {
      errorHandler(e, form);
    }
  })
  form.onSubmit = onSubmit
  return form
}

export const useUpdateAdCopy = (onSuccess) => {
  const form = useForm({
    initialValues: {
      id: null,
      ...DEFAULT_CREATION_VALUES,
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      const response = await adCopyApi.updateAdCopy(values)
      form.resetForm()
      onSuccess && onSuccess(response)
    } catch (e) {
      errorHandler(e, form);
    }
  })

  form.onSubmit = onSubmit;
  return form;
}

export const useCreateOrUpdateAdCopy = (onSuccess) => {
  const form = useForm({
    initialValues: {
      id: null,
      ...DEFAULT_CREATION_VALUES,
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      
      if(values.id) {
        const response = await adCopyApi.updateAdCopy(values)
        onSuccess && onSuccess(response)
      } else {
        const response = await adCopyApi.createAdCopy(values)
        form.resetForm();
        onSuccess && onSuccess(response)
      }
    } catch (e) {
      errorHandler(e, form);
    }
  })

  form.onSubmit = onSubmit;
  return form;
}
