Semantic Diff for PHP
=====================

[![Build Status](https://img.shields.io/travis/joshdifabio/semantic-diff.svg?style=flat)](https://travis-ci.org/joshdifabio/semantic-diff) [![Coveralls](https://img.shields.io/coveralls/joshdifabio/semantic-diff.svg?style=flat)](https://coveralls.io/r/joshdifabio/semantic-diff) [![Codacy Badge](https://img.shields.io/codacy/5e498265acf942d9b437b362247b0145.svg?style=flat)](https://www.codacy.com/public/joshdifabio/semantic-diff)

API status
----------

Until the first tag is created, this package should be considered very unstable.

Usage
-----

```php
use PhpParser\Parser;
use PhpParser\Lexer;
use SemanticDiff\Diff\Factory;
use SemanticDiff\Status;

$phpParser = new Parser(new Lexer);

$diff = (new Factory)->createDiff(
    $phpParser->parse($oldPhpCode),
    $phpParser->parse($newPhpCode)
);

$status = $diff->getStatus();

/*
 * $status is now one of:
 *  Status::NO_CHANGES
 *  Status::API_ADDITIONS
 *  Status::INTERNAL_CHANGES
 *  Status::API_CHANGES
 *  Status::INCOMPATIBLE_API
 */
```

License
-------

Semantic Diff is released under the [MIT](https://github.com/joshdifabio/semantic-diff/blob/master/LICENSE) license.
