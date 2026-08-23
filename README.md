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

### Requirements

- PHP 8.1 or newer

### Installation

```shell
composer require quillstack/stream
```

### Usage

#### The body of a request

`InputStream` reads `php://input`, which is what a request arrives in:

```php
use Quillstack\Stream\InputStream;

$stream = new InputStream();
$body = (string) $stream;
```

#### A file

```php
use Quillstack\Stream\FileStream;

$stream = new FileStream('/var/www/files/report.pdf');

$stream->getSize();      // bytes
$stream->read(1024);
$stream->rewind();
$stream->getContents();
```

The handle is closed when the stream is, and when it goes out of scope.

#### No body at all

```php
use Quillstack\Stream\EmptyStream;

$stream = new EmptyStream();

(string) $stream;        // ''
$stream->getSize();      // 0
$stream->eof();          // true
```

Nothing is opened, so a response which has no body pays nothing for having one.

### Technical documentation

All three implement `Psr\Http\Message\StreamInterface`, so anything taking a PSR-7 stream
takes any of them.

| Class | What it reads |
| --- | --- |
| `InputStream` | `php://input` — the body of the request being handled |
| `FileStream` | a file on disk, opened when the stream is built |
| `EmptyStream` | nothing; every read is empty and every size is zero |

Asking a stream for something it cannot do throws rather than answering wrongly:

| Exception | Thrown when |
| --- | --- |
| `StreamNotReadableException` | reading a stream which is not readable |
| `StreamNotWritableException` | writing to a stream which is not writable |
| `StreamNotSeekableException` | seeking a stream which cannot seek |

All three extend `StreamException`, so one `catch` covers the lot.

### Unit tests

```shell
composer test
```

### Docker

```shell
docker-compose up -d
docker exec -w /var/www/html -it quillstack_stream sh
```

### License

MIT. See [LICENSE](LICENSE).
