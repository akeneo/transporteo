# Migration Tool

## Install

A docker-compose is present to help you to setup everything but the tool does not need it to work. Use it only if you are confident with docker.

```
 docker-compose run php php MigrationTool.php akeneo-pim:migrate
```

Also there is a Makefile to help to do some stuff through docker:

```
  make update
  make launch
```

### Requirements for the tool

- php-7.1
- php7.1-gmp
- composer
- ssh-client

If you need to install a PIM-Enterprise edition or download your source PIM from a remote server,
the tool will ask you a private keySo please, be sure the private key you private is able to do what we want to do.

The tool will ask you several path, please provide absolute path and in case you use docker provide the corresponding path.

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

If you use docker you have a tons of helpers, just type `make list`

In addition of usage requirements you need to install `dot` to be able to print the workflow.

Please run CS-fixer, PHPSpec and PHPUnit before committing. (`make commit` if you use docker)

## Troubleshooting

As the test of the parts using SSH are complicated we are not sure that your configuration fits our usage.
