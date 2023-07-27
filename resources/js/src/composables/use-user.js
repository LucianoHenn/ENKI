import { useForm } from "vee-validate";
import userApi from "@/services/api/users";
import { errorHandler, serializeFormData } from "@/utils/form";

const DEFAULT_CREATION_VALUES = {
  name: "",
  email: "",
  roles: [],
  password: "",
  c_password: "",
};

export const useCreateUser = (onSuccess) => {
  const form = useForm({
    initialValues: {
      ...DEFAULT_CREATION_VALUES,
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    console.log(values);
    try {
      const response = await userApi.createUser(values);
      form.resetForm();
      onSuccess && onSuccess(response);
    } catch (e) {
      errorHandler(e, form);
    }
  });

  form.onSubmit = onSubmit;
  return form;
};

export const useUpdateUser = (onSuccess) => {
  const form = useForm({
    initialValues: {
      ...DEFAULT_CREATION_VALUES,
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      const response = await userApi.updateUser(values);
      form.resetForm();
      onSuccess && onSuccess(response);
    } catch (e) {
      errorHandler(e, form);
    }
  });

  form.onSubmit = onSubmit;
  return form;
};

export const useUpdateAvatarProfile = (onSuccess) => {
  const form = useForm({
    initialValues: {
      id: null,
      avatar: "",
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    const formData = serializeFormData(values);
    try {
      const response = await userApi.updateAvatarProfile(formData);
      onSuccess && onSuccess(response);
    } catch (e) {
      errorHandler(e, form);
    }
  });

  form.onSubmit = onSubmit;
  return form;
};

export const useChangePassword = (onSuccess) => {
  const form = useForm({
    initialValues: {
      password: "",
      new_password: "",
      confirm_new_password: "",
    },
  });

  const onSubmit = form.handleSubmit(async (values) => {
    try {
      const response = await userApi.updatePassword(values);
      onSuccess && onSuccess(response);
      form.resetForm();
    } catch (e) {
      errorHandler(e, form);
    }
  });

  form.onSubmit = onSubmit;
  return form;
};

