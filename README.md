# DynamicScheduleBundle
Manage dynamic schedule tasks

This bundle just add a storage layer using Doctrine to manage schedule tasks.

It use Symfony scheduler, lock and cache.

## Usage


### Install bundle


### Create your scheduler provider

Add in your `service.yaml`:

```
services:
  MyScheduleProvider:
    class: Beerfranz\DynamicScheduleBundle\Service\DynamicScheduleProvider
    arguments:
      $scheduleName: 'my'
    tags:
      - scheduler.schedule_provider: { name: 'my' }
```

### Consume your scheduler

```
php bin/console messenger:consume scheduler_<name>
```


## Bootstrap

```
$ docker run --rm -v $PWD:/app -u 1000:1000 composer install
$ docker run --rm -v $PWD:/app -u 1000:1000 composer update
```

Run test:
```
$ docker compose run php bin/console doctrine:schema:update --force
$ docker compose up php
```
