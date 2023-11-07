<script setup lang="ts">
export interface Props {
  operationMode: string;
}
defineProps<Props & { errors?: unknown; processing: boolean }>();

interface Emits {
  'update:operationMode': [string];
}
const emits = defineEmits<Emits>();
</script>

<template>
  <div class="columns is-multiline is-vcentered">
    <div class="column is-full">
      <h2 class="subtitle">Step #5</h2>
      <h2 class="title">Choose Operation Mode</h2>
    </div>
    <div class="column is-full content mb-0">
      <p>
        When someone dials a Zoom SIP address (i.e. *@zoomcrc.com) on a configured RoomOS device, this application can
        display an on device UI Message Prompt for confirmation. If the person trying to join is the meeting host, they
        can then choose to use their Zoom Host Key, or the OTP that is simultaneously sent over Webex Messaging. This
        essentially verifies that the user trying to join the meeting is in fact the host of the meeting.
      </p>
      <p>
        In the default, automated operation mode, the user is not prompted for any confirmation. Hence, there's no
        verification of the user's identity.
      </p>
    </div>

    <div class="column">
      <div class="field">
        <input
          id="operation-mode-automatic"
          class="is-checkradio is-dark"
          type="radio"
          name="operation-mode"
          :value="'automatic'"
          :checked="operationMode === 'automatic'"
          :disabled="processing"
          @input="emits('update:operationMode', $event.target.value)"
        />
        <label for="operation-mode-automatic">
          Automatic; default, most convenient &mdash; Start meetings as a host on the RoomOS device without any user
          confirmation
        </label>
        <span v-if="errors?.operationMode" class="help is-danger">{{ errors?.operationMode }}</span>
        <span v-else class="help">
          Any user may start the meeting using the original host's identity or a machine/room account if configure (in
          Step #6, below)
        </span>
      </div>

      <div class="field">
        <input
          id="operation-mode-manual"
          class="is-checkradio is-dark"
          type="radio"
          name="operation-mode"
          :value="'manual'"
          :checked="operationMode === 'manual'"
          :disabled="processing"
          @input="emits('update:operationMode', $event.target.value)"
        />
        <label for="operation-mode-manual">
          Manual; flexible, more secure &mdash; Show UI prompt on the RoomOS device before starting the meeting as a
          host
        </label>
        <span v-if="errors?.operationMode" class="help is-danger">{{ errors?.operationMode }}</span>
        <span v-else class="help">
          The host will have the choice to input their Zoom Host Key (if they remember it), or the OTP sent over Webex
          Messaging &mdash; that will become their new Zoom Host Key
        </span>
        <span class="help">
          Any other user may choose to host as a pre-selected machine/room account if configure (in Step #6, below)
        </span>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
