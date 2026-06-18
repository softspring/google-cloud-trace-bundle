# Google Cloud Trace Bundle

[![Latest Stable](https://img.shields.io/packagist/v/softspring/google-cloud-trace-bundle?label=stable&style=flat-square)](https://github.com/softspring/google-cloud-trace-bundle/releases)
[![Latest Unstable](https://img.shields.io/packagist/v/softspring/google-cloud-trace-bundle?label=unstable&style=flat-square&include_prereleases)](https://github.com/softspring/google-cloud-trace-bundle/releases)
[![License](https://img.shields.io/packagist/l/softspring/google-cloud-trace-bundle?style=flat-square)](https://github.com/softspring/google-cloud-trace-bundle/blob/6.0/LICENSE)
[![PHP Version](https://img.shields.io/packagist/dependency-v/softspring/google-cloud-trace-bundle/php?style=flat-square)](https://github.com/softspring/google-cloud-trace-bundle/blob/6.0/composer.json)
[![Downloads](https://img.shields.io/packagist/dt/softspring/google-cloud-trace-bundle?style=flat-square)](https://packagist.org/packages/softspring/google-cloud-trace-bundle)
[![CI](https://img.shields.io/github/actions/workflow/status/softspring/google-cloud-trace-bundle/ci.yml?branch=6.0&style=flat-square&label=CI)](https://github.com/softspring/google-cloud-trace-bundle/actions/workflows/ci.yml)
[![Coverage](https://img.shields.io/codecov/c/github/softspring/google-cloud-trace-bundle?branch=6.0&style=flat-square)](https://app.codecov.io/gh/softspring/google-cloud-trace-bundle/tree/6.0)

This bundle sends Symfony request traces to Google Cloud Trace.

By default, it traces the main Symfony kernel flow only. More detailed instrumentation is opt-in to avoid adding noisy spans or unnecessary runtime overhead in production.

## Configuration

```yaml
sfs_google_cloud_trace:
    enabled: true
    instrumentation:
        kernel: true
        event_dispatcher: false
        twig: false
        doctrine: false
        http_cache: false
    doctrine:
        include_sql: false
```

Enable `twig`, `event_dispatcher`, `doctrine`, or `http_cache` only when that detail is useful for the application. Keep `doctrine.include_sql` disabled unless query text is needed and safe to send to Google Cloud Trace.

## Armonic

This package is part of [Armonic](https://softspring.es/en/armonic).

## Documentation

[Armonic Documentation](https://armonic.softspring.es/latest/components/google-cloud-trace)

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

[Report issues](https://github.com/softspring/google-cloud-trace-bundle/issues) and [send Pull Requests](https://github.com/softspring/google-cloud-trace-bundle/pulls)

## License

This package is free and released under the [AGPL-3.0 license](LICENSE).
