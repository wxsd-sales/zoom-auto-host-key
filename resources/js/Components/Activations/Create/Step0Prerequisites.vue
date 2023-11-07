<script setup lang="ts"></script>

<template>
  <div class="columns is-multiline">
    <div class="column is-full">
      <h2 class="title">Before we begin...</h2>
      <h3 class="subtitle">Please ensure you have completed these prerequisites</h3>
    </div>
    <div class="column is-full content mb-0">
      <p>
        This page allows you to activate the Zoom Auto Host Key application in your Webex Control Hub organization. For
        a smooth activation experience, please ensure that you have:
      </p>
      <ol class="gradient-list">
        <li>
          Factory reset the target devices or otherwise
          <b>ensured that they don't have any conflicting active macros and users/bots</b> with
          <span class="is-family-monospace">xAPI</span> access.
        </li>
        <li>
          Provided <b>dedicated internet access</b> to the devices so they can access Webex services and this
          application host.
        </li>
        <li>Moved the devices into Workspaces, that is, the devices should be in <b>shared mode</b>.</li>
        <li>Enough <b>Zoom Business or Concurrent Meetings Basic/Plus licenses</b>, to cover your deployment size.</li>
        <li>
          Administrator access to the org's Webex
          <a href="https://www.webex.com/control-hub.html" target="_blank">Control Hub</a> dashboard with the devices as
          well as <a href="https://zoom.us/account#/" target="_blank">Zoom Tenant</a> (to make required changes).
        </li>
      </ol>
      <div class="has-text-danger">
        <p>
          Please note that this application is strictly a PoC (Proof-of-Concept) for hosting Zoom Meetings without
          providing the Host Key on RoomOS devices and <b>is not designed for use in a production environment</b> or for
          an extend period of time.
        </p>
        <p>
          This project is under rapid development. Although, we will do our best to make any needed changes in a
          backward-compatible fashion, it may not always be possible (we are a small team). As a result, expect things
          to break from time to time. Use at your own risk.
        </p>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
// https://codepen.io/erinesullivan/pen/qGrdGV

$black: #000000;
$blue: #00bcf5;
$green: #82cf5f;
$yellow: #fbbd23;
ol.gradient-list {
  counter-reset: gradient-counter;
  list-style: none;
  margin: 1.75rem 0;
  padding-left: 1rem;
  > li {
    border-radius: 0.1rem 0.5rem 0.5rem 0.5rem;
    counter-increment: gradient-counter;
    margin-top: 1rem;
    min-height: 3rem;
    padding: 0 1rem 1rem 3rem;
    position: relative;
    &::before,
    &::after {
      background: linear-gradient(135deg, $blue 0%, $green 100%);
      border-radius: 1rem 1rem 0.25rem 1rem;
      content: '';
      height: 3rem;
      left: -1rem;
      overflow: hidden;
      position: absolute;
      top: -1rem;
      width: 3rem;
    }
    &::before {
      align-items: flex-end;
      content: counter(gradient-counter);
      color: $black;
      display: flex;
      font-weight: bold;
      justify-content: flex-end;
      padding: 0.125em 0.25em;
      z-index: 1;
    }
    @for $i from 1 through 5 {
      &:nth-child(10n + #{$i}):before {
        background: linear-gradient(135deg, rgba($green, $i * 0.2) 0%, rgba($yellow, $i * 0.2) 100%);
      }
    }
    @for $i from 6 through 10 {
      &:nth-child(10n + #{$i}):before {
        background: linear-gradient(
          135deg,
          rgba($green, 1 - (($i - 5) * 0.2)) 0%,
          rgba($yellow, 1 - (($i - 5) * 0.2)) 100%
        );
      }
    }
    + li {
      margin-top: 1rem;
    }
  }
}
</style>
