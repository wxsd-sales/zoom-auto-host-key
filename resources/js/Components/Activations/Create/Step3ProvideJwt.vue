<script setup lang="ts">
import { ref, watch } from 'vue';
import { JWTPayload } from 'jose';
import * as jose from 'jose';

interface Props {
  jwt: string | null;
}
const props = defineProps<Props & { errors?: unknown }>();

interface Emits {
  'update:jwt': [string];
}
const emits = defineEmits<Emits>();

function togglePasswordReveal() {
  jwtInputType.value = jwtInputType.value === 'password' ? 'text' : 'password';
}

const jwtInputType = ref<'password' | 'text'>('password');
const jwtPayload = ref<(JWTPayload | { error?: string }) | null>();

watch(
  () => props.jwt,
  (value: string) => {
    try {
      jwtPayload.value = value?.length > 0 ? jose.decodeJwt(value) : null;
    } catch (e) {
      jwtPayload.value = { error: e };
    }
  }
);
</script>

<template>
  <div class="columns is-multiline">
    <div class="column is-full">
      <h2 class="subtitle">Step #3</h2>
      <h2 class="title">Provide JWT</h2>
    </div>
    <div class="column is-full content mb-0">
      <p>
        Activate the Workspace Integration using the "Actions" dropdown button on the Integration Details page. This
        should give you the Activation Code generated for Zoom Auto Host Key.
      </p>
      <p>Please transfer the Activation Code that you see on Activate Integration page to the input field below.</p>
    </div>
    <div class="column is-full">
      <label class="label" for="webex-workspace-integration-jwt">
        JWT (Activation/Update Code) <sup class="has-text-danger" title="required">*</sup>
      </label>
      <div class="control has-icons-left has-icons-right">
        <input
          id="webex-workspace-integration-jwt"
          class="input"
          placeholder="eyJraWQiOi..."
          required
          :type="jwtInputType"
          :value="jwt"
          @input="emits('update:jwt', $event.target.value)"
        />
        <span class="icon is-left">
          <i class="mdi mdi-key" />
        </span>
        <span class="icon is-right is-clickable">
          <i
            :class="['mdi', jwtInputType === 'password' ? 'mdi-eye' : 'mdi-eye-off']"
            @click="togglePasswordReveal()"
          />
        </span>
      </div>
      <p v-if="errors?.wbxWiJwt" class="help is-danger">{{ errors?.wbxWiJwt }}</p>
      <p v-else class="help">
        Copy-paste the Activation Code that you see on the Activate Integration Webex Control Hub page.
      </p>
    </div>
    <div class="column is-full">
      <details :open="jwtPayload?.error">
        <summary>JWT Payload</summary>
        <pre>{{ jwtPayload }}</pre>
      </details>
    </div>
  </div>
</template>

<style scoped></style>
