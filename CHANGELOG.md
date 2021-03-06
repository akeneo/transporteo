# 1.0.1

## Bug fixes

- Fix DSN construction for MySQL connection
- PIM-7114: All the products of an inner variation type family must be migrated

# 1.0.0

## Improvements

- Add comments to configuration parameters
- Add some documentation
- Remove useless file

# 1.0.0-beta1

## Improvements

- MIG-64 Migration of product variants from InnerVariationBundle and variant groups both
- MIG-36 Migration of product variants from variant groups
- Improve use of SSH to connect to a remote source PIM

## Bug fixes

- MIG-92 fix destination PIM installation for EE
- MIG-91 Missing variant group combination validation

# 1.0.0-alpha4

## Improvements

- MIG-71 Execute Symfony commands in production env
- MIG-77 Keep the internal jobs in destination PIM

## Bug fixes

- Fix error when the inner variation type label is null

# 1.0.0-alpha3

## Improvements

- MIG-1 Migration of products variations from InnerVariationBundle
- MIG-71 SSH with passphrase
- MIG-69 PIM 2.0.2 compatibility

## Bug fixes

- GITHUB-93 Fix destination pim version check
- GITHUB-89 Error when reading Composer repositories
- GITHUB-95 Fix SshConsole constructor

# 1.0.0-alpha2

## Improvements

## Bug fixes

- GITHUB-77 Fix missing new column for jobs migration
- GITHUB-74 Add possibility to define defaults responses
- Fix MySQL query parameter that needs to be escaped
- GITHUB-78 Add support for null as database password
- GITHUB-84 Fix missing internal job instance
- GITHUB-79 Remove useless usage of database name in MySQL queries

# 1.0.0-alpha1

Very first version of our migration tool :rocket:
