<template>
  <div
    ref="modalRef"
    id="editUsertModal"
    class="modal fade"
    aria-labelledby="editUsertModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-md modal-dialog-centered">
      <form @submit="onSubmit">
        <div class="modal-content mailbox-popup">
          <div class="modal-header">
            <h5 class="modal-title">Edit User</h5>
            <button
              type="button"
              data-dismiss="modal"
              data-bs-dismiss="modal"
              aria-label="Close"
              class="btn-close"
            ></button>
          </div>
          <div class="modal-body">
            <div class="add-contact-box">
              <div class="add-contact-content">
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Name</label>
                      <Field
                        name="id"
                        type="hidden"
                        class="hidden"
                        v-model="user.id"
                      />
                      <Field
                        name="name"
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': errors.name }"
                        placeholder="Name"
                        v-model="user.name"
                      />
                      <ErrorMessage class="invalid-feedback" name="name" />
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Email</label>
                      <Field
                        name="email"
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': errors.email }"
                        placeholder="Code"
                        v-model="user.email"
                      />
                      <ErrorMessage class="invalid-feedback" name="email" />
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group mb-4">
                      <label>Roles</label>
                      <Field
                        name="roles"
                        type="select"
                        v-model="user.roles"
                        class="form-select"
                        :class="{ 'is-invalid': errors.roles }"
                      >
                        <roles-select v-model="user.roles" :class="{ 'is-invalid': errors.roles }"/>
                      </Field>
                      <ErrorMessage class="invalid-feedback" name="roles" />
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group mb-4">
                      <label>Password</label>
                      <Field
                        name="password"
                        type="password"
                        class="form-control"
                        :class="{ 'is-invalid': errors.password }"
                        placeholder="Password"
                      />
                      <ErrorMessage class="invalid-feedback" name="password" />
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group mb-4">
                      <label>Confirm Password</label>
                      <Field
                        name="c_password"
                        type="password"
                        class="form-control"
                        :class="{ 'is-invalid': errors.c_password }"
                        placeholder="Confirm Password"
                      />
                      <ErrorMessage
                        class="invalid-feedback"
                        name="c_password"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-default"
              :class="{disabled : store.getters.isLoading}"
              data-dismiss="modal"
              data-bs-dismiss="modal"
            >
              Discard
            </button>
            <submit-button
              class="btn btn-primary"
              buttonText="Update User"
              textLoading="Loading..."
              :is-loading="store.getters.isLoading"
            />
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
<script setup>
import { useStore } from "vuex";
import { toRef, ref } from "vue";
import { Form, Field, ErrorMessage } from "vee-validate";
import { showMessage } from "@/utils/toast";
import { useUpdateUser } from "@/composables/use-user";
import useBsModal from "@/composables/useBsModal";
import SubmitButton from "@/components/form/SubmitButton.vue";
import RolesSelect from "@/components/form/RolesSelect.vue";

const user = ref({
  id: null,
  name: "",
  role: "",
  email: "",
  password: "",
  c_password: "",
});

const store = useStore();
const emit = defineEmits(["closeModal", "userUpdated"]);
const props = defineProps({
  isShow: Boolean,
});

const setUserData = (data) => {
  user.value = JSON.parse(JSON.stringify(data));
  user.value.roles = data.roles_list.map((role) => {
    return {
      value: role.name,
      name: role.display_name
    }
  });
};

defineExpose({ setUserData });

const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);
const { onSubmit, errors } = useUpdateUser((res) => {
  emit("userUpdated", res.data);
  showMessage(res.message);
  closeModal();
});
</script>
