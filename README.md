# Migration Tool

**While the tool is not stable we do not recommend to use it to setup a Production PIM.**

## Goal

This tool aims to help you to migrate your *PIM 1.7 standard edition* (either _Community_ or _Enterprise_) to the new version 2.0. All your data will be migrated seamlessly. Your source PIM won't be updated nor touched. Instead, we'll perform the migration in a brand new PIM 2.0. Several reasons led us to this choice:
- the [System Requirements](https://docs.akeneo.com/2.0/install_pim/system_requirements/system_requirements.html) have changed between Akeneo PIM 1.7 and 2.0
- with variant groups and inner variation (a paid extension for the _Enterprise Edition_) there are a lot of different and complex use cases to migrate products data
- the need to migrate real production data without worrying about a failure, a problem or an incomplete migration

The 1.7 source PIM you will migrate from can be either installed locally or remotely. 

The 2.0 destination PIM you will migrate to should be installed locally running on the port 80, you can install it following these [instructions](https://docs.akeneo.com/latest/install_pim/manual/system_requirements/system_requirements.html).
We do not support the Docker installation yet regarding Transporteo.

Both PIM should be functionnals and have a functionnal API with admin rights.

Before proceeding, we strongly encourage you to read our documentation and our functional and technical blog posts about the version 2.0:
- [System Requirements](https://docs.akeneo.com/2.0/install_pim/system_requirements/system_requirements.html), documentation
- [Community Edition BC Breaks](https://github.com/akeneo/pim-community-dev/blob/master/CHANGELOG-2.0.md), documentation
- Enterprise Edition BC Breaks (check out the file `CHANGELOG-2.0.md` provided at the root of your archive), documentation
- [Story of Storage](https://medium.com/akeneo-labs/story-of-storage-9dbc27090de0), technical blog post
- [Single Product Storage?](https://medium.com/akeneo-labs/single-product-storage-28d92f35cbd7), technical blog post
- [Re-building the storage from the ground up](https://medium.com/akeneo-labs/re-building-the-storage-from-the-ground-up-d857bf497c32), technical blog post
- [Offer choice with variants!](https://medium.com/akeneo-labs/offer-choice-with-variants-8460a82fa36), functional blog post
- [How Akeneo deals products with variants?](https://medium.com/akeneo-labs/how-does-akeneo-deal-with-variants-42bcab83a879), functional blog post


### Access to remote servers

If the 1.7 source PIM you will migrate from is installed remotely, you'll be asked to provide a *private SSH key* able to connect to this server.

## Installation

### System requirements

- php7.1
- php7.1-gmp
- php7.1-mbstring
- php7.1-json
- php7.1-xml
- [composer](https://getcomposer.org/download/)
- a SSH client

And the same requirements as the PIM as you need a PIM installed on your computer ([instructions](https://docs.akeneo.com/latest/install_pim/manual/system_requirements/system_requirements.html)).
As we don't use Elasticsearch in Transporteo, you can install it the way you want.

### Install the tool and its dependencies

```bash
  $ composer.phar create-project "akeneo/transporteo":"dev-master"
```

## How to use

To launch the tool, run:

```bash
  $ php Transporteo.php akeneo-pim:migrate
```

then, let you guide ;) 

At the end of the tool's job, you will have your database setuped with your data but we do not migrate your custom code.
You will have to migrate it following this [upgrade file](./UPGRADE-2.0.md).

We plan to automate this part in future release, stay tuned ! :)

## How to contribute

Please, have a look on the [CONTRIBUTING](./.github/CONTRIBUTING.md) page.

## What's next ?

We will continuously improve this tool, you can follow our plans [here](https://github.com/akeneo/transporteo/projects/1).

You can also have a look on the [changelog](./CHANGELOG.md).
