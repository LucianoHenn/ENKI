export const initTooltip = () => {
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  tooltipTriggerList.map((tooltipTriggerEl) => {
    return new window.bootstrap.Tooltip(tooltipTriggerEl, {
      trigger: "hover",
    });
  });
};
