# The Code Registry - Code Backup & Intelligence

Contributors: thecoderegistry
Tags: code analysis, security, vulnerabilities, code quality, performance
Requires at least: 5.0
Tested up to: 6.6.1
Stable tag: 1.0.9
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Backup your code and analyze security vulnerabilities, third-party component usage, licensing issues, code quality and more with The Code Registry.

## Description

The Code Registry - Code Backup & Intelligence plugin connects your WordPress site to our code intelligence and analysis service. It securely replicates your site's code for analysis, providing insights on code complexity, security vulnerabilities, third-party components, licensing issues, and code quality.

### Key Features

- Secure code replication for analysis
- Code complexity assessment
- Security vulnerability detection
- Third-party component identification
- License compliance checking
- Code quality evaluation
- Integration with The Code Registry's web application for advanced features

### How It Works

1. The plugin securely backs up and replicates your site's code.
2. Our service analyzes the code for any issues and generates AI-powered insights.
3. Results are displayed in your WordPress admin dashboard.
4. Detailed reports are available in the dashboard and as downloadable PDFs.

### Free vs. Paid Features

- The plugin and code analysis features are free to use indefinitely.
- All features are available during a 14-day evaluation period.
- Some advanced features are only accessible through our main web app which you will have access to.
- Automated monthly code re-analysis works during your trial and then requires a paid subscription.
- Existing data and basic features remain accessible after the evaluation period.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/the-code-registry-code-backup-intelligence` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Code Backup & Intelligence menu item in the WordPress admin panel to start your first analysis.

## Internationalization

This plugin is internationalized and uses the text domain the-code-registry-code-backup-intelligence. If you're interested in translating the plugin to your language, you can use this text domain with the WordPress translation tools.

To generate a POT file for translations, you can use the following WP-CLI command:

`wp i18n make-pot . languages/the-code-registry-code-backup-intelligence.pot --domain=the-code-registry-code-backup-intelligence`

Please ensure that all translatable strings in the plugin use this text domain for proper internationalization.

## Source Code and Build Process

This plugin uses npm and webpack to compile and minify its JavaScript and CSS files. The compiled files are located in the admin/js/dist and admin/css/dist directories.

For developers interested in reviewing or contributing to the source code:

1. The uncompiled source files are located in the src directory.

2. To set up the development environment:

`npm install`

3. To build the project:

`npm run build`

4. To watch for changes and rebuild automatically:

`npm run watch`

The build process uses Vite for bundling and optimization. The configuration can be found in vite.config.js.

We welcome contributions and encourage developers to review and adapt our code to push WordPress development forward.

## Frequently Asked Questions

### Is my code safe?

Yes, we prioritize security. Your code is transmitted securely, analyzed on our protected servers, and not stored after analysis. We comply with strict data protection standards.

### How long does the analysis take?

Initial setup typically takes about 5 minutes. The analysis itself usually completes in around 15 minutes for average-sized sites, though larger sites may require more time.

### Can I delete my account and data?

Yes, you can easily delete your account and all associated data at any time through the plugin interface.

### How often is my code analyzed?

During the free trial and with an active subscription, your code is automatically re-analyzed monthly. Without a subscription, you can still access previous analysis results.

## Changelog

### 1.0.9
* Expand security issue insighst to be more useful specifically for WordPress. Fix layout issue with AI insights.

### 1.0.8
* Update code slightly to ignore compiled and build files as they skew the accuracy of the results.

### 1.0.7
* Fixed various incompatibilities with Elementor and other plugins.

### 1.0.6
* Fixed a layout issue due to too many detected third party licenses. Swapped Apachje Echarts for Chart.js in the AI Quotient widgets due to compatibility issues.

### 1.0.5
* Added some additional user input sanitisation checks.

### 1.0.4
* Fixed some incorrect calls to gettext functions.

### 1.0.3
* Applied improvements and fixes suggested by the WordPress.org plugin review team.

### 1.0.2
* Fixed issue which caused our CSS and JS to load on ALL admin pages, not just ours. Decreased plugin short description length.

### 1.0.1
* Fixed a bug where the API kept being polled for data even though all the code analysis was complete.

### 1.0.0
* Initial release of The Code Registry - Code Backup & Intelligence plugin.

## Privacy Policy

This plugin securely transmits your site's code to The Code Registry's servers for analysis. We do not store your complete codebase after analysis. For full details on how we handle your data, please view our [Privacy Policy](https://thecoderegistry.com/privacy-policy).

## Additional Information

For more information about our services, please visit [The Code Registry](https://thecoderegistry.com).