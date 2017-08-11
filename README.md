# Migration Tool

## Goal

This tool aims to help you to migrate your *PIM 1.7 standard edition* (either _Community_ or _Enterprise_) to the new version 2.0. All your data will be migrated seamlessly. Your source PIM won't be updated nor touched. Instead, we'll perform the migration in a brand new PIM 2.0. Several reasons led us to this choice:
- the [System Requirements](TODO) have changed between Akeneo PIM 1.7 and 2.0
- with variant groups and inner variation (a paid extension for the _Enterprise Edition_) there are a lot of different and complex use cases to migrate products data
- the need to migrate real production data without worrying about a failure, a problem or an incomplete migration

The 1.7 source PIM you will migrate from can be either installed locally or remotely. 

The 2.0 destination PIM you will migrate to should be installed locally. If that's not the case, don't worry, we'll do it for you with [Docker](https://www.docker.com/).

Before proceeding, we strongly encourage you to read our documentation and our functional and technical blog posts about the version 2.0:
- [System Requirements](TODO), documentation
- [Community Edition BC Breaks](TODO), documentation
- Enterprise Edition BC Breaks (check out the file `CHANGELOG-2.0.md` provided at the root of your archive), documentation
- [Story of Storage](https://medium.com/akeneo-labs/story-of-storage-9dbc27090de0), technical blog post
- [Single Product Storage?](https://medium.com/akeneo-labs/single-product-storage-28d92f35cbd7), technical blog post
- [Re-building the storage from the ground up](https://medium.com/akeneo-labs/re-building-the-storage-from-the-ground-up-d857bf497c32), technical blog post
- [Offer choice with variants!](TODO), functional blog post
- [How Akeneo deals products with variants?](TODO), functional blog post
- [third article](TODO), functional blog post

### Access to remote servers

If the 1.7 source PIM you will migrate from is installed remotely, you'll be asked to provide a *private SSH key* able to connect to this server.

Moreover, if you migrate an _Enterprise Edition_, you'll be asked to provide a *private SSH key* able to download this edition located on the _Akeneo distribution server_. This *private SSH key* should match the one you have provided in the [Partners Portal](https://partners.akeneo.com/login).

## Installation

### System requirements

- php7.1
- php7.1-gmp
- php7.1-mbstring
- php7.1-json
- php7.1-xml
- [composer](https://getcomposer.org/download/)
- a SSH client
- [Docker](https://www.docker.com/), in case you want to automatically install the destination PIM 2.0

### Install the tool and its dependencies

```bash
  $ git clone https://github.com/akeneo/migration-tool.git
  $ composer.phar install
```

## How to use

To launch the tool, run:

```bash
  $ php MigrationTool.php akeneo-pim:migrate
```

then, let you guide ;) 

## How to contribute

Please, have a look on the [CONTRIBUTING](./.github/CONTRIBUTING.md) page.
