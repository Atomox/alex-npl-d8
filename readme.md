
## The Challenge

Ask Alexa a question about our site. Let Alexa interact with our site, and get
a response to any question it knows about.


## The Question

Ask Alexa: “Who is Ben Helmer and what are his hobbies”


## The NLP JSON

NLP will convert the question to a weighted object:

```
{
  "query" : [ {
    "word" : "helmer",
    "weight" : 4
  }, {
    "word" : "ben",
    "weight" : 4
  }, {
    "word" : "hobby",
    "weight" : 2
  } ],
  "required" : null,
  "path" : "/content/ey",
  "type" : "[nt:base]"
}
```

## The GET Request:

```
http://drupal.eyinnovation.com/api/ask?question=%7B%0A++%22query%22+%3A+%5B+%7B%0A++++%22word%22+%3A+%22helmer%22%2C%0A++++%22weight%22+%3A+4%0A++%7D%2C+%7B%0A++++%22word%22+%3A+%22ben%22%2C%0A++++%22weight%22+%3A+4%0A++%7D%2C+%7B%0A++++%22word%22+%3A+%22hobby%22%2C%0A++++%22weight%22+%3A+2%0A++%7D+%5D%2C%0A++%22required%22+%3A+null%2C%0A++%22path%22+%3A+%22%2Fcontent%2Fey%22%2C%0A++%22type%22+%3A+%22%5Bnt%3Abase%5D%22%0A%7D
```

## The Response:
```
{
  "value" : "Ben Helmer enjoys photography.",
  "status" : 0
}
```


### References:

 - https://www.eyinnovation.com/ - Existing Adobe implimentation.



## TODO
COMPLETE:
x - NO TICKET -- Docker -- Setup Drupal with Docker.
x - NO TICKET -- Drupal -- Setup Drupal Server with Composer.
x - NO TICKET -- Docker -- Setup SOLR to interface with D8.
    - `Search API Module`, `Search API SOLR`, Copy SOLR conf from search API solr module.

IN PROGRESS:
- NO TICKET -- Drupal -- Custom Module to search and return Alexa result.
- NO TICKET -- Drupal -- CKEditor Plugin to mark Alexa content.

BACKLOG:
- NO TICKET -- Drupal -- Setup Drush
- NO TICKET -- Drupal -- Setup Devel
- NO TICKET -- Drupal -- Content Types: Break out Alexa response into individual nodes:
    - each will have a `response`, `search context`, `node reference`.
- NO TICKET -- Drupal -- Better page formats to handle site structure.

- NO TICKET -- Alexa -- Setup Tomcat and NLP App.
- NO TICKET -- SERVER -- Setup PROD server.
- NO TICKET -- Theme -- Setup Drupal Theme.


## Installation

Install (including new repo checkout):
`composer install`

Update:
`composer update` from the root directory.

Add Contrib Modules:
`composer require drupal/module_name 8.1.*@dev`


## SOLR

Requires: Search API, SOLR Search API.

__Use `solr` as the host___ when configuring Drupal to talk to SOLR.