# OpenSearchServer module

OpenSearchServer module for Thelia integrates the Open source search engine [OpenSearchServer](http://www.opensearchserver.com/).

**This module is still in development.**

## Known bugs and remaining developments

* listen to all event for products to ensure proper updating of index

## Installation

### Installing OpenSearchServer

This module requires a running instance of OpenSearchServer. 

* OpenSearchServer can be easily used with SaaS hosting. 
* Otherwise [installing it on a server](http://www.opensearchserver.com/documentation/installation/README.md) only take a few minutes.
* A [Docker image](http://www.opensearchserver.com/documentation/installation/docker.md) is also available.

### Installing the OpenSearchServerSearch module

Require the module in your composer.json file using the command line tool :

```
$ composer require "thelia/open-search-server":"1.0.*@dev"
```

### Configuring the module

Once installed, activate it in the Modules page of the back office.

Configuration is quite easy. You will need to provide:
* URL of the running OpenSearchServer instance
* Login for connecting to the instance
* API Key for this login
* Name of the index to use
* Name of the query template to use

If given index does not exist yet the module will automatically create it on the OpenSearchServer instance. 
Same thing for the query template, if the given one does not exist it will be created, based on a common template.

Query template can then be further tuned directly into the OpenSearchServer instance.

Once configuration is completed the checkbox __Enable search with OpenSearchServer__ can be checked to replace the front-end search engine with the one providen by this module.

### Indexing products

Products will be automatically indexed when created or updated and automatically deleted from the index when deleted.

A button _Index all products_ in the module's configuration page allow for indexing of all visible products.

## Roadmap for future versions

* Integrate facets
* Integrate autocompletion
* Do not rely on Thelia's loop to display results   