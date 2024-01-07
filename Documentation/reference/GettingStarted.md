# Getting Started

[Table of Contents](../README.md)


## Typoscript integration
It is important to include static TS resource **T3Twigs (t3twigs)** under **Include static (from extensions)**.


## rn_base Plugin

First of all you need a [rn_base plugin](https://github.com/digedag/rn_base/blob/master/Documentation/fe_plugins.md) with an action which extends `\Sys25\RnBase\Frontend\Controller\AbstractAction`.

Here you have to write all data into the $viewData object via `$viewData->offsetSet('key', 'value')` and `return null`

Also you have to override the `getViewClassName()` function to use twig rendering
```php
public function getViewClassName()
{
    return 'System25\T3twigs\View\TwigView';
}
```
and the `protected function getTemplateName() {return 'templateName';}` to define your template name. Now the `templateName.html.twig` file is used from your templatePath which was configured via TS.


### Usage for ThirdParty Plugins

For existing plugins, the view class can be set by TS.
So you can use [MK SEARCH](https://github.com/DMKEBUSINESSGMBH/typo3-mksearch/),
or any other [rn_base](https://github.com/digedag/rn_base) based Plugin, with twig templates.

```
    plugin.tx_mksearch {
        ### set the template path for the solr action to the twig template. can be done in flexform to.
        searchsolrTemplate = EXT:mksearchtwig/Resources/Private/Template/Extensions/MkSearch/SearchSolr.html.twig
        
        #### set the twig view
        searchsolr.viewClassName = System25\T3twigs\View\TwigView
    }
```

In the template you can access the all the data from the viewdata like this:
```twig
<ul>
    {% for item in result.items %}
        <li>
            <h4>{{ item.record.title|t3link({destination: item.record.pid}) }}</h4>
            <p>{{ item.record.content }}</p>
        </li>
    {% endfor %}
</ul>
```
