<script setup lang="ts">
import { ref } from 'vue';

interface Props {
  clientId: string | null;
  clientSecret: string | null;
}
defineProps<Props & { errors?: unknown; processing: boolean }>();

interface Emits {
  'update:clientId': [string];
  'update:clientSecret': [string];
}
const emits = defineEmits<Emits>();

function togglePasswordReveal() {
  clientSecretType.value = clientSecretType.value === 'password' ? 'text' : 'password';
}

const clientSecretType = ref<'password' | 'text'>('password');
</script>

<template>
  <div class="columns is-multiline">
    <div class="column is-full">
      <h2 class="subtitle">Step #2</h2>
      <h2 class="title">Provide OAuth Credentials</h2>
    </div>
    <div class="column is-full content mb-0">
      <p>
        After successfully uploading the Workspace Integration's manifest file to your Control Hub org, you should see
        OAuth Credentials generated for Zoom Auto Host Key.
      </p>
      <p>Please transfer the OAuth Credentials that you see on Upload Integration page to the input fields below.</p>
    </div>
    <div class="column is-full">
      <label class="label" for="webex-workspace-integration-oauth-client-id">
        OAuth Client ID <sup class="has-text-danger" title="required">*</sup>
      </label>
      <div class="control has-icons-left">
        <input
          id="webex-workspace-integration-oauth-client-id"
          class="input"
          type="text"
          required
          :class="{ 'is-danger': errors?.wbxWiClientId }"
          :value="clientId"
          :disabled="processing"
          @input="emits('update:clientId', $event.target.value)"
        />
        <span class="icon is-left">
          <i class="mdi mdi-identifier" />
        </span>
      </div>
      <p v-if="errors?.wbxWiClientId" class="help is-danger">{{ errors?.wbxWiClientId }}</p>
      <p v-else class="help">
        Copy-paste the OAuth Client ID that you see on the Upload Integration Webex Control Hub page.
      </p>
    </div>
    <div class="column is-full">
      <label class="label" for="webex-workspace-integration-oauth-client-secret">
        OAuth Client Secret <sup class="has-text-danger" title="required">*</sup>
      </label>
      <div class="field has-addons">
        <div class="control is-expanded has-icons-left has-icons-right">
          <input
            id="webex-workspace-integration-oauth-client-secret"
            class="input"
            required
            :type="clientSecretType"
            :value="clientSecret"
            :disabled="processing"
            @input="emits('update:clientSecret', $event.target.value)"
          />
          <span class="icon is-left">
            <i class="mdi mdi-key" />
          </span>
          <span class="icon is-right is-clickable">
            <i
              class="mdi"
              :class="[clientSecretType === 'password' ? 'mdi-eye' : 'mdi-eye-off']"
              @click="togglePasswordReveal()"
            />
          </span>
        </div>
        <div class="control">
          <button class="button is-primary is-light">
            <span class="icon">
              <i class="mdi mdi-pencil"></i>
            </span>
          </button>
        </div>
      </div>
      <p v-if="errors?.wbxWiClientSecret" class="help is-danger">{{ errors?.wbxWiClientSecret }}</p>
      <p v-else class="help">
        Copy-paste the OAuth Client Secret that you see on the Upload Integration Webex Control Hub page.
      </p>
    </div>
  </div>
</template>

<style scoped></style>
