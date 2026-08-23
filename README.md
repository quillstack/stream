# Quillstack Stream

[![Tests](https://github.com/quillstack/stream/actions/workflows/tests.yml/badge.svg)](https://github.com/quillstack/stream/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/quillstack/stream.svg)](https://packagist.org/packages/quillstack/stream)
[![Downloads](https://img.shields.io/packagist/dt/quillstack/stream.svg)](https://packagist.org/packages/quillstack/stream)
[![PHP Version](https://img.shields.io/packagist/php-v/quillstack/stream)](https://packagist.org/packages/quillstack/stream)
[![StyleCI](https://github.styleci.io/repos/301132689/shield?branch=main)](https://github.styleci.io/repos/301132689?branch=main)
[![CodeFactor](https://www.codefactor.io/repository/github/quillstack/stream/badge)](https://www.codefactor.io/repository/github/quillstack/stream)
[![Quality Gate](https://sonarcloud.io/api/project_badges/measure?project=quillstack_stream&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=quillstack_stream)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=quillstack_stream&metric=coverage)](https://sonarcloud.io/summary/new_code?id=quillstack_stream)
[![Maintainability](https://sonarcloud.io/api/project_badges/measure?project=quillstack_stream&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=quillstack_stream)
[![Reliability](https://sonarcloud.io/api/project_badges/measure?project=quillstack_stream&metric=reliability_rating)](https://sonarcloud.io/summary/new_code?id=quillstack_stream)
[![Security](https://sonarcloud.io/api/project_badges/measure?project=quillstack_stream&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=quillstack_stream)
[![License](https://img.shields.io/packagist/l/quillstack/stream)](https://github.com/quillstack/stream/blob/main/LICENSE)

The simple implementation of [PSR-7: Stream](https://www.php-fig.org/psr/psr-7/). Full
documentation: https://quillstack.org/stream

A request has a body, and so does a response. Sometimes that body is a file on disk, sometimes
it is whatever was sent to the process, and very often there is none at all. Three streams
rather than one, so the empty case costs nothing and the reading cases are honest about what
they can and cannot do.

## Why this exists

Most PSR-7 implementations have one stream class, built over a PHP resource — usually
`php://temp`, which spills to disk once it grows past a couple of megabytes. It handles
everything, which means every string you wrap goes through `fwrite`, `fseek` and `ftell` to be
read back.

This has four small ones instead, each doing a single thing: a string being read, a file being
read, a body being sent, and nothing at all. **A body that is already a string in memory does
not need a file handle to be read back**, and most bodies in an API are already a string in
memory — see the [benchmark](#benchmark).

A file still gets a file: `FileStream` reads a nineteen-megabyte body without loading it, the
same as everybody else's does.

## Requirements

- PHP 8.1 or newer

## Installation

```shell
composer require quillstack/stream
```

## Usage

### The body of a request

`InputStream` reads `php://input`, which is what a request arrives in:

```php
use Quillstack\Stream\InputStream;

$stream = new InputStream();
$body = (string) $stream;
```

### A file

```php
use Quillstack\Stream\FileStream;

$stream = new FileStream('/var/www/files/report.pdf');

$stream->getSize();      // bytes
$stream->read(1024);
$stream->rewind();
$stream->getContents();
```

The handle is closed when the stream is, and when it goes out of scope.

### A body being sent

```php
use Quillstack\Stream\TextStream;

$stream = new TextStream(json_encode(['hello' => 'world']));
```

`InputStream` does the same thing, but it is named for where a request arrives from and a
body being sent somewhere is not that.

### No body at all

```php
use Quillstack\Stream\EmptyStream;

$stream = new EmptyStream();

(string) $stream;        // ''
$stream->getSize();      // 0
$stream->eof();          // true
```

Nothing is opened, so a response which has no body pays nothing for having one.

### Reading

`read($length)` takes what was asked for and leaves the rest; `getContents()` is what is left
of the stream from where the reader has got to; `__toString()` is always the whole thing:

```php
$stream = new TextStream('abcdef');

$stream->read(3);        // 'abc'
$stream->tell();         // 3
$stream->getContents();  // 'def'
$stream->eof();          // true
(string) $stream;        // 'abcdef', wherever the reader is
```

Reaching for a body twice is what `__toString()` is for — reading the contents twice reads
them once, which is what PSR-7 means by it.

A stream over a string is seekable, because a string held in memory is.

## Technical documentation

All three implement `Psr\Http\Message\StreamInterface`, so anything taking a PSR-7 stream
takes any of them.

| Class | What it reads |
| --- | --- |
| `InputStream` | `php://input` — the body of the request being handled, or a string given to it |
| `TextStream` | a string somebody already has, on its way out |
| `FileStream` | a file on disk, opened when the stream is built |
| `EmptyStream` | nothing; every read is empty and every size is zero |

Asking a stream for something it cannot do throws rather than answering wrongly:

| Exception | Thrown when |
| --- | --- |
| `StreamNotReadableException` | reading a stream which is not readable |
| `StreamNotWritableException` | writing to a stream which is not writable |
| `StreamNotSeekableException` | seeking a stream which cannot seek |

All three extend `StreamException`, so one `catch` covers the lot.

## Benchmark

Measured with [quillstack/benchmark](https://github.com/quillstack/benchmark) on a body of 880
bytes — the size of a small JSON response — created and read back in full. Runs are interleaved,
each figure is the median of five, and PHP is 8.5.7.

| | Version |
| --- | --- |
| quillstack/stream | v0.8.0 |
| nyholm/psr7 | 1.8.2 |
| guzzlehttp/psr7 | 2.13.0 |
| laminas/laminas-diactoros | 3.8.0 |

| | Per body | Relative | Memory |
| --- | --- | --- | --- |
| **quillstack/stream** | **1.12 µs** | — | 28 kB |
| nyholm/psr7 | 5.81 µs | 5.2× | 56 kB |
| guzzlehttp/psr7 | 6.92 µs | 6.2× | 116 kB |
| laminas/laminas-diactoros | 7.99 µs | 7.1× | 47 kB |

**The five-fold difference is the design, not the code.** The other three write the string into
a `php://temp` resource and read it back out through the filesystem layer; this one keeps it as
a string, because it already was one. Their single class covers files, sockets and strings
alike; here a file gets `FileStream`, which behaves exactly as theirs do:

| Reading the first 16 bytes of a 19 MB file | Peak memory |
| --- | --- |
| quillstack/stream, `FileStream` | 730 kB |
| nyholm/psr7 | 731 kB |
| laminas/laminas-diactoros | 730 kB |
| guzzlehttp/psr7 | 781 kB |

Nobody loads the file. **What this trades away is one class that does everything** — if you need
a stream over a socket, a compressed resource or anything else PHP can open, theirs takes it and
this one does not.

## Tests

```shell
composer test
```

## The rest of Quillstack

This is one component of [Quillstack](https://github.com/quillstack), a PHP framework which is
as simple to use as it is strict about what it does.

- [quillstack/response](https://github.com/quillstack/response) — what carries one back
- [quillstack/server-request](https://github.com/quillstack/server-request) — what carries one in
- [quillstack/http-client](https://github.com/quillstack/http-client) — what sends one out
- [quillstack/uri](https://github.com/quillstack/uri) — the other half of a PSR-7 message

## License

MIT. See [LICENSE](LICENSE).
