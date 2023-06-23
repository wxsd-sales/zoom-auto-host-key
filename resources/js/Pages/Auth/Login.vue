<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps({ errors: {} });
</script>

<template>
  <section id="login" class="container p-4 is-align-items-center">
    <div class="columns is-multiline">
      <div class="column is-12 is-hidden">
        <input class="input is-rounded is-medium is-fullwidth" name="provider" value="webex" readonly disabled />
      </div>
      <div class="column is-12">
        <article class="message is-info is-medium">
          <div class="message-header is-hidden" />
          <div class="message-body content">
            <p>
              Join Zoom CRC meetings on RoomOS devices as a host, without the need for inputting the host key. To learn
              more, watch this
              <a href="https://app.vidcast.io/share/" target="_blank">Vidcast</a>.
            </p>
          </div>
        </article>
        <article class="message is-danger is-medium">
          <div class="message-header is-hidden" />
          <div class="message-body content">
            <p>
              Before continuing, please note that you will need access to a
              <b>compatible RoomOS 11 device</b> registered in <b>shared mode</b> and
              <b>administrator access to the corresponding Webex Control Hub</b> organization.
            </p>
            <ul>
              <li>
                Do not login using your Cisco account (*@cisco.com); as you would most likely not have admin access to
                the corresponding Control Hub org.
              </li>
              <li>
                If you don't have an existing sandboxed or demo Control Hub org with admin access, please follow the
                instructions on the
                <a href="https://collabtoolbox.cisco.com/tools/sandbox" target="_blank">Collab Toolbox Sandbox</a> page
                to create one.
              </li>
              <li>
                If your device is in personal mode, you will have to perform a factory reset and then register it as a
                shared mode device on the sandboxed or demo Control Hub org.
              </li>
            </ul>
          </div>
        </article>
      </div>
      <div v-if="!!Object.keys(errors).length" class="column is-12">
        <article class="message is-danger is-medium">
          <div class="message-header">
            <p>Error</p>
          </div>
          <div class="message-body content">
            <ul class="mt-0">
              <li v-for="(error, key) in errors" :key="key">
                {{ error }}
              </li>
            </ul>
          </div>
        </article>
      </div>
      <div class="column is-12">
        <Link
          class="button is-rounded is-medium is-fullwidth is-primary has-text-weight-bold"
          type="button"
          :href="route('auth.webex')"
        >
          <span class="icon">
            <i class="mdi mdi-login" />
          </span>
          <span>Login with Webex</span>
        </Link>
      </div>
    </div>
  </section>
</template>

<style scoped></style>
