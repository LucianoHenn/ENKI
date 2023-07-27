import { ref, watch, onMounted } from 'vue';
import { useEventListener } from '@vueuse/core';

const useBsModal = (isShowRef, emit) => {
  const staticModalRef = ref(null);

  const showModal = () => window.bootstrap.Modal.getInstance(staticModalRef.value)?.show();
  const closeStaticModal = () => window.bootstrap.Modal.getInstance(staticModalRef.value)?.hide();

  const setModal = (show
  ) => {
    if (show) {
      showModal();
    } else {
      closeStaticModal();
    }
  }

  // Emit closeStaticModal when modal is closed.
  useEventListener(staticModalRef, 'hidden.bs.modal', () => emit('closeStaticModal'))
  watch(isShowRef, (show) => setModal(show))

  onMounted(() => {
    new window.bootstrap.Modal(staticModalRef.value);

    window.bootstrap.Modal.getInstance(staticModalRef.value)._config.backdrop =
      "static";
    window.bootstrap.Modal.getInstance(
      staticModalRef.value
    )._config.keyboard = false;

    setModal(isShowRef.value);
  });

  return {
    staticModalRef,
    showModal,
    closeStaticModal
  }
}

export default useBsModal;
