# Install guide 

## Prerequisite

You need to run TYPO3 in Composer mode! Install from TER is not supported as T3twig is shipped without Twig itself.

## Install

As usual with composer add a new requirement in your project.

```
composer.phar require digedag/t3twigs
```

After this enable **T3twigs** in extension manager and add static typoscript template **T3twigs (t3twigs)** to your page setup. Ensure there is some new typoscript configuration under `lib.tx_t3twigs.`
