# Zoom Auto Host Key

**Join Zoom CRC meetings on RoomOS devices as a host, without the need for inputting the host key.**

This is a proof-of-concept application that lets anyone join Zoom Conference Room Connector (CRC) meetings as host on
RoomOS devices using a preconfigured machine/room account.

The target audience for this PoC are IT Administrators who want to emulate the Zoom Rooms scheduled meeting join
experience on Webex RoomOS devices for users in their organization. This PoC was developed in response to Zoom API
change as detailed [here](https://devforum.zoom.us/t/not-able-to-retrieve-host-key-using-users-api/70898).
The change disallow retrieving a host's key using their email address, essentially making previous device macro based
solutions useless.

<p align="center">
   <a href="https://app.vidcast.io/share/3fe18984-4c85-4edb-9550-cea3d03050e3" target="_blank">
       <img src="https://github.com/wxsd-sales/zoom-auto-host-key/assets/6129517/6734dbd0-3bb4-41fc-b77e-730f94ea8e57" alt="zoom-auto-host-key-demo"/>
    </a>
</p>

<!-- ⛔️ MD-MAGIC-EXAMPLE:START (TOC:collapse=true&collapseText=Click to expand) -->
<details>
<summary>Table of Contents (click to expand)</summary>

- [Overview](#overview)
- [Setup](#setup)
- [Demo](#demo)
- [Disclaimer](#disclaimer)
- [License](#license)
- [Support](#support)

</details>
<!-- ⛔️ MD-MAGIC-EXAMPLE:END -->

## Overview

At it's core, the application is a background processes that runs in response to certain events on RoomOS devices.

These processes detect when someone tries to dial into an eligible Zoom CRC meeting and automatically adds a
machine/room account as the host to that Zoom meeting using a Zoom Server to Server Oauth App and Zoom APIs.

It then inputs the machine/room account's host key on the Webex RoomOS device via the DTMF xAPI command.

Of course, this is an over-simplification of the steps involved.

## Setup

These instructions assume that you have:

- Administrator access to an organization's Zoom Dashboard and Webex Control Hub.
- [Docker installed](https://docs.docker.com/engine/install/) and running on a Windows (via WSL2), macOS, or Linux
  machine.

Open a new terminal window and follow the instructions below to setup the project locally for development/demo.

1. Clone this repository and change directory:

   ```
   git clone https://github.com/WXSD-Sales/zoom-auto-host-key && cd zoom-auto-host-key
   ```

2. Copy `.env.example` file as `.env`:

   ```
   cp .env.example .env
   ```

3. Set the `APP_URL` environment variable to your host's secure public url (e.g. https://example.com). You should
   ensure you change the `APP_ENV` environment variable to `production` when running in production environment.

4. Review and follow the [Registering your Integration
   on Webex](https://developer.webex.com/docs/integrations#registering-your-integration) guide.

   - Your registration must have the following [Webex REST API scopes](https://developer.webex.com/docs/integrations#scopes):
     | Scope | Description |
     |-------------------------|-----------------------------------------------|
     | spark-admin:people_read | Access to read your user's company directory |
     | spark:people_read | Access to read your user's company directory |
     | spark:kms | Permission to interact with encrypted content (automatically added to your integration) |
   - Use these Redirect URIs:
     - `<APP_URL>/auth/webex/callback`
     - `http://localhost/auth/webex/callback`
     - `https://localhost/auth/webex/callback`
   - Take note of your Client ID and Client Secret. Assign these values to the `WEBEX_CLIENT_ID`
     and `WEBEX_CLIENT_SECRET` environment variables within the `.env` file respectively.

5. [Install Composer dependencies for the application](https://laravel.com/docs/10.x/sail#installing-composer-dependencies-for-existing-projects):

   ```
   docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
   ```

6. Start the Docker development environment via [Laravel Sail](https://laravel.com/docs/10.x/sail):

   ```
   ./vendor/bin/sail up -d
   ```

7. Generate the [application key](https://laravel.com/docs/10.x/encryption#configuration):

   ```
   ./vendor/bin/sail php artisan key:generate
   ```

8. Initialize the [database for the application](https://laravel.com/docs/9.x/migrations#drop-all-tables-migrate=):

   ```
   ./vendor/bin/sail php artisan migrate:fresh
   ```

9. Install NPM dependencies for the application:

   ```
   ./vendor/bin/sail npm install
   ```

10. Run [Vite](https://laravel.com/docs/10.x/vite):
    ```
    ./vendor/bin/sail npx vite build && npx vite build --ssr
    ```

Lastly, navigate to `http://localhost` or the `<APP_URL>` in your browser to complete the setup by creating
Webex Workspace Integration and Zoom Server to Server application. To stop, execute `./vendor/bin/sail down` on the
terminal.

## Demo

A video where I demo this PoC is available on Vidcast — [https://app.vidcast.io/share/3fe18984-4c85-4edb-9550-cea3d03050e3](https://app.vidcast.io/share/3fe18984-4c85-4edb-9550-cea3d03050e3).

## Disclaimer

Everything included in this repository is for demo and Proof of Concept (PoC) purposes only. Use of the PoC is solely
at your own risk. This project may contain links to external content, which we do not warrant, endorse, or assume
liability for. This project is for Cisco Webex use-case, but is not official Cisco Webex branded project.

## License

[MIT](./LICENSE)

## Support

Please reach out to the WXSD team at [wxsd@external.cisco.com](mailto:wxsd@external.cisco.com?cc=ashessin@cisco.com&subject=Zoom%20Auto%20Host%20Key) or contact me on Webex (ashessin@cisco.com).
