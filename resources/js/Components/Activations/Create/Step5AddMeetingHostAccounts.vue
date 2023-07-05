<script setup lang="ts">
import { computed } from 'vue';

export interface Props {
  domain?: string;
  hostAccounts: string[] | null;
}
const props = withDefaults(defineProps<Props & { errors?: unknown; processing: boolean }>(), {
  hostAccounts: null,
  domain: 'example.com'
});

interface Emits {
  'update:hostAccounts': [string[]];
}
const emits = defineEmits<Emits>();

const emails = computed(() => {
  return ['account1@' + props.domain, 'account2@' + props.domain];
});
</script>

<template>
  <div class="columns is-multiline">
    <div class="column is-full">
      <h2 class="subtitle">Step #5 <span class="has-text-info">(optional)</span></h2>
      <h2 class="title">Add Meeting Host Accounts</h2>
    </div>
    <div class="column is-full content mb-0">
      <p>
        When someone dials a Zoom SIP address (i.e. *@zoomcrc.com) on a configured RoomOS device, this application
        displays an on device UI Message Prompt. The user can then choose to host the meeting as a machine/room account
        or their own user account. If you do not provide any machine/room accounts below, the app will default to using
        the user's account. However, in this case, the user account's host key will be reset.
      </p>
      <p>
        Depending on the deployment size, you may provide multiple machine/room accounts to circumvent Zoom's limits on
        hosting concurrent meetings.
      </p>
    </div>
    <div class="column is-full">
      <label class="label" for="bot-token">Host Account Email(s)</label>
      <div class="control has-icons-left">
        <input
          id="bot-token"
          class="input"
          type="email"
          multiple
          :disabled="processing"
          :placeholder="emails.join(',')"
          :value="hostAccounts?.join(',')"
          @input="
            emits(
              'update:hostAccounts',
              $event.target.value?.length > 0 ? $event.target.value.split(',').map((i) => i.trim()) : null
            )
          "
        />
        <span class="icon is-left">
          <i class="mdi mdi-at" />
        </span>
      </div>
      <p v-if="errors?.zmHostAccounts" class="help is-danger">{{ errors?.zmHostAccounts }}</p>
      <p v-else class="help">Provide a comma separated list of machine/room account emails.</p>
    </div>
  </div>
</template>

<style scoped></style>
