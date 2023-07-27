import { useForm } from "vee-validate"
import clientApi from "@/services/api/arc-association"
import { errorHandler } from "@/utils/form"

const DEFAULT_CREATION_VALUES = {
  client_id: "",
  market_id: "",
  source_type: "",
  source: "",
  device: "",
  begin: "",
  end: "",
  info: {},
}

export const useCreateClient = (onSuccess) => {
  const form = useForm({
    initialValues: {
      ...DEFAULT_CREATION_VALUES,
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    console.log(values)
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

export const  updateTimeRange = (onSuccess) => {
  const form = useForm({
    initialValues: {
      ...DEFAULT_CREATION_VALUES,
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      const response = await clientApi.updateTimeRange(values)
      onSuccess && onSuccess(response)
    } catch (e) {
      errorHandler(e, form);
    }
  })

  form.onSubmit = onSubmit

  return form
}
