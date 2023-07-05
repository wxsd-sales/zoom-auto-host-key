<script setup lang="ts">
import { computed, ref, watch, onBeforeUnmount } from 'vue';

interface Props {
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
  manifest: object | null;
}
const props = defineProps<Props & { errors?: unknown; processing: boolean }>();

interface Emits {
  'update:manifest': [object];
}
const emits = defineEmits<Emits>();

function createDownloadUrl(text: string) {
  return URL.createObjectURL(new Blob([text], { type: 'plain/text' }));
}

const displayNameSuffix = ref('');

const permissions = computed<
  { scope?: string; path?: string; access: string; role?: string; name: string; description: string; type: string }[]
>(() => {
  const apiAccess = props.apiAccess.map((e) => ({ ...e, type: 'API Access' }));
  const xapiStatus = props.xapiAccess.status.map((e) => ({ ...e, type: 'xAPI Status' }));
  const xapiEvents = props.xapiAccess.events.map((e) => ({ ...e, type: 'xAPI Events' }));
  const xapiCommands = props.xapiAccess.commands.map((e) => ({ ...e, type: 'xAPI Commands' }));

  return [...apiAccess, ...xapiStatus, ...xapiEvents, ...xapiCommands];
});

const manifestJson = computed(() => {
  const { manifest, errors, ...config } = props;
  const defaultDisplayName = config.displayName;

  if (displayNameSuffix.value?.length > 0) {
    const newDisplayName = defaultDisplayName + ' — ' + displayNameSuffix.value;
    return JSON.stringify({ ...config, displayName: newDisplayName }, null, 2);
  }

  return JSON.stringify(config, null, 2);
});

const manifestJsonLink = ref({ download: props.id + '.json', href: createDownloadUrl(manifestJson.value) });

watch(manifestJson, (value) => {
  URL.revokeObjectURL(manifestJsonLink.value.href);
  manifestJsonLink.value = { download: props.id + '.json', href: createDownloadUrl(value) };
});

onBeforeUnmount(() => URL.revokeObjectURL(manifestJsonLink.value.href));
</script>

<template>
  <div class="columns is-multiline">
    <div class="column is-full">
      <h2 class="subtitle">Step #1</h2>
      <h2 class="title">Deploy Manifest</h2>
    </div>
    <div class="column is-full content mb-0">
      <p>
        The application uses a Webex Workspace Integration to manage shared mode RoomOS devices and is defined by a JSON
        manifest file that you should deployed on your Control Hub org. Deploying the manifest file is a two step
        process. First, download the file by clicking the <a href="#download-button">"Download"</a> button below.
        Second, upload it to your Control Hub org by navigating to the
        <a href="https://admin.webex.com/workspaces/integrations" target="_blank">Workspace Integration page</a>.
      </p>
      <p>
        It is strongly advised that you deploy the manifest file to Control Hub as it is, without making any changes.
      </p>
      <div class="table-container">
        <table class="table is-bordered is-hoverable">
          <thead>
            <tr>
              <th>Scope/Path</th>
              <th>Type</th>
              <th>Name</th>
              <th>Access</th>
              <th>Description / Used to...</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="permission in permissions" :key="permission.type + (permission?.scope ?? permission?.path)">
              <th class="is-family-monospace">
                {{ permission.type.startsWith('API') ? permission.scope : permission.path }}
              </th>
              <td>{{ permission.type }}</td>
              <td>{{ permission.name }}</td>
              <td>{{ permission.access }} {{ permission.role != null ? '(' + permission.role + ')' : '' }}</td>
              <td>{{ permission.description }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="column is-full">
      <label class="label" for="display-name">Display Name Suffix</label>
      <div class="control has-icons-left">
        <input
          id="display-name-suffix"
          v-model.trim="displayNameSuffix"
          class="input"
          placeholder="CHG FL 7"
          autocomplete="off"
          :maxlength="255 - 3 - displayName.length"
          :disabled="processing"
          @input="emits('update:manifest', JSON.parse(manifestJson))"
        />
        <span class="icon is-left">
          <i class="mdi mdi-text-box" />
        </span>
      </div>
      <p class="help">A friendly name for easy identification on Webex Control Hub.</p>
    </div>
    <div class="column is-full">
      <label class="label" for="manifest">Manifest File (read-only) &mdash; {{ manifestJsonLink.download }}</label>
      <div class="control">
        <textarea
          id="manifest"
          class="textarea is-family-monospace"
          readonly
          rows="20"
          :value="manifestJson"
        ></textarea>
      </div>
      <p v-if="errors?.wbxWiManifest" class="help is-danger">{{ errors?.wbxWiManifest }}</p>
      <p v-else class="help">
        Please see integration manifest section of the
        <a
          href="https://developer.webex.com/docs/workspace-integration-technical-details#the-integration-manifest"
          target="_blank"
        >
          Workspace Integrations Guide
        </a>
        for detailed description.
      </p>
    </div>
    <div class="column is-two-fifths is-offset-three-fifths">
      <a
        id="download-button"
        class="button is-fullwidth is-rounded is-primary is-light is-justify-content-space-between"
        v-bind="manifestJsonLink"
      >
        <span class="icon">
          <i class="mdi mdi-download"></i>
        </span>
        <span>Download</span>
      </a>
    </div>
  </div>
</template>

<style scoped>
div.table-container {
  max-height: 250px;
  overflow-y: visible;
}
</style>
