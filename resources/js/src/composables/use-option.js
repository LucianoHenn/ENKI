import { useForm } from "vee-validate"
import clientApi from "@/services/api/options"
import { errorHandler } from "@/utils/form"

const DEFAULT_CREATION_VALUES = {
  name: "",
  value: "",
  autoload: 0,
}

export const useCreateOption = (onSuccess) => {
  const form = useForm({
    initialValues: {
      ...DEFAULT_CREATION_VALUES,
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      const response = await clientApi.createOption(values)
      form.resetForm();
      onSuccess && onSuccess(response)
    } catch (e) {
      errorHandler(e, form);
    }
  })

  form.onSubmit = onSubmit

  return form
}

export const useUpdateOption = (onSuccess) => {
  const form = useForm({
    initialValues: {
      ...DEFAULT_CREATION_VALUES,
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      const response = await clientApi.updateOption(values)

      onSuccess && onSuccess(response)
    } catch (e) {
      errorHandler(e, form);
    }
  })

  form.onSubmit = onSubmit
  return form
}
