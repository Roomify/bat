# BAT

[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

BAT stands for Booking and Availability Management Tools.

It is a set of tools created by the [Roomify.us](https://roomify.us) team to provide a foundation through which a wide range of availability management, reservation and booking use cases can be addressed.
BAT will work with a variety of CMSs and PHP Frameworks, of which the first is Drupal (check out our [Drupal module](https://github.com/roomify/bat_drupal)).

BAT builds on our experience with [Rooms](http://drupal.org/project/rooms), which handles the problem of bookings specifically for the accommodation for rental use case(vacation rentals, hotels, B&B, etc). With BAT we took everything we learned and build a system that will let you build an application like Rooms - or something for table booking at a restaurant, or conference room bookings, or sharing power tools with friends, or booking activities, or... well you get the idea.

BAT on its own is a **booking and availability management framework** - much in the same way Drupal is a content management framework or Drupal Commerce is an e-commerce framework. Our aim is to build specific solutions on top of BAT to tackle specific application domains.


## Basic Concepts

### Units

Units are the things that can be booked. For BAT they simply have an id, a default state (for a given event type - we will get to this later) and can define Constraints. Constraints are like extra rules about whether a specific unit is available (we get back to these as well).

For each application units will represent something concrete like hotel rooms, cars, tables, etc.

### Events

Events define what *value* a unit has for a given time period. There can be multiple types of events and the value of the event together with the type of event will provide some meaning within an application. 

For example, one set of events can denote "Availability", while another "Price". The value of events of type Availability will indicate whether a unit is available (1), unavailable (0) or booked (2) - i.e they indicate the state of a unit. The value of events of type Price could denote instead the cost per night to change the state of a unit for a given time period. So to make Unit 1 change state from available to booked for a given set of days you can retrieve all pricing events for that set of days and multiple the number of nights by the value associated with that event. 

### Calendar

A Calendar allows us to retrieve Events of a given type for a given set of Units as well as search over Units to see the ones that match specific event values.

You can, for example, use a Calendar to find all Units that from Jan 1 2016 to Jan 15 2016 have Availability Events that only hold value of 2 - which given our definition above - would indicate booked units.

#### CalendarResponse

A search using the Calendar will return a CalendarResponse - this will indicate for each unit that was involved in the search whether it is part of the *included* units or the *excluded* units together with the reason it ended up in one or the other set. This allows our applications to reason about why something didn't make the cut and display it to the end user.

### Constraints

When a Calendar does a search it does it for a given time range and a valid set of values. We can identify further Constrainers either at the global level or at the Unit level. For example a specific Unit may indicate that it will only make itself available if the range search starts on a Monday, or it is of at least 7 days, etc. 

The Calendar Response will hold information about which Constraint moved a Unit from the included set to the excluded set.

### Valuator

A Valuator performs an operation on event values to determine the value of a Unit for a given period given a specific valuation strategy. The simplest case for hotels would be to sum up the cost per night. Our applications can define multiple valuators and refer to different EventTypes to cater for a range of valuation strategies. 

### Store

Store stores the value of a unit for a given moment in time. The Store goes down to minute granularity which means our Units can have a different value for each minute in time. The Store data structure is designed to quickly allows us to determine the value of a unit for a given time range and quickly change it. 

Currently we support a SQLite store (used in our tests) and a DrupalStore. Additional Store support is on its way. 


## Install

Via Composer

``` bash
$ composer require Roomify/Bat
```

## Usage

Create a unit with ID 1, default value 1 and a minimum length of event constraint.

``` php
$constraint = new MinMaxDaysConstraint([], 5)

$unit = new Unit(1,1, array($constraint)); 
```

Create a Store for events of type availability and pricing, create an event for Unit 1 and save it

``` php
$state_store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

$start_date = new \DateTime('2016-01-01 12:12');
$end_date = new \DateTime('2016-01-04 07:07');

$state_event = new Event($start_date, $end_date, $unit, 0); \\ Event value is 0 (i.e. unavailable)

$state_calendar = new Calendar(array($unit), $state_store);
$state_calendar->addEvents(array($state_event), Event::BAT_HOURLY); \\ BAT_HOURLY denotes granularity
```

We can then search for matching Units. In this case we are searching for all units from date $s1 to date $s2 that have events only of value 1 (Available). Given that our unit in that period also has a value of 0 our calendar will not find any matching units.

``` php
$s1 = new \DateTime('2016-01-01 00:00');
$s2 = new \DateTime('2016-01-31 12:00');

$response = $state_calendar->getMatchingUnits($s1, $s2, array(1), array());
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email hello@roomify.us instead of using the issue tracker.

## Credits

- [Roomify](https://roomify.us)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/Roomify/bat/master.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/roomify/bat.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/roomify/bat.svg?style=flat-square

[link-travis]: https://travis-ci.org/Roomify/bat
[link-scrutinizer]: https://scrutinizer-ci.com/g/roomify/bat/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/roomify/bat
[link-downloads]: https://packagist.org/packages/roomify/bat
[link-author]: https://github.com/roomify
