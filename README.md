# Venture Native Ads

**Client plugin for WordPress** to display rotating native ads from the Venture Native Ad Management host plugin.


## About

WordPress plugin that allows rotating native advertisements on client site. It fetches ads from a central **Venture Native Ad Management** host.

This is the **client-side** plugin. You need a separate **host** installation to manage campaigns and serve ads.

## Features

- **Rotating native ads** with configurable duration
- **Smooth fade transitions**
- **Impression & click tracking**
- **Shortcode integration**
- **Secure communication** via secret key
- **No external dependencies** besides jQuery (included with WordPress)

## Requirements

- The companion **Venture Native Ad Management** host plugin installed on a different site

## Installation

1. Download the latest release or clone this repository
2. Upload the `Venture-Native-Ads` folder to your `/wp-content/plugins/` directory
3. Activate the plugin through the WordPress admin panel

## Configuration

1. Go to **Venture Native Ads** in the WordPress admin sidebar
2. Enter the following settings:

   - **Host Site URL**: Full URL of the site running the Venture Native Ad Management host plugin
   - **Secret Key**: Copy the secret key from the host plugin's settings page
   - **Ad Duration**: How many seconds each ad should be displayed

3. Save changes

## Usage

Use the shortcode:

```shortcode
[venture-native-ads id="YOUR_CAMPAIGN_ID"]
