# Transporteo

## Goal

This tool aims at helping you to migrate your *PIM 1.7 standard edition* (either _Community_ or _Enterprise_) to the new version 2.0. All your data will be migrated seamlessly. Your source PIM won't be updated nor touched. Instead, we'll perform the migration in a brand new PIM 2.0. Several reasons led us to this choice:
- the [System Requirements](https://docs.akeneo.com/2.0/install_pim/manual/system_requirements/system_requirements.html) have changed between Akeneo PIM 1.7 and 2.0
- with variant groups and inner variation (a paid extension for the _Enterprise Edition_) there are a lot of different and complex use cases to migrate products data
- the need to migrate real production data without worrying about a failure, a problem or an incomplete migration

The 1.7 source PIM you will migrate from can be either installed locally or remotely. 

The 2.0 destination PIM you will migrate to should be installed locally running on the port 80, you can install it following these [instructions](https://docs.akeneo.com/latest/install_pim/manual/index.html).
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
- [How Akeneo deals with variants?](https://medium.com/akeneo-labs/how-does-akeneo-deal-with-variants-42bcab83a879), functional blog post

## Scope

This tool has a dedicated release cycle and we're regularly releasing improvements in new versions.

We count on your feedback to continue to improve it in attempt to cover all your migration needs, don't hesitate to open issues describing your cases.

### Data Migration

Edition    | Model                    | Version          |
---------- | ------------------------ | -----------------|
Community  | Association type         | 1.0.0            |
Community  | Attribute                | 1.0.0            |
Community  | Attribute Group          | 1.0.0            |
Community  | Categories               | 1.0.0            |
Community  | Family                   | 1.0.0            |
Community  | Group type               | 1.0.0            |
Community  | Group                    | 1.0.0            |
Community  | Reference Data           | 1.0.0            |
Community  | Product                  | 1.0.0            |
Community  | User                     | 1.0.0            |
Community  | User Roles               | 1.0.0            |
Community  | User Groups              | 1.0.0            |
Community  | Access Control List      | 1.0.0            |
Community  | Variant Group            | 1.0.0            |
Community  | Product History          | Not supported    |
Community  | Image files              | [See this section](#image-and-asset-files) |
Enterprise | Product Asset            | 1.0.0            |
Enterprise | Asset files              | [See this section](#image-and-asset-files) |
Enterprise | Product Asset Categories | 1.0.0            |
Enterprise | Product Rules            | 1.0.0            |
Enterprise | Product Draft            | Not supported    |
Enterprise | Published Product        | Not supported    |
Enterprise | Teamwork Assistant       | Not supported    |

### Extensions

Extension             | Version                                                       |
--------------------- | ------------------------------------------------------------- |
ElasticSearchBundle   | Not relevant as ElasticSearch is now part of the native stack |
InnerVariationBundle  | 1.0.0                                                         |
CustomEntityBundle    | Not supported                                                 |

### Custom Code

For now, the custom code migration is not automated.

Our plan is to enrich Transporteo with a step by step assistant to help you updating your custom code.

### Image and asset files

If your images and assets are configured on a remote file system, you just have to configure you 2.0 PIM to access them.
If your images and assets are configured to be stored locally, you have to copy them manually into the 2.0 PIM.

## Installation

```bash
  $ composer.phar create-project "akeneo/transporteo":"dev-master"
```

## How to use

To launch the tool, run:

```bash
  $ php Transporteo.php akeneo-pim:migrate
```

then, let yourself be guided ;) 

At the end of the tool's job, you will have your database setup with your data but we do not migrate your custom code.
You will have to migrate it following this [upgrade file](./UPGRADE-2.0.md).

We plan to automate this part in future release, stay tuned! :)

## Documentation

- [Requirements](doc/requirements.md)
- [Advance usage](doc/advance-usage.md)
- [Product variants migration](doc/product-variants-migration.md)

## Support & contribution

Be aware that this tool is only supported in **best effort** by our team.
If you find an issue or want to ask for an improvement, do not hesitate to open a Github issue on this repository.

All contributions are of course very welcomed! So do not hesitate to help us build an even better migration tool. We'd love that.
You can have a look to the [Contributing](./.github/CONTRIBUTING.md) page.
