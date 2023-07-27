import { useForm } from "vee-validate"
import clientApi from "@/services/api/clients"
import { errorHandler } from "@/utils/form"

const DEFAULT_CREATION_VALUES = {
  name: "",
  code: "",
  secret: "",
  status: "0",
}

export const useCreateClient = (onSuccess) => {
  const form = useForm({
    initialValues: {
      ...DEFAULT_CREATION_VALUES,
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      const response = await clientApi.createClient(values)
      form.resetForm();
      onSuccess && onSuccess(response)
    } catch (e) {
      errorHandler(e, form);
    }
  })

  form.onSubmit = onSubmit

  return form
}

export const useUpdateClient = (onSuccess) => {
  const form = useForm({
    initialValues: {
      ...DEFAULT_CREATION_VALUES,
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      const response = await clientApi.updateClient(values)
      onSuccess && onSuccess(response)
    } catch (e) {
      errorHandler(e, form);
    }
  })

  form.onSubmit = onSubmit

  return form
}
