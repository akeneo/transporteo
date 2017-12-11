# Transporteo

## Goal

This tool aims at helping you to migrate your *PIM 1.7 standard edition* (either _Community_ or _Enterprise_) to the new version 2.0. All your data will be migrated seamlessly. Your source PIM won't be updated nor touched. Instead, we'll perform the migration in a brand new PIM 2.0. Several reasons led us to this choice:
- the [System Requirements](https://docs.akeneo.com/2.0/install_pim/manual/system_requirements/system_requirements.html) have changed between Akeneo PIM 1.7 and 2.0
- with variant groups and inner variation (a paid extension for the _Enterprise Edition_) there are a lot of different and complex use cases to migrate products data
- the need to migrate real production data without worrying about a failure, a problem or an incomplete migration

The 1.7 source PIM you will migrate from can be either installed locally or remotely. 

The 2.0 destination PIM you will migrate to should be installed locally running on the port 80, you can install it following these [instructions](https://docs.akeneo.com/latest/install_pim/manual/index.html).
We do not support the Docker installation yet regarding Transporteo.
The minimum version of the destination PIM is 2.0.3.

Both PIM should be functionnal and have a functionnal API with admin rights.

Before proceeding, we strongly encourage you to read our documentation and our functional and technical blog posts about the version 2.0:
- [System Requirements](https://docs.akeneo.com/2.0/install_pim/manual/system_requirements/system_requirements.html), documentation
- [Community Edition BC Breaks](https://github.com/akeneo/pim-community-dev/blob/master/CHANGELOG-2.0.md), documentation
- Enterprise Edition BC Breaks (check out the file `CHANGELOG-2.0.md` provided at the root of your archive), documentation
- [Story of Storage](https://medium.com/akeneo-labs/story-of-storage-9dbc27090de0), technical blog post
- [Single Product Storage?](https://medium.com/akeneo-labs/single-product-storage-28d92f35cbd7), technical blog post
- [Re-building the storage from the ground up](https://medium.com/akeneo-labs/re-building-the-storage-from-the-ground-up-d857bf497c32), technical blog post
- [Offer choice with variants!](https://medium.com/akeneo-labs/offer-choice-with-variants-8460a82fa36), functional blog post
- [How Akeneo deals products with variants?](https://medium.com/akeneo-labs/how-does-akeneo-deal-with-variants-42bcab83a879), functional blog post

## Scope

This tool has a dedicated release cycle and we're regularly releasing improvements in new versions.

We count on your feedback to continue to improve it in attempt to cover all your migration needs, don't hesitate to open issues describing your cases.

### Data Migration

Edition    | Model                    | Version      |
---------- | ------------------------ | ------------ |
Community  | Association type         | 1.0.0-alpha1 |
Community  | Attribute                | 1.0.0-alpha1 |
Community  | Attribute Group          | 1.0.0-alpha1 |
Community  | Categories               | 1.0.0-alpha1 |
Community  | Family                   | 1.0.0-alpha1 |
Community  | Group type               | 1.0.0-alpha1 |
Community  | Group                    | 1.0.0-alpha1 |
Community  | Reference Data           | 1.0.0-alpha1 |
Community  | Product                  | 1.0.0-alpha1 |
Community  | User                     | 1.0.0-alpha1 |
Community  | User Roles               | 1.0.0-alpha1 |
Community  | User Groups              | 1.0.0-alpha1 |
Community  | Access Control List      | 1.0.0-alpha1 |
Community  | Variant Group            | 1.0.0-beta1  |
Community  | Product History          |      `-`     |
Community  | Image files              |      `-`     |
Enterprise | Product Asset            | 1.0.0-alpha1 |
Enterprise | Asset files              |      `-`     |
Enterprise | Product Asset Categories | 1.0.0-alpha1 |
Enterprise | Product Rules            | 1.0.0-alpha1 |
Enterprise | Product Draft            |      `-`     |
Enterprise | Published Product        |      `-`     |
Enterprise | Teamwork Assistant       |      `-`     |

### Extensions

Extension             | Version                                                       |
--------------------- | ------------------------------------------------------------- |
ElasticSearchBundle   | Not relevant as ElasticSearch is now part of the native stack |
InnerVariationBundle  | 1.0.0-alpha3                                                  |
CustomEntityBundle    | `-`                                                           |

#### InnerVariationBundle

The modeling of the variations with the IVB must be well structured to be fully handled by Transporteo. In the PIM 2.0, a family variant can't have more than 5 axis, and this axis should be one of the following types: 
- Simple select
- Reference data simple select
- Metric
- Yes/No

If one of these conditions is not fulfilled, the products concerned won't be migrated. You will have to think about a better modeling for these products and migrate them manually.

You can find the details of the errors in the file "var/logs/error.log".

### Custom Code

For now, the custom code migration is not automated.

Our plan is to enrich Transporteo with a step by step assistant to help you updating your custom code.

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

### Access to remote servers

If the 1.7 source PIM you will migrate from is installed remotely, you'll be asked to provide a *private SSH key* able to connect to this server.

### Upgrade!

To launch the tool, run:

```bash
  $ php Transporteo.php akeneo-pim:migrate
```

then, let yourself be guided ;) 

At the end of the tool's job, you will have your database setup with your data but we do not migrate your custom code.
You will have to migrate it following this [upgrade file](./UPGRADE-2.0.md).

We plan to automate this part in future release, stay tuned! :)

*Tip: You can define the default responses in the file "src/Infrastructure/Common/config/parameters.yml"*

## How to contribute

Please, have a look on the [CONTRIBUTING](./.github/CONTRIBUTING.md) page.

## What's next?

We will continuously improve this tool, you can follow our plans [here](https://github.com/akeneo/transporteo/projects/1).

You can also have a look on the [changelog](./CHANGELOG.md).
