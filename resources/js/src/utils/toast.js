import store from "@/store";

export const showMessage = (msg = "", type = "success") => {
  const toast = window.Swal.mixin({
    toast: true,
    position: "top",
    showConfirmButton: false,
    timer: 3000,
  });
  toast.fire({
    icon: type,
    title: msg,
    padding: "10px 20px",
  });
  store?.dispatch("setIsLoading", false);
};

export const askForConfirmation = (
  msg = "",
  title = "Are you sure?",
  confirmButtonText = "Yes, delete it!",
  cancelButtonText = "Cancel",
  html = "",
  icon = "warning"
) => {
  return window.Swal.fire({
    title,
    text: msg,
    icon,
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText,
    cancelButtonText,
    html,
  });
};

export const askForConfirmationWithHtml = (html = "") => {
  return window.Swal.fire({
    title: "Are you sure?",
    text: "",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!",
    cancelButtonText: "Cancel",
    html,
  });
};
