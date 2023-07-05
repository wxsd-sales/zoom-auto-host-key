<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';

import Prerequisites from '@/Components/Activations/Create/Step0Prerequisites.vue';
import DeployManifest from '@/Components/Activations/Create/Step1DeployManifest.vue';
import ProvideOauthCredentials from '@/Components/Activations/Create/Step2ProvideOauthCredentials.vue';
import ProvideActivationCode from '@/Components/Activations/Create/Step3ProvideJwt.vue';
import CreateServerToServerApp from '@/Components/Activations/Create/Step4CreateServerToServerOauthApp.vue';
import AddMeetingHostAccounts from '@/Components/Activations/Create/Step5AddMeetingHostAccounts.vue';

interface Props {
  actionUrl: string;
  wbxWiConfig: {
    id: string;
    displayName: string;
    manifestVersion: number;
    vendor: string;
    email: string;
    description: string;
    availability: string;
    apiAccess: [{ scope: string; access: string; role: string; name: string; description: string }];
    xapiAccess: {
      status: [{ path: string; access: string; name: string; description: string }];
      events: [{ path: string; access: string; name: string; description: string }];
      commands: [{ path: string; access: string; name: string; description: string }];
    };
    provisioning: { type: 'manual' };
  };
  zmS2sConfig: { scopes: [{ id: string; name?: string; description?: string }] };
  wbxWiClientId: null;
  zmS2sAccountId: null;
  zmS2sClientId: null;
  zmHostAccounts: null;
}

const props = defineProps<Props & { errors?: unknown }>();
const form = useForm({
  wbxWiManifest: props.wbxWiConfig,
  wbxWiClientId: props.wbxWiClientId,
  wbxWiClientSecret: null,
  wbxWiJwt: null,
  zmS2sAccountId: props.zmS2sAccountId,
  zmS2sClientId: props.zmS2sClientId,
  zmS2sClientSecret: null,
  zmHostAccounts: props.zmHostAccounts
});
</script>

<template>
  <Head>
    <title>Create Activation</title>
  </Head>
  <form
    id="demo-activate"
    class="container px-4 mb-6"
    autocomplete="off"
    @submit.prevent="form.post(actionUrl, { preserveScroll: true })"
  >
    <Prerequisites />
    <hr />
    <DeployManifest
      v-model:manifest="form.wbxWiManifest"
      v-bind="wbxWiConfig"
      :errors="errors"
      :processing="form.processing"
    />
    <hr />
    <ProvideOauthCredentials
      v-model:client-id="form.wbxWiClientId"
      v-model:client-secret="form.wbxWiClientSecret"
      :errors="errors"
      :processing="form.processing"
    />
    <hr />
    <ProvideActivationCode v-model:jwt="form.wbxWiJwt" :errors="errors" :processing="form.processing" />
    <hr />
    <CreateServerToServerApp
      v-model:account-id="form.zmS2sAccountId"
      v-model:client-id="form.zmS2sClientId"
      v-model:client-secret="form.zmS2sClientSecret"
      v-bind="zmS2sConfig"
      :errors="errors"
      :processing="form.processing"
    />
    <hr />
    <AddMeetingHostAccounts v-model:host-accounts="form.zmHostAccounts" :errors="errors" />
    <hr />
    <div class="columns is-multiline">
      <div class="column is-12">
        <button
          type="submit"
          class="button is-medium is-rounded is-success is-fullwidth"
          :class="{ 'is-loading': form.processing }"
        >
          <span>Next</span>
          <span class="icon">
            <i class="mdi mdi-arrow-right" />
          </span>
        </button>
      </div>
      <div class="column is-12">
        <p v-show="Object.keys(errors as object).length > 0" class="subtitle has-text-danger">
          Please fix the errored field(s) above and try gain.
        </p>
      </div>
    </div>
  </form>
</template>

<style scoped></style>
