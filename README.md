# Next.js for Drupal on Pantheon

[![Drupal](https://github.com/chapter-three/pantheon-next/actions/workflows/drupal.yml/badge.svg)](https://github.com/chapter-three/pantheon-next/actions/workflows/drupal.yml)

This module provides integration for developing decoupled Drupal and Next.js sites on Pantheon.

## Installation

1. Add the `next_for_drupal_pantheon` module to your Drupal site.

```bash
composer require drupal/next_for_drupal_pantheon
```

2. Visit `/admin/modules` and enable the **Next.js for Pantheon** module.

Once you've enabled the module, you can visit `/admin/config/services/next/pantheon` to run the installer.

The installer will configure a new Next.js site and the required variables for running the site on Pantheon.

3. Visit `/admin/config/services/next/pantheon`.
4. Click **Run Installer**.

## Pantheon Configuration

To connect your Next.js site to Drupal, you need to add the required environment variables on the Pantheon Dashboard.

1. On your Drupal side, visit `/admin/config/services/next/pantheon`.
2. Click **Environment Variables** under **Operations**.
3. Click **Generate New Secret**.
4. Open your site's dashboard on Pantheon and visit Settings → Build.
5. Copy and paste the environment variables from Drupal.
6. Trigger a new build for your Next.js site on Pantheon.
