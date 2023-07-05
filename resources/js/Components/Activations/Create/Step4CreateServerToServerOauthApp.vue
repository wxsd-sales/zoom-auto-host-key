<script setup lang="ts">
import { ref } from 'vue';

interface Props {
  scopes: [{ id: string; name?: string; description?: string }];
  accountId: string | null;
  clientId: string | null;
  clientSecret: string | null;
}
defineProps<Props & { errors?: unknown; processing: boolean }>();

interface Emits {
  'update:accountId': [string];
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
      <h2 class="subtitle">Step #4</h2>
      <h2 class="title">Create Server to Server OAuth App</h2>
    </div>
    <div class="column is-full content mb-0">
      <p>
        The application uses a Zoom Server to Server OAuth Application to manage Zoom Meetings on Webex RoomOS devices
        (utilizing Zoom CRC license). To learn how to create a a Zoom Server to Server OAuth Application, please refer
        to
        <a href="https://developers.zoom.us/docs/internal-apps/s2s-oauth/" target="_blank">Zoom's documentation</a> on
        the topic.
      </p>
      <p>
        Then, create and activate a Zoom Server to Server OAuth Application with the scopes below and provide its
        details:
      </p>
      <div class="table-container">
        <table class="table is-bordered is-hoverable">
          <thead>
            <tr>
              <th>Scope ID</th>
              <th>Scope Name</th>
              <th>Description / Used to...</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="scope in scopes" :key="scope.id">
              <th class="is-family-monospace">{{ scope.id }}</th>
              <td>{{ scope.name }}</td>
              <td>{{ scope.description }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="column is-full">
      <label class="label" for="zoom-server-to-server-oauth-app-account-id">
        Account ID <sup class="has-text-danger" title="required">*</sup>
      </label>
      <div class="control has-icons-left">
        <input
          id="zoom-server-to-server-oauth-app-account-id"
          class="input"
          required
          :value="accountId"
          :disabled="processing"
          @input="emits('update:accountId', $event.target.value)"
        />
        <span class="icon is-left">
          <i class="mdi mdi-account" />
        </span>
      </div>
      <p v-if="errors?.zmS2sAccountId" class="help is-danger">{{ errors?.zmS2sAccountId }}</p>
      <p v-else class="help">
        Copy-paste the OAuth Account ID that you see on the App Credentials section of the Zoom App page.
      </p>
    </div>
    <div class="column is-full">
      <label class="label" for="zoom-server-to-server-oauth-app-client-id">
        OAuth Client ID <sup class="has-text-danger" title="required">*</sup>
      </label>
      <div class="control has-icons-left">
        <input
          id="zoom-server-to-server-oauth-app-client-id"
          class="input"
          required
          :value="clientId"
          :disabled="processing"
          @input="emits('update:clientId', $event.target.value)"
        />
        <span class="icon is-left">
          <i class="mdi mdi-identifier" />
        </span>
      </div>
      <p v-if="errors?.zmS2sClientId" class="help is-danger">{{ errors?.zmS2sClientId }}</p>
      <p v-else class="help">
        Copy-paste the OAuth Client ID that you see on the App Credentials section of the Zoom App page.
      </p>
    </div>
    <div class="column is-full">
      <label class="label" for="zoom-server-to-server-oauth-app-client-secret">
        OAuth Client Secret <sup class="has-text-danger" title="required">*</sup>
      </label>
      <div class="control has-icons-left has-icons-right">
        <input
          id="zoom-server-to-server-oauth-app-client-secret"
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
            :class="['mdi', clientSecretType === 'password' ? 'mdi-eye' : 'mdi-eye-off']"
            @click="togglePasswordReveal()"
          />
        </span>
      </div>
      <p v-if="errors?.zmS2sClientSecret" class="help is-danger">{{ errors?.zmS2sClientSecret }}</p>
      <p v-else class="help">
        Copy-paste the OAuth Client Secret that you see on the App Credentials section of the Zoom App page.
      </p>
    </div>
  </div>
</template>

<style scoped>
div.table-container {
  max-height: 250px;
  overflow-y: scroll;
}
</style>
