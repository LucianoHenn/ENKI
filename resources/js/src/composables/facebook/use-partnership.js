import { useForm } from "vee-validate";
import partnershipApi from "@/services/api/facebook/partnerships";
import { errorHandler, serializeFormData } from "@/utils/form";

const DEFAULT_CREATION_VALUES = {
  name: "",
  display_name: "",
  template_url: "",
  countries: [],
  status: "inactive",
};

export const useCreatePartnership = (onSuccess) => {
  const form = useForm({
    initialValues: {
      ...DEFAULT_CREATION_VALUES,
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      const response = await partnershipApi.createPartnership(values);
      form.resetForm();
      onSuccess && onSuccess(response);
    } catch (e) {
      errorHandler(e, form);
    }
  });
  form.onSubmit = onSubmit;
  return form;
};

export const useUpdatePartnership = (onSuccess) => {
  const form = useForm({
    initialValues: {
      id: null,
      ...DEFAULT_CREATION_VALUES,
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      const response = await partnershipApi.updatePartnership(values);
      onSuccess && onSuccess(response);
    } catch (e) {
      errorHandler(e, form);
    }
  });

  form.onSubmit = onSubmit;
  return form;
};
