# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased 2.1]

## Version 2.0.0

### Removed

- The current request will no longer be passed to controllers generating the shortcode substitution (see #15). For shortcodes being handled `inline`, use the `RequestStack` directly in the substituting controller method to obtain the parent request. For `esi`-based renderers, there is no current solution provided by this bundle (see #14). Check your shortcode controllers for access to the request attribute named `request` to find affected places.

### Changed

- Logging will now by default be directed to the `shortcode` channel, instead of the `app` channel used previously.
- Slightly reduced the logging level in `EmbeddedShortcodeHandler`.
