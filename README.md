Semantic Diff
=============

Semantic diffs for PHP code.

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

$diff->getStatus();

/*
 * $diff->getStatus() returns one of:
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
