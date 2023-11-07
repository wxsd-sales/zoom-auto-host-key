<script setup lang="ts">
import { computed, Ref, ref, UnwrapRef } from 'vue';

export interface Props {
  domain?: string;
  hostAccounts: { email: string; key: string }[] | null;
}
const props = withDefaults(defineProps<Props & { errors?: unknown; processing: boolean }>(), {
  hostAccounts: null,
  domain: 'example.com'
});

interface Emits {
  'update:hostAccounts': [{ email: string; key: string }[]];
}
const emits = defineEmits<Emits>();

const hostAccountFields = ref(props?.hostAccounts ?? [{ email: null, key: null }]);

function addHostAccountField() {
  hostAccountFields.value.push({ email: null, key: null });
}

function removeHostAccountField(i: number) {
  hostAccountFields.value.splice(i, 1);
}

function filterHostAccountFields() {
  const accounts = hostAccountFields.value.filter((e) => e.email?.length && e.key?.length);

  return accounts.length > 0 ? accounts : null;
}
</script>

<template>
  <div class="columns is-multiline is-vcentered">
    <div class="column is-full">
      <h2 class="subtitle">Step #6 <span class="has-text-info">(optional)</span></h2>
      <h2 class="title">Add Meeting Host Accounts</h2>
    </div>
    <div class="column is-full content mb-0">
      <p>
        You may provide one or more machine accounts that can act as host of a meeting. This is specially handy if you
        want to be transparent that someone else besides the original meeting host is hosting. If you do not provide any
        accounts below, the app will default to using the host's identity. However, in this case, the meeting host's key
        will be changed.
      </p>
      <p>
        Depending on the deployment size, you may provide multiple machine/room accounts to circumvent Zoom's limits on
        hosting concurrent meetings.
      </p>
    </div>
    <template v-for="(hostAccountField, i) in hostAccountFields" :key="i">
      <div class="column is-8">
        <label class="label" for="zoom-host-account-email">
          Email
          <sup class="has-text-danger" title="required" :class="{ 'is-invisible': !hostAccountField.key?.length }">
            *
          </sup>
        </label>
        <div class="control has-icons-left">
          <input
            id="zoom-host-account-email"
            v-model.trim="hostAccountField.email"
            class="input"
            type="email"
            :placeholder="`account${i + 1}@example.com`"
            :required="!!hostAccountField.key"
            :disabled="processing"
            @input="emits('update:hostAccounts', filterHostAccountFields())"
          />
          <span class="icon is-left">
            <i class="mdi mdi-at" />
          </span>
        </div>
        <p v-if="errors?.[`zmHostAccounts.${i}.email`]" class="help is-danger">
          {{ errors?.[`zmHostAccounts.${i}.email`] }}
        </p>
        <p v-else class="help">Provide a machine/room account email.</p>
      </div>
      <div class="column is-4">
        <label class="label" for="zoom-host-account-key">
          Key
          <sup class="has-text-danger" title="required" :class="{ 'is-invisible': !hostAccountField.email?.length }">
            *
          </sup>
        </label>
        <div class="field is-grouped">
          <div class="control is-expanded has-icons-left">
            <input
              id="zoom-host-account-key"
              v-model.trim="hostAccountField.key"
              class="input"
              type="text"
              pattern="^[0-9]{6}$"
              placeholder="012345"
              :required="!!hostAccountField.email"
              :disabled="processing"
              @input="emits('update:hostAccounts', filterHostAccountFields())"
            />
            <span class="icon is-left">
              <i class="mdi mdi-numeric" />
            </span>
            <p v-if="errors?.[`zmHostAccounts.${i}.key`]" class="help is-danger">
              {{ errors?.[`zmHostAccounts.${i}.key`] }}
            </p>
            <p v-else class="help">Provide the account's host key.</p>
          </div>
          <div class="control">
            <button
              class="button is-danger is-light is-rounded"
              type="button"
              :disabled="hostAccountFields.length === 1"
              @click="
                removeHostAccountField(i);
                emits('update:hostAccounts', filterHostAccountFields());
              "
            >
              <span class="icon">
                <i class="mdi mdi-delete"></i>
              </span>
            </button>
          </div>
        </div>
      </div>
    </template>
    <div class="column is-8">
      <button
        class="button is-fullwidth is-info is-light is-rounded is-justify-content-space-between"
        type="button"
        @click="addHostAccountField()"
      >
        <span class="icon">
          <i class="mdi mdi-plus"></i>
        </span>
        <span>Add More</span>
      </button>
    </div>
  </div>
</template>

<style scoped></style>
