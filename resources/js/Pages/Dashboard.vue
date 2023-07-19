<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';

interface Props {
  activations: any[];
}

withDefaults(defineProps<Props>(), {
  activations: () => []
});
const form = useForm({});
</script>

<template>
  <div class="container px-4 mb-6">
    <div class="columns is-vcentered">
      <div class="column is-3">
        <Link
          class="button is-fullwidth is-rounded is-primary is-light is-justify-content-space-between"
          :href="route('activations.create')"
        >
          <span class="icon">
            <i class="mdi mdi-wrench" />
          </span>
          <span>Activate</span>
        </Link>
      </div>
      <div class="column is-6 is-offset-3 has-text-centered has-text-right-tablet">
        <p>Last refreshed at: {{ new Date().toLocaleString() }}</p>
      </div>
    </div>
    <template v-if="activations.length === 0">
      <hr />
      <p>You currently have no activations. To create one, click the "Activate" button above.</p>
    </template>
    <template v-for="activation in activations" :key="activation.id">
      <hr />
      <div class="columns is-vcentered is-mobile">
        <div class="column is-6">
          <p class="title is-size-5 has-text-weight-bold">{{ activation.wbxWiDisplayName }}</p>
        </div>
        <div class="column is-3">
          <a
            v-show="false"
            class="button is-fullwidth is-rounded is-warning is-light is-justify-content-space-between"
            :href="route('activations.edit', activation.id)"
          >
            <span class="icon">
              <i class="mdi mdi-pencil" />
            </span>
            <span>Edit</span>
          </a>
        </div>
        <form
          v-show="false"
          class="column is-3"
          @submit.prevent="form.delete(route('activations.destroy', activation.id), { preserveScroll: true })"
        >
          <button class="button is-fullwidth is-rounded is-danger is-light is-justify-content-space-between">
            <span class="icon">
              <i class="mdi mdi-delete" />
            </span>
            <span>Delete</span>
          </button>
        </form>
      </div>
      <div class="columns">
        <div class="column is-12">
          <div class="level is-mobile mb-0">
            <p class="level-left">id</p>
            <p class="level-right">{{ activation.id }}</p>
          </div>
          <div class="level is-mobile mb-0">
            <p class="level-left">updated</p>
            <p class="level-right">{{ new Date(activation.updatedAt).toLocaleString() }}</p>
          </div>
          <div class="level is-mobile mb-0">
            <p class="level-left">created</p>
            <p class="level-right">{{ new Date(activation.createdAt).toLocaleString() }}</p>
          </div>
          <div v-if="activation.deletedAt" class="level is-mobile mb-0">
            <p class="level-left">deleted</p>
            <p class="level-right">{{ new Date(activation.deletedAt).toLocaleString() }}</p>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
