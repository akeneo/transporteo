# Product variants migration

If you used variant groups and/or inner variation types (with the paid extension InnerVariationBundle for the Enterprise Edition), or both together, the product migration is done in two steps. At first the products are migrated as they are not taking variants in account. Then in a second step, family variants, product models and product variants are created in the PIM 2.0 to reproduce the variants of the PIM 1.7.

However , 2.0 PIM is stricter about the use of variations: it enforces to use them in a correct way. 
Therefore, some misconception in the use of the variation in 1.7 can prevent the migration of the variants in 2.0.

Here are the rules that must be respected in order to fully migrate the product variants:

- All the variants of a product must be of the same family
- A family variant must not have more than 5 axes.
- A variant axis must be one of the following types:
    - Simple select
    - Reference data simple select
    - Metric
    - Yes/No

If a variant group or a inner variation type does not comply with these rules, the concerned products will remain without variant and you will be warned if it occurs. You will have to think about a better modeling for these products and then two options are available:

1- Create manually the family variants, product models and product variants (directly from the UI, or using the API or imports) in the PIM 2.0

2- Fix the invalid variant groups and/or inner variation types in the PIM 1.7 and re-perform the migration.

You can read this articles to learn more about variants in version 2.0: 

- [What about products with variants?](https://help.akeneo.com/articles/what-about-products-variants.html)
- [Offer choice with variants!](https://medium.com/akeneo-labs/offer-choice-with-variants-8460a82fa36)
- [How Akeneo deals with variants?](https://medium.com/akeneo-labs/how-does-akeneo-deal-with-variants-42bcab83a879)
