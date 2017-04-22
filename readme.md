
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
x - NO TICKET -- Docker -- SASS watch/compiling.
x - NO TICKET -- Drupal -- Setup Devel
x - NO TICKET -- Alexa -- Setup Tomcat and NLP App.
x - NO TICKET -- SERVER -- Setup PROD server.

IN PROGRESS:
- NO TICKET -- Drupal -- Custom Module to search and return Alexa result.

BACKLOG:

BLOCKER:
- NO TICKET -- Alexa -- Amazon config integration. Talk to NLP.

CRITICAL:
- NO TICKET -- Pull Annas Changes
- NO TICKET -- Sample content from Jeff
- NO TICKET -- Theme -- Setup Drupal Theme.

- NO TICKET -- Lock down SOLR -- Password Protect? Route through Nginx?
- NO Ticket -- Lock down 8080
- No Ticket -- Lock down tomcat admin

MAJOR:
- NO TICKET -- Drupal -- Content Types: Break out Alexa response into individual nodes:
    - each will have a `response`, `search context`, `node reference`.
- NO TICKET -- Drupal -- Better page formats to handle site structure.
- NO TICKET -- Drupal -- CKEditor Plugin to mark Alexa content.

MINOR:
- NO TICKET -- Drupal -- Setup Drush
- NO TICKET -- SOLR -- Find config file for core definitions, and map as volume.


## Installation

Install (including new repo checkout):
`composer install`

Update:
`composer update` from the root directory.

Add Contrib Modules:
`composer require drupal/module_name 8.1.*@dev`


## SOLR

Requires: Search API, SOLR Search API.

__Use `solr` as the host__ when configuring Drupal to talk to SOLR.


## Alexa Questions from Adobe Version:

- Does EY provide digital technology assessments?
- How is the Adobe Marketing Cloud being used to drive personalization
- Tell me about the sharing economy business model.
- How are industrial mashups affecting the digital economy?
- What are some potentials of blockchain technology?
- How much money did EY make last year?
- Tell me about EY Society
- What is EY's Focus
- Tell me about NorthPoint's strength and experience.
- What are EY's Adobe analytics offerings?