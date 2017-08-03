# Migration Tool

## Goal

This tool aims to help you to migrate your PIM 1.7 to the new version 2.0 by installing a new fresh PIM with your old data.

## Install

### Requirements for the tool

- php-7.1
- php7.1-gmp
- php7.1-mbstring
- php7.1-json
- php7.1-xml
- composer

### Optional requirements (using SSH)

The tool is able to download your data from an old PIM which could be located on a server. To be able to download you will need to provide
a private ssh-key able to connect to this server.

Moreover, if you want to install an Enterprise Edition, you will also have to provide a private ssh-key able to download this edition located on the akeneo
distribution server.

To conclude about SSH, you will need

- ssh-client
- one or two private ssh keys (distant source pim & enterprise edition installation)

### Additional information

The tool will ask you several paths, please always provide absolute paths.

The tool will never update or make changes on your source pim, we copy everything and this one will always run.

Run the tool !

```
    composer update
    php MigrationTool.php akeneo-pim:migrate
```

The tool will ask you information about your destination PIM, you will have several way to install it:
- You have an empty PIM 2.0 already installed and running, it means you already check the PIM is running correctly.
- We can install it for you with docker and will explain you how to handle it.


## Developer part:

There is a tons of helpers availables through a Makefile, just type `make list`

In addition of usage requirements you need to install `dot` to be able to print the workflow.

Please run CS-fixer, PHPSpec and PHPUnit before committing. (`make commit` if you want to use the Makefile)

```
  $ php ./vendor/bin/php-cs-fixer fix --config=./.php_cs.php
  $ php ./vendor/bin/phpspec run
  $ php ./vendor/bin/phpunit
```

## Troubleshooting

As the test of the parts using SSH are complicated we are not sure that your configuration fits our usage.
