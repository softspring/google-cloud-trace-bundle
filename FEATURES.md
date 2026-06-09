# Google Cloud Trace Bundle Features

Functional definition for `softspring/google-cloud-trace-bundle`.

This file defines the expected behavior and functional scope of the component. It describes what the bundle should add to a Symfony application when Google Cloud Trace is used.

## Purpose

- Send trace spans from Symfony applications to Google Cloud Trace.
- Add tracing to common runtime layers without forcing application code to create spans manually.
- Help teams inspect request flow, kernel work, template rendering, event dispatching, Doctrine activity, and HttpCache behavior from the same trace.

## Main Features

- Detect incoming distributed tracing context through the `traceparent` header.
- Create spans only when trace context is available and sampled.
- Send collected spans to Google Cloud Trace at the end of the request lifecycle.
- Decorate the Symfony HTTP kernel to trace request handling and termination.
- Allow event dispatcher tracing to be enabled when dispatched events need to be inspected.
- Allow Twig template rendering tracing to be enabled when template-level detail is useful.
- Allow Symfony HttpCache tracing to be enabled when HttpCache behavior needs to be inspected.
- Allow Doctrine DBAL activity tracing to be enabled:
  - through Doctrine middleware on newer DBAL versions
  - through the legacy SQL logger decoration on older DBAL versions
- Keep full SQL text disabled by default.
- Provide a `TraceKernelTrait` that applications can use in their kernel when they want to create a top-level server span and send traces on terminate.

## Expected Usage

- Install the bundle in applications that already run on Google Cloud and receive distributed trace context.
- Use it to observe the main request flow without changing every controller or service.
- Enable detailed Twig, event dispatcher, Doctrine, or HttpCache tracing only when the additional spans are worth the runtime and storage cost.
- Combine it with Google Cloud logging and monitoring to investigate slow requests or noisy event and database activity.
- Use the kernel trait when the application kernel should own the top-level server span and final trace submission.

## Instrumented Areas

- Main HTTP kernel handling
- Kernel termination
- Optional event dispatching
- Optional Twig template rendering
- Optional Symfony HttpCache handling and termination
- Optional Doctrine DBAL query activity and related low-level DBAL operations

## Integration Expectations

- The bundle should work without explicit bundle configuration after installation.
- Instrumentation should only activate when the related subsystem exists in the container.
- Detailed instrumentation should be configurable so applications can avoid noisy spans in production.
- Applications without Twig, Doctrine, or HttpCache should still be able to use the bundle safely.
- Applications should be able to keep using their normal Symfony and Doctrine configuration.

## Extension Expectations

- Applications should be able to create their own spans through the `Tracer` utility.
- Applications should be able to decide whether to use the kernel trait or only the service decorators.
- Applications should be able to extend tracing strategy by decorating traced services or adding custom spans around their own domain logic.

## Operational Expectations

- Spans should include useful request and response attributes when that information is available.
- Tracing should stay effectively disabled when there is no incoming trace context.
- Tracing should stay disabled for unsampled `traceparent` contexts.
- The bundle should remain usable across the supported Symfony 6.4, 7.x, and 8.x line.
- The bundle should support both older and newer Doctrine DBAL tracing integration styles.

## Current Limits

- The bundle is focused on request lifecycle tracing, not on generic background job tracing.
- It depends on Google Cloud Trace classes and distributed trace context being available.
- It does not expose a rich configuration layer for naming or filtering spans.
- It does not currently document a formal public API for custom trace exporters or alternative trace clients.
