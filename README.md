# Migration Tool

## Goal

This tool aims to help you to migrate your PIM 1.7 to the new version 2.0. All your data will be migrated seamlessly.

## Installation

### System Requirements

- php-7.1
- php7.1-gmp
- php7.1-mbstring
- php7.1-json
- php7.1-xml
- [composer](https://getcomposer.org/download/)
- a ssh-client

The source PIM you will migrate from can be either in local, either in a server. To be able to download you will need to provide
a private ssh-key able to connect to this server.

Moreover, if you want to install an Enterprise Edition, you will also have to provide a private ssh-key able to download this edition located on the akeneo
distribution server.

To conclude about SSH, you will need

- one or two private ssh keys (distant source pim & enterprise edition installation)


### Install the tool and its dependencies

```
  $ git clone https://github.com/akeneo/migration-tool.git
  $ composer install
```

## How to use

To run the tool, you just have to run:

```
    php MigrationTool.php akeneo-pim:migrate
```

The tool will ask you several paths, please always provide absolute paths.

The tool will never update or make changes on your source pim, we copy everything and this one will always run.

The tool will ask you information about your destination PIM, you will have several way to install it:
- You have an empty PIM 2.0 already installed and running, it means you already check the PIM is running correctly.
- We can install it for you with docker and will explain you how to handle it.

## How to contribute:

Please, have a look on the [CONTRIBUTING](./.github/CONTRIBUTING.md) page.
