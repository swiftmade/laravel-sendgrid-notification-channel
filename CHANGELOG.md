# Changelog

All notable changes will be documented in this file

## 2.2.0 - 2022-09-24

-   You can now pass nested arrays, numbers, bools as the payload. Previously, the library only accepted strings. (Thanks [timstl](https://github.com/swiftmade/laravel-sendgrid-notification-channel/pull/7))
-   Added `setSandboxMode($bool)` method on the `SendGridMessage` object, so you can now control sandbox mode using a variable.

## 2.1.0 - 2022-08-12

-   Added support for Sentry SDK v8
-   You can now enable sandbox mode while sending emails. (Thanks [@zbrody](https://github.com/swiftmade/laravel-sendgrid-notification-channel/pull/3))

## 2.0.0 - 2022-04-07

-   Added support for Laravel 8 and 9.

## 1.0.1 - 2020-08-11

-   stable release for Laravel 5, 6 and 7.

## 0.0.4 - 2020-08-11

-   initial release
