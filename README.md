# woo-dimension-check

woo-dimension-check is a library that helps to identify WooCommerce products with no weight, length, width, or height.

## Installation

`composer require ckreidl/woo-dimension-check`

## Usage

```
use ckreidl\WooDimensionCheck;

$url = 'https://your_store_url.com';
$key = 'consumer_key';
$secret = 'consumer_secret';

$missing = new WooDimensionCheck\DimChecker($url, $key, $secret);
```

## Example
```
$missing = new WooDimensionCheck\DimChecker($url, $key, $secret);

print "There are " . count($missing->weight()) . " items missing weights. They are: \n";
array_walk($missing->weight(), function($prod) { print "{$prod->id} "; }); print "\n";
```

## Background

I ran into some issues where the shipping calculators we were using wouldn't play nicely if items didn't have weights and/or measures entered. For example:

1. Customer adds widget A to their cart, which doesn't have dimensions or a weight listed
2. Customer proceeds to checkout
3. Shipping calculator, not knowing the size or weight of widget A, declines to show them shipping options

Meanwhile, another customer adds the same widget A as well as widget B, which does have weight & dimensions

1. Customer adds widget A to their cart, with no weight or dims
2. Customer adds widget B, with weight & dims
3. Shipping calculator defaults to 0 for widget A, solely calculating shipping based off weight and dims of widget B

We can go back and forth all day about what the default behavior should be when an item without this information is encountered. It's my opinion that the database should have that information, so I wrote this little tool to identify items that don't have that data entered.

## License
[GPL 3.0](https://opensource.org/licenses/GPL-3.0)