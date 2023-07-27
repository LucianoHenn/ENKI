import { ref, watch, onMounted } from 'vue';
import { useEventListener } from '@vueuse/core';

const useBsModal = (isShowRef, emit) => {
    const modalRef = ref(null);

    const showModal = () => window.bootstrap.Modal.getInstance(modalRef.value)?.show();
    const closeModal = () => window.bootstrap.Modal.getInstance(modalRef.value)?.hide();

    const setModal = (show
        ) => {
        if (show) {
            showModal();
        } else {
            closeModal();
        }
    }

    // Emit closeModal when modal is closed.
    useEventListener(modalRef, 'hidden.bs.modal', () => emit('closeModal'))
    watch(isShowRef, (show) => setModal(show))

    onMounted(() => {
        new window.bootstrap.Modal(modalRef.value);

        setModal(isShowRef.value);
    });

    return {
        modalRef,
        showModal,
        closeModal
    }
}

export default useBsModal;
