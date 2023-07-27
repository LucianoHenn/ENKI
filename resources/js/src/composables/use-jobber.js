import { useForm } from "vee-validate"
import jobberApi from "@/services/api/jobber"
import { errorHandler } from "@/utils/form"

const DEFAULT_CREATION_VALUES = {
  name: "",
  code: "",
  secret: "",
  status: "0",
}

export const useCreateJobber = (onSuccess) => {
  const form = useForm({
    initialValues: {
      ...DEFAULT_CREATION_VALUES,
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    console.log(values)
    return;
    try {
      const response = await jobberApi.createJobber(values)
      form.resetForm();
      onSuccess && onSuccess(response)
    } catch (e) {
      errorHandler(e, form);
    }
  })

  form.onSubmit = onSubmit

  return form
}

export const useUpdateJobber = (onSuccess) => {
  const form = useForm({
    initialValues: {
      ...DEFAULT_CREATION_VALUES,
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      const response = await jobberApi.updateJobber(values)
      onSuccess && onSuccess(response)
    } catch (e) {
      errorHandler(e, form);
    }
  })

  form.onSubmit = onSubmit

  return form
}
