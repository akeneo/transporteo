# Requirements

## System

- php7.1
- php7.1-mbstring
- php7.1-json
- php7.1-xml
- [composer](https://getcomposer.org/download/)
- a SSH client if the 1.7 source PIM you will migrate from is installed remotely.

And the same requirements as the PIM as you need a PIM installed on your computer ([instructions](https://docs.akeneo.com/latest/install_pim/manual/system_requirements/system_requirements.html)).
As we don't use Elasticsearch in Transporteo, you can install it the way you want.

## API

Transporteo uses the API to migrate the products (and variants if there are any). So it has to be functional and well configured on the 1.7 source PIM.

### Authentication

You need to have created a pair of client id / secret on the 1.7 source PIM. You can check if a pair of client id / secret already exists with this Symfony command:

```bash
php app/console pim:oauth-server:list-clients
```

And you can create one with:

```bash
php app/console pim:oauth-server:create-client
```

No need to create one on the 2.0 destination PIM. They will all be migrated from the 1.7 source PIM.

### User

If the 1.7 source PIM is an enterprise edition, you have to check that the user you'll use has all the Web API permissions, and that he is allowed to view and edit all the products and attributes.
